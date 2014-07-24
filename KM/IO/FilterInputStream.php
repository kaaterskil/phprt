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

use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\NullPointerException;

/**
 * A <code>FilterInputStream</code> contains some other input stream, which it
 * uses as its basic source of data, possibly transforming the data along the
 * way or providing additional functionality. The class
 * <code>FilterInputStream</code> itself simply overrides all methods of
 * <code>InputStream</code> with versions that pass all requests to the
 * contained input stream. Subclasses of <code>FilterInputStream</code> may
 * further override some of these methods and may also provide additional
 * methods and fields.
 *
 * @author Blair
 */
class FilterInputStream extends InputStream
{

    /**
     * The input stream to be filtered.
     *
     * @var InputStream
     */
    protected $in;

    /**
     * Creates a FilterInputStream.
     *
     * @param InputStream $in The underlying input stream.
     */
    protected function __construct(InputStream $in)
    {
        $this->in = $in;
    }

    /**
     * Reads the next byte of data from this input stream. The value byte is
     * returned as an <code>int</code> in the range <code>0</code> to
     * <code>255</code>. If no byte is available because the end of the stream
     * has been reached, the value <code>-1</code> is returned. This method
     * blocks until input data is available, the end of the stream is detected,
     * or an exception is thrown. <p> This method simply performs
     * <code>in.read()</code> and returns the result.
     *
     * @return int The next byte of data, or <code>null</code> if the end of the
     *         stream is reached.
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::readByte()
     */
    public function readByte()
    {
        return $this->in->readByte();
    }

    /**
     * Reads up to <code>len</code> bytes of data from this input stream into an
     * array of bytes. If <code>len</code> is not zero, the method blocks until
     * some input is available; otherwise, no bytes are read and <code>0</code>
     * is returned. <p> This method simply performs <code>in.read(b, off,
     * len)</code> and returns the result.
     *
     * @param array $b The buffer into which the data is read.
     * @param int $off The start offset in the destination array.
     * @param int $len The maximum number of bytes read
     * @return int The total number of bytes read into the buffer, or
     *         <code>-1</code> if there is no more data because the end of the
     *         stream has been reached.
     * @throws NullPointerException if <code>b</code> is <code>null</code>.
     * @throws IndexOutOfBoundsException if <code>off</code> is negative,
     *         <code>len</code> is negative, or <code>len</code> is greater than
     *         <code>b.length - off</code>.
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::read()
     */
    public function read(array &$b, $off = 0, $len = null)
    {
        return $this->in->read($b, $off, $len);
    }

    /**
     * Skips over and discards <code>n</code> bytes of data from the input
     * stream. The <code>skip</code> method may, for a variety of reasons, end
     * up skipping over some smaller number of bytes, possibly <code>0</code>.
     * The actual number of bytes skipped is returned. <p> This method simply
     * performs <code>in.skip(n)</code>.
     *
     * @param int $n The number of bytes to be skipped.
     * @return int The actual number of bytes skipped.
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::skip()
     */
    public function skip($n)
    {
        return $this->in->skip($n);
    }

    /**
     * Returns an estimate of the number of bytes that can be read (or skipped
     * over) from this input stream without blocking by the next caller of a
     * method for this input stream. The next caller might be the same thread or
     * another thread. A single read or skip of this many bytes will not block,
     * but may read or skip fewer bytes. <p> This method returns the result of
     * <code>#in in</code>.available().
     *
     * @return int An estimate of the number of bytes that can be read (or
     *         skipped over) from this input stream without blocking.
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::available()
     */
    public function available()
    {
        return $this->in->available();
    }

    /**
     * Closes this input stream and releases any system resources associated
     * with the stream. This method simply performs <code>in.close()</code>.
     *
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::close()
     */
    public function close()
    {
        $this->in->close();
    }

    /**
     * Marks the current position in this input stream. A subsequent call to the
     * <code>reset</code> method repositions this stream at the last marked
     * position so that subsequent reads re-read the same bytes. <p> The
     * <code>readlimit</code> argument tells this input stream to allow that
     * many bytes to be read before the mark position gets invalidated. <p> This
     * method simply performs <code>in.mark(readlimit)</code>.
     *
     * @param int $readLimit the maximum limit of bytes that can be read before
     *            the mark position becomes invalid.
     * @see \KM\IO\InputStream::mark()
     */
    public function mark($readLimit)
    {
        $this->in->mark($readLimit);
    }

    /**
     * Repositions this stream to the position at the time the <code>mark</code>
     * method was last called on this input stream. <p> This method simply
     * performs <code>in.reset()</code>. <p> Stream marks are intended to be
     * used in situations where you need to read ahead a little to see what's in
     * the stream. Often this is most easily done by invoking some general
     * parser. If the stream is of the type handled by the parse, it just chugs
     * along happily. If the stream is not of that type, the parser should toss
     * an exception when it fails. If this happens within readlimit bytes, it
     * allows the outer code to reset the stream and try another parser.
     *
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::reset()
     */
    public function reset()
    {
        $this->in->reset();
    }

    /**
     * Tests if this input stream supports the <code>mark</code> and
     * <code>reset</code> methods. This method simply performs
     * <code>in.markSupported()</code>.
     *
     * @return boolean <code>true</code> if this stream type supports the
     *         <code>mark</code> and <code>reset</code> method;
     *         <code>false</code> otherwise.
     * @see \KM\IO\InputStream::markSupported()
     */
    public function markSupported()
    {
        return $this->in->markSupported();
    }
}
?>