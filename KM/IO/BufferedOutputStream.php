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

use KM\Lang\System;
use KM\Lang\IllegalArgumentException;

/**
 * The class implements a buffered output stream. By setting up such an output
 * stream, an application can write bytes to the underlying output stream
 * without necessarily causing a call to the underlying system for each byte
 * written.
 *
 * @author Blair
 */
class BufferedOutputStream extends FilterOutputStream
{

    /**
     * The internal buffer where data is stored.
     *
     * @var array
     */
    protected $buf;

    /**
     * THe number of valid bytes in the buffer.
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Creates a new buffered output stream to write data to the specified
     * underlying output stream with the specified buffer size.
     *
     * @param OutputStream $out The underlying output stream.
     * @param int $size The buffer size.
     * @throws IllegalArgumentException if $size <= 0.
     */
    public function __construct(OutputStream $out, $size = 8192)
    {
        parent::__construct($out);
        if ($size <= 0) {
            throw new IllegalArgumentException('Buffer size <= 0');
        }
        $this->buf = array_fill(0, $size, null);
    }

    /**
     * Flush the internal buffer.
     */
    private function flushBuffer()
    {
        if ($this->count > 0) {
            $this->out->write($this->buf, 0, $this->count);
            $this->count = 0;
        }
    }

    /**
     * Writes the specified byte to this buffered output stream.
     *
     * @param int $b The byte to be written.
     * @see \KM\IO\FilterOutputStream::writeByte()
     */
    public function writeByte($b)
    {
        if ($this->count >= count($this->buf)) {
            $this->flushBuffer();
        }
        $this->buf[$this->count ++] = $b;
    }

    /**
     * Writes $len bytes from the specified byte array starting at offset $off
     * to this buffered output stream. Ordinarily, this method stores bytes from
     * the given array into this stream's buffer, flushing the buffer to the
     * underlying output stream as needed. If the requested length is at least
     * as large as this stream's buffer, however, then this method will flush
     * the buffer and write the bytes directly to the underlying output stream.
     * Thus redundant BufferedOutputStreams will not copy data unnecessarily.
     *
     * @param array $b The data.
     * @param int $off The start offset in the data.
     * @param int $len The number of bytes to write.
     * @see \KM\IO\FilterOutputStream::write()
     */
    public function write(array &$b, $off = 0, $len = null)
    {
        $off = (int) $off;
        if ($len == null) {
            $len = count($b);
        }
        $len = (int) $len;
        if ($len >= count($this->buf)) {
            // If the request length exceeds the size of the output buffer flush
            // the output buffer
            // and then write the data directly. In this way, buffered streams
            // will cascade
            // harmlessly.
            $this->flushBuffer();
            $this->out->write($b, $off, $len);
            return;
        }
        if ($len > (count($this->buf) - $this->count)) {
            $this->flushBuffer();
        }
        System::arraycopy($b, $off, $this->buf, $this->count, $len);
        $this->count += $len;
    }

    /**
     * Flushes this buffered output stream. This forces any buffered output
     * bytes to be written out to the underlying output stream.
     *
     * @see \KM\IO\FilterOutputStream::flush()
     */
    public function flush()
    {
        $this->flushBuffer();
        $this->out->flush();
    }
}
?>