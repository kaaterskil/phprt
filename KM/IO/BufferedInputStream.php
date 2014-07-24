<?php
/**
 * Copyright (c) 2009-2014 Kaaterskil Management, LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace KM\IO;

use KM\Lang\IllegalArgumentException;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\System;

/**
 * A <code>BufferedInputStream</code> adds functionality to another input
 * stream-namely, the ability to buffer the input and to support the
 * <code>mark</code> and <code>reset</code> methods. When the
 * <code>BufferedInputStream</code> is created, an internal buffer array is
 * created. As bytes from the stream are read or skipped, the internal buffer is
 * refilled as necessary from the contained input stream, many bytes at a time.
 * The <code>mark</code> operation remembers a point in the input stream and the
 * <code>reset</code> operation causes all the bytes read since the most recent
 * <code>mark</code> operation to be reread before new bytes are taken from the
 * contained input stream.
 *
 * @author Blair
 */
class BufferedInputStream extends FilterInputStream
{

    private static $DEFAULT_BUFFER_SIZE = 8192;

    private static $MAX_BUFFER_SIZE;

    public static function clinit()
    {
        self::$MAX_BUFFER_SIZE = PHP_INT_MAX - 8;
    }

    /**
     * The internal buffer array where the data is stores, When necessary, it
     * may be replaces by another array of a different size.
     *
     * @var int[]
     */
    protected $buf;

    /**
     * The index one greater than the index of the last valid byte in the
     * buffer. This value is always in the range <code>0</code> through
     * <code>buf.length</code>; elements <code>buf[0]</code> through
     * <code>buf[count-1] </code>contain buffered input data obtained from the
     * underlying input stream.
     *
     * @var int
     */
    protected $count;

    /**
     * The current position in the buffer. This is the index of the next
     * character to be read from the <code>buf</code> array. <p> This value is
     * always in the range <code>0</code> through <code>count</code>. If it is
     * less than <code>count</code>, then <code>buf[pos]</code> is the next byte
     * to be supplied as input; if it is equal to <code>count</code>, then the
     * next <code>read</code> or <code>skip</code> operation will require more
     * bytes to be read from the contained input stream.
     *
     * @var int
     */
    protected $position;

    /**
     * The value of the <code>pos</code> field at the time the last
     * <code>mark</code> method was called. <p> This value is always in the
     * range <code>-1</code> through <code>pos</code>. If there is no marked
     * position in the input stream, this field is <code>-1</code>. If there is
     * a marked position in the input stream, then <code>buf[markpos]</code> is
     * the first byte to be supplied as input after a <code>reset</code>
     * operation. If <code>markpos</code> is not <code>-1</code>, then all bytes
     * from positions <code>buf[markpos]</code> through <code>buf[pos-1]</code>
     * must remain in the buffer array (though they may be moved to another
     * place in the buffer array, with suitable adjustments to the values of
     * <code>count</code>, <code>pos</code>, and <code>markpos</code>); they may
     * not be discarded unless and until the difference between <code>pos</code>
     * and <code>markpos</code> exceeds <code>marklimit</code>.
     *
     * @var int
     */
    protected $markpos = - 1;

    /**
     * The maximum read ahead allowed after a call to the <code>mark</code>
     * method before subsequent calls to the <code>reset</code> method fail.
     * Whenever the difference between <code>pos</code> and <code>markpos</code>
     * exceeds <code>marklimit</code>, then the mark may be dropped by setting
     * <code>markpos</code> to <code>-1</code>.
     *
     * @var int
     */
    protected $marklimit;

    /**
     * Check to make sure that underlying input stream has not been nulled out
     * due to close; if not return it;
     *
     * @throws IOException
     * @return \KM\IO\InputStream
     */
    private function getInIfOpen()
    {
        $input = $this->in;
        if ($input == null) {
            throw new IOException('Stream closed');
        }
        return $input;
    }

    /**
     * Check to make sure that buffer has not been nulled out due to close; if
     * not return it;
     *
     * @throws IOException
     * @return int[]
     */
    private function &getBufIfOpen()
    {
        $buffer = $this->buf;
        if ($buffer == null) {
            throw new IOException('Stream closed');
        }
        return $this->buf;
    }

    /**
     * Creates a <code>BufferedInputStream</code> with the specified buffer size
     * (or the default size if none is specified), and saves its argument, the
     * input stream <code>in</code>, for later use. An internal buffer array of
     * length <code>size</code> is created and stored in <code>buf</code>.
     *
     * @param InputStream $in The underlying input stream.
     * @param int $size The buffer size.
     * @throws IllegalArgumentException if <code>size <= 0</code>.
     */
    public function __construct(InputStream $in, $size = null)
    {
        parent::__construct($in);
        if ($size == null) {
            $size = self::$DEFAULT_BUFFER_SIZE;
        }
        $size = (int) $size;
        if ($size <= 0) {
            throw new IllegalArgumentException('Buffer size <= 0');
        }
        $this->buf = array_fill(0, $size, null);
    }

