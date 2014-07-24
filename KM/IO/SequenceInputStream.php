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

use KM\IO\InputStream;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\NullPointerException;
use KM\Util\Enumeration;
use KM\Util\Vector;

/**
 * A <code>SequenceInputStream</code> represents the logical concatenation of
 * other input streams. It starts out with an ordered collection of input
 * streams and reads from the first one until end of file is reached, whereupon
 * it reads from the second one, and so on, until end of file is reached on the
 * last of the contained input streams.
 *
 * @author Blair
 */
class SequenceInputStream extends InputStream
{

    /**
     * Initializes a newly created <code>SequenceInputStream</code> by
     * remembering the two arguments, which will be read in order, first
     * <code>s1</code> and then <code>s2</code>, to provide the bytes to be read
     * from this <code>SequenceInputStream</code>.
     *
     * @param InputStream $s1 The first input stream to read.
     * @param InputStream $s2 The second input stream to read.
     * @return \KM\IO\SequenceInputStream
     */
    public static function getInstanceFromStreams(InputStream $s1, InputStream $s2)
    {
        $v = new Vector('\KM\IO\InputStream');
        $v->add($s1);
        $v->add($s2);
        return new self($v->elements());
    }

    /**
     * An enumeration of input streams.
     *
     * @var Enumeration
     */
    protected $e;

    /**
     * The current input stream
     *
     * @var InputStream
     */
    protected $in;

    /**
     * Initializes a newly created <code>SequenceInputStream</code> by
     * remembering the argument, which must be an <code>Enumeration</code> that
     * produces objects whose run-time type is <code>InputStream</code>. The
     * input streams that are produced by the enumeration will be read, in
     * order, to provide the bytes to be read from this
     * <code>SequenceInputStream</code>. After each input stream from the
     * enumeration is exhausted, it is closed by calling its <code>close</code>
     * method.
     *
     * @param Enumeration $e An enumeration of input streams.
     */
    public function __construct(Enumeration $e)
    {
        $this->e = $e;
        try {
            $this->nextStream();
        } catch (IOException $e) {
            // This should not happen
            throw new \Exception('panic');
        }
    }

    /**
     * Continues reading in the next stream is an EOF is reached.
     *
     * @throws NullPointerException
     */
    public final function nextStream()
    {
        if ($this->in != null) {
            $this->in->close();
        }
        if ($this->e->hasMoreElements()) {
            $this->in = $this->e->nextElement();
            if ($this->in == null) {
                throw new NullPointerException();
            }
        } else {
            $this->in = null;
        }
    }

    /**
     * Returns an estimate of the number of bytes that can be read (or skipped
     * over) from the current underlying input stream without blocking by the
     * next invocation of a method for the current underlying input stream. The
     * next invocation might be the same thread or another thread. A single read
     * or skip of this many bytes will not block, but may read or skip fewer
     * bytes. <p> This method simply calls {@code available} of the current
     * underlying input stream and returns the result.
     *
     * @return int an estimate of the number of bytes that can be read (or
     *         skipped over) from the current underlying input stream without
     *         blocking or {@code 0} if this input stream has been closed by
     *         invoking its <code>close()</code> method
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::available()
     */
    public function available()
    {
        if ($this->in == null) {
            return 0;
        }
        return $this->in->available();
    }

    /**
     * Reads the next byte of data from this input stream. The byte is returned
     * as an <code>int</code> in the range <code>0</code> to <code>255</code>.
     * If no byte is available because the end of the stream has been reached,
     * the value <code>-1</code> is returned. This method blocks until input
     * data is available, the end of the stream is detected, or an exception is
     * thrown. <p> This method tries to read one character from the current
     * substream. If it reaches the end of the stream, it calls the
     * <code>close</code> method of the current substream and begins reading
     * from the next substream.
     *
     * @return int The next byte of data, or <code>-1</code> if the end of the
     *         stream is reached.
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::readByte()
     */
    public function readByte()
    {
        if ($this->in == null) {
            return - 1;
        }
        $c = $this->in->readByte();
        if ($c == - 1) {
            $this->nextStream();
            return $this->readByte();
        }
        return $c;
    }

    /**
     * Reads up to <code>len</code> bytes of data from this input stream into an
     * array of bytes. If <code>len</code> is not zero, the method blocks until
     * at least 1 byte of input is available; otherwise, no bytes are read and
     * <code>0</code> is returned. <p> The <code>read</code> method of
     * <code>SequenceInputStream</code> tries to read the data from the current
     * substream. If it fails to read any characters because the substream has
     * reached the end of the stream, it calls the <code>close</code> method of
     * the current substream and begins reading from the next substream.
     *
     * @param array $b The buffer into which the data is read.
     * @param int $off The start offset in array <code>b</code> at which the
     *            data is written.
     * @param int $len The maximum number of bytes read.
     * @throws NullPointerException if <code>b</code> is <code>null</code>.
     * @throws IndexOutOfBoundsException if <code>off</code> is negative,
     *         <code>len</code> is negative, or <code>len</code> is greater than
     *         <code>count(b) - off</code>.
     * @throws IOException if an I/O error occurs.
     * @return int The number of bytes read.
     * @see \KM\IO\InputStream::read()
     */
    public function read(array &$b, $off = 0, $len = null)
    {
        if ($this->in == null) {
            return - 1;
        } elseif ($b == null) {
            throw new NullPointerException();
        }
        if ($len == null) {
            $len = count($b);
        }
        if ($off < 0 || $len < 0 || $len > count($b) - $off) {
            throw new IndexOutOfBoundsException();
        } elseif ($len == 0) {
            return 0;
        }
        $n = $this->in->read($b, $off, $len);
        if ($n <= 0) {
            $this->nextStream();
            return $this->read($b, $off, $len);
        }
        return $n;
    }

    /**
     * Closes this input stream and releases any system resources associated
     * with the stream. A closed <code>SequenceInputStream</code> cannot perform
     * input operations and cannot be reopened. <p> If this stream was created
     * from an enumeration, all remaining elements are requested from the
     * enumeration and closed before the <code>close</code> method returns.
     *
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::close()
     */
    public function close()
    {
        do {
            $this->nextStream();
        } while ($this->in != null);
    }
}
?>