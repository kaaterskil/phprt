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

/**
 * This class is the superclass of all classes that filter output streams. These
 * streams sit on top of an already existing output stream (the underlying
 * output stream) which it uses as its basic sink of data, but possibly
 * transforming the data along the way or providing additional functionality.
 * The class FilterOutputStream itself simply overrides all methods of
 * OutputStream with versions that pass all requests to the underlying output
 * stream. Subclasses of FilterOutputStream may further override some of these
 * methods as well as provide additional methods and fields.
 *
 * @author Blair
 */
class FilterOutputStream extends OutputStream
{

    /**
     * The underlying output stream to be filtered.
     *
     * @var OutputStream
     */
    protected $out;

    /**
     * Creates an output stream filter built on top of the specified underlying
     * output stream.
     *
     * @param OutputStream $out
     */
    public function __construct(OutputStream $out)
    {
        $this->out = $out;
    }

    /**
     * Writes the specified byte to this output stream. The writeByte() method
     * of FilterOutputStream calls the writeByte() method of its underlying
     * output stream.
     *
     * @param int $b The byte.
     * @see \KM\IO\OutputStream::writeByte()
     */
    public function writeByte($b)
    {
        $this->out->writeByte($b);
    }

    /**
     * Writes $len bytes from the specified byte array starting at offset $off
     * to this output stream. The write() method of FilterOutputStream calls the
     * writeByte() method on each byte to output. Note that this method does not
     * call the write() method of its underlying output stream with the same
     * arguments, Subclasses of FilterOutputStream should provide a more
     * efficient implementation of this method.
     *
     * @param array $b The data.
     * @param int $off The start offset of the data.
     * @param int $len The number of bytes to write.
     * @throws IndexOutOfBoundsException
     * @see \KM\IO\OutputStream::write()
     */
    public function write(array &$b, $off = 0, $len = null)
    {
        $off = (int) $off;
        if ($len == null) {
            $len = count($b);
        }
        $len = (int) $len;
        if (($off | $len | (count($b) - ($len + $off)) | ($off + $len)) < 0) {
            throw new IndexOutOfBoundsException();
        }
        for ($i = 0; $i < $len; $i ++) {
            $this->writeByte($b[$off + $i]);
        }
    }

    /**
     * Flushes this output stream and forces any buffered output bytes to be
     * written out to the stream. The flush() method of FilterOutputStream calls
     * the flush() method of its underlying output stream.
     *
     * @see \KM\IO\OutputStream::flush()
     */
    public function flush()
    {
        $this->out->flush();
    }

    /**
     * Closes the output stream and releases any system resources associated
     * with the stream. The close() method of FilterOutputStream calls its
     * flush() method, and then calls the close() method of its underlying
     * output stream.
     *
     * @see \KM\IO\OutputStream::close()
     */
    public function close()
    {
        $this->flush();
        $this->out->close();
    }
}
?>