    /**
     * Fills the buffer with more data, taking into account shuffling and other
     * tricks for dealing with marks. Assumes that it is being called by a
     * synchronized method. This method also assumes that all data has already
     * been read in, hence pos > count.
     *
     * @throws \OutOfRangeException
     */
    private function fill()
    {
        $buffer = &$this->getBufIfOpen();
        if ($this->markpos < 0) {
            // No mark - throw away the buffer.
            $this->position = 0;
        } elseif ($this->position >= count($buffer)) {
            // No room left in the buffer.
            if ($this->markpos > 0) {
                // Can throw away early part of the buffer
                $sz = $this->position - $this->markpos;
                System::arraycopy($buffer, $this->markpos, $buffer, 0, $sz);
                $this->position = $sz;
                $this->markpos = 0;
            } elseif (count($buffer) >= $this->marklimit) {
                // Buffer got too big - invalidate mark
                $this->markpos = - 1;
                // Drop the buffer contents
                $this->position = 0;
            } elseif (count($buffer) >= self::$MAX_BUFFER_SIZE) {
                throw new \OutOfRangeException('Required array size too large');
            } else {
                // Grow buffer
                $nsz = ($this->position <= (self::$MAX_BUFFER_SIZE - $this->position)) ? $this->position * 2 : self::$MAX_BUFFER_SIZE;
                if ($nsz > $this->marklimit) {
                    $nsz = $this->marklimit;
                }
                $nbuf = array_fill(0, $nsz, null);
                System::arraycopy($buffer, 0, $nbuf, 0, $this->position);
                $buffer = $nbuf;
            }
        }
        $this->count = $this->position;
        $n = $this->getInIfOpen()->read($buffer, $this->position, count($buffer) - $this->position);
        if ($n > 0) {
            $this->count = $n + $this->position;
        }
    }

    /**
     * See the general contract of the <code>read</code> method of
     * <code>InputStream</code>.
     *
     * @return int the next byte of data, or <code>-1</code> if the end of the
     *         stream is reached.
     * @throws IOException if this input stream has been closed by invoking its
     *         <code>close()</code> method, or an I/O error occurs.
     * @see \KM\IO\FilterInputStream::readByte()
     */
    public function readByte()
    {
        if ($this->position >= $this->count) {
            $this->fill();
            if ($this->position >= $this->count) {
                return - 1;
            }
        }
        $value = &$this->getBufIfOpen()[$this->position ++];
        return $value;
    }

    /**
     * Read characters into a portion of an array, reading from the underlying
     * stream at most once if necessary.
     *
     * @param array $b The destination buffer.
     * @param int $off Offset at which to start storing bytes.
     * @param int $len The maximum number of bytes to read.
     * @throws IOException if this input stream has been closed by invoking its
     *         <code>close()</code> method, or an I/O error occurs.
     * @return int The number of bytes read, or <code>-1</code> if the end of
     *         the stream has been reached.
     */
    private function read1(array &$b, $off, $len)
    {
        $avail = $this->count - $this->position;
        if ($avail <= 0) {
            if ($len >= count($this->getBufIfOpen()) && $this->markpos < 0) {
                return $this->getInIfOpen()->read($b, $off, $len);
            }
            $this->fill();
            $avail = $this->count - $this->position;
            if ($avail <= 0) {
                return - 1;
            }
        }
        $cnt = ($avail < $len) ? $avail : $len;
        System::arraycopy($this->getBufIfOpen(), $this->position, $b, $off, $cnt);
        $this->position += $cnt;
        return $cnt;
    }

    /**
     * Reads bytes from this byte-input stream into the specified byte array,
     * starting at the given offset. <p> This method implements the general
     * contract of the corresponding <code>InputStream#read(byte[], int, int)
     * read</code> method of the <code>InputStream</code> class. As an
     * additional convenience, it attempts to read as many bytes as possible by
     * repeatedly invoking the <code>read</code> method of the underlying
     * stream. This iterated <code>read</code> continues until one of the
     * following conditions becomes true: <ul> <li> The specified number of
     * bytes have been read, <li> The <code>read</code> method of the underlying
     * stream returns <code>-1</code>, indicating end-of-file, or <li> The
     * <code>available</code> method of the underlying stream returns zero,
     * indicating that further input requests would block. </ul> If the first
     * <code>read</code> on the underlying stream returns <code>-1</code> to
     * indicate end-of-file then this method returns <code>-1</code>. Otherwise
     * this method returns the number of bytes actually read. <p> Subclasses of
     * this class are encouraged, but not required, to attempt to read as many
     * bytes as possible in the same fashion.
     *
     * @param array $b The destination buffer.
     * @param int $off Offset at which to start storing bytes.
     * @param int $len The maximum number of bytes to read.
     * @throws IOException if this input stream has been closed by invoking its
     *         <code>close()</code> method, or an I/O error occurs.
     * @return int The number of bytes read, or <code>-1</code> if the end of
     *         the stream has been reached.
     * @see \KM\IO\FilterInputStream::read()
     */
    public function read(array &$b, $off = 0, $len = null)
    {
        $this->getBufIfOpen();
        if ($len == null) {
            $len = count($b);
        }
        if (($off | $len | ($off + $len) | (count($b) - ($off + $len))) < 0) {
            throw new IndexOutOfBoundsException();
        } elseif ($len == 0) {
            return 0;
        }
        $n = 0;
        for (;;) {
            $nread = $this->read1($b, $off + $n, $len - $n);
            if ($nread <= 0) {
                return ($n == 0) ? $nread : $n;
            }
            $n += $nread;
            if ($n > $len) {
                return $n;
            }
            $input = $this->in;
            if ($input != null && $input->available() <= 0) {
                return $n;
            }
        }
    }

    /**
     * See the general contract of the <code>skip</code> method of
     * <code>InputStream</code>.
     *
     * @param int $n
     * @return int
     * @throws IOException if the stream does not support seek, or if this input
     *         stream has been closed by invoking its <code>close()</code>
     *         method, or an I/O error occurs.
     * @see \KM\IO\FilterInputStream::skip()
     */
    public function skip($n)
    {
        $this->getBufIfOpen();
        if ($n <= 0) {
            return 0;
        }
        $avail = $this->count - $this->position;
        if ($avail <= 0) {
            // IOf no mark position then don't keep in buffer.
            if ($this->markpos < 0) {
                return $this->getInIfOpen()->skip($n);
            }
            // Fill in buffer to save bytes for reset.
            $this->fill();
            $avail = $this->count - $this->position;
            if ($avail <= 0) {
                return 0;
            }
        }
        $skipped = ($avail - $n) ? $avail : $n;
        $this->position += $skipped;
        return $skipped;
    }

    /**
     * Returns an estimate of the number of bytes that can be read (or skipped
     * over) from this input stream without blocking by the next invocation of a
     * method for this input stream. The next invocation might be the same
     * thread or another thread. A single read or skip of this many bytes will
     * not block, but may read or skip fewer bytes. <p> This method returns the
     * sum of the number of bytes remaining to be read in the buffer
     * (<code>count&nbsp;- pos</code>) and the result of calling the
     * <code>KM\IO\FilterInputStream#in in</code>.available().
     *
     * @return int an estimate of the number of bytes that can be read (or
     *         skipped over) from this input stream without blocking.
     * @throws IOException if this input stream has been closed by invoking its
     *         <code>close()</code> method, or an I/O error occurs.
     * @see \KM\IO\FilterInputStream::available()
     */
    public function available()
    {
        $n = $this->count - $this->position;
        $avail = $this->getInIfOpen()->available();
        return $n > (PHP_INT_MAX - $avail) ? PHP_INT_MAX : $n + $avail;
    }

    /**
     * See the general contract of the <code>mark</code> method of
     * <code>InputStream</code>.
     *
     * @param int $readLimit the maximum limit of bytes that can be read before
     *            the mark position becomes invalid.
     * @see \KM\IO\FilterInputStream::mark()
     */
    public function mark($readLimit)
    {
        $this->marklimit = (int) $readLimit;
        $this->markpos = $this->position;
    }

    /**
     * See the general contract of the <code>reset</code> method of
     * <code>InputStream</code>. <p> If <code>markpos</code> is <code>-1</code>
     * (no mark has been set or the mark has been invalidated), an
     * <code>IOException</code> is thrown. Otherwise, <code>pos</code> is set
     * equal to <code>markpos</code>.
     *
     * @throws IOException if this stream has not been marked or, if the mark
     *         has been invalidated, or the stream has been closed by invoking
     *         its <code>close()</code> method, or an I/O error occurs.
     * @see \KM\IO\FilterInputStream::reset()
     */
    public function reset()
    {
        $this->getBufIfOpen();
        if ($this->markpos < 0) {
            throw new IOException('Resetting to invalid mark');
        }
        $this->position = $this->markpos;
    }

    /**
     * Tests if this input stream supports the <code>mark</code> and
     * <code>reset</code> methods. The <code>markSupported</code> method of
     * <code>BufferedInputStream</code> returns <code>true</code>.
     *
     * @return boolean a <code>boolean</code> indicating if this stream type
     *         supports the <code>mark</code> and <code>reset</code> methods.
     * @see \KM\IO\FilterInputStream::markSupported()
     */
    public function markSupported()
    {
        return true;
    }

    /**
     * Closes this input stream and releases any system resources associated
     * with the stream. Once the stream has been closed, further read(),
     * available(), reset(), or skip() invocations will throw an IOException.
     * Closing a previously closed stream has no effect.
     *
     * @see \KM\IO\FilterInputStream::close()
     */
    public function close()
    {
        $input = $this->in;
        $this->in = null;
        if ($input != null) {
            $input->close();
        }
    }
}
?>