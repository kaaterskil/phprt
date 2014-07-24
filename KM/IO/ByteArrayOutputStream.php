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
use KM\Util\Arrays;

/**
 * This class implements an output stream in which the data is written into a
 * byte array, The buffer automatically grows as data is written into it. The
 * data can be retrieved by using toByteArray() and __toString(). Closing a
 * ByteArrayOutputStream has no effect, The methods in this class can be called
 * after the stream has been closed without generating an IOException.
 *
 * @author Blair
 */
class ByteArrayOutputStream extends OutputStream
{

    /**
     * The buffer where data is stored.
     *
     * @var array
     */
    protected $buf;

    /**
     * The number of valid bytes in the buffer.
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Creates a new byte array output stream.
     */
    public function __construct($size = 32)
    {
        if ($size < 0) {
            throw new IllegalArgumentException('Negative initial size: ' . $size);
        }
        $this->buf = array_fill(0, $size, null);
    }

    /**
     * Increases the capacity if necessary to ensure that it can hold at least
     * the number of elements specified by the minimum capacity argument.
     *
     * @param int $minCapacity The desired minimum capacity.
     */
    private function ensureCapacity($minCapacity)
    {
        $minCapacity = (int) $minCapacity;
        if ($minCapacity - count($this->buf) > 0) {
            $this->grow($minCapacity);
        }
    }

    /**
     * Increases the capacity to ensure that it can hold at least the number of
     * elements specified by the minimum capacity argument.
     *
     * @param int $minCapacity The desired minimum capacity.
     * @throws \OverflowException
     */
    private function grow($minCapacity)
    {
        $minCapacity = (int) $minCapacity;
        
        $oldCapacity = count($this->buf);
        $newCapacity = $oldCapacity << 1;
        if ($newCapacity - $minCapacity < 0) {
            $newCapacity = $minCapacity;
        }
        if ($newCapacity < 0) {
            if ($minCapacity < 0) {
                throw new \OverflowException();
            }
            $newCapacity = PHP_INT_MAX;
        }
        $this->buf = Arrays::copyOf($this->buf, $newCapacity);
    }

    /**
     * Writes the specified byte to this byte array output stream.
     *
     * @param unknown $b
     * @see \KM\IO\OutputStream::writeByte()
     */
    public function writeByte($b)
    {
        $this->ensureCapacity($this->count + 1);
        $this->buf[$this->count] = $b;
        $this->count += 1;
    }

    /**
     * Writes $len bytes from the specified byte array starting at offset $off
     * to this byte array output stream.
     *
     * @param array $b The data.
     * @param int $off The start offset of the data.
     * @param int $len The number of bytes to write.
     * @throws IndexOutOfBoundsException
     * @see \KM\IO\OutputStream::write()
     */
    public function write(array &$b, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($b);
        }
        if (($off < 0) || ($off > count($b)) || ($len < 0) || ($off + $len - count($b) > 0)) {
            throw new IndexOutOfBoundsException();
        }
        $this->ensureCapacity($this->count + $len);
        System::arraycopy($b, $off, $this->buf, $this->count, $len);
        $this->count += $len;
    }

    /**
     * Writes the complete contents of this byte array output stream to the
     * specified output stream argument, as if by calling the output stream's
     * write method.
     *
     * @param OutputStream $out The output stream to which to write the data.
     */
    public function writeTo(OutputStream $out)
    {
        $out->write($this->buf, 0, $this->count);
    }

    /**
     * Resets the $count field of this byte array output stream to zero so that
     * all currently accumulated output in the output stream is discarded. The
     * output stream can be used again, reusing the already allocated buffer
     * space.
     */
    public function reset()
    {
        $this->count = 0;
    }

    /**
     * Creates a newly allocated byte array. Its size is the current size of
     * this output stream, and the valid contents of the buffer have been copied
     * into it.
     *
     * @return array The current contents of this output stream as a byte array.
     */
    public function toByteArray()
    {
        return Arrays::copyOf($this->buf, $this->count);
    }

    /**
     * Returns the current size of the buffer.
     *
     * @return int The value of the $count field, which is the number of valid
     *         bytes in this output stream.
     */
    public function size()
    {
        return $this->count;
    }

    /**
     * Converts the buffer's contents into a string decoding bytes using the
     * platform's default character set. The length of the new string is a
     * function of the character set and hence may not be equal to the size of
     * the buffer. This method always replaces malformed input and unmappable
     * character sequences with the default replacement string for the
     * [latform's default character set.
     *
     * @return string
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        $str = '';
        foreach ($this->buf as $chr) {
            $str .= chr($chr);
        }
        return $str;
    }

    /**
     * Closing a ByteArrayOutputStream has no effect, The methods in this class
     * can be called after the stream has been closed without generating an
     * IOException.
     *
     * @see \KM\IO\OutputStream::close()
     */
    public function close()
    {}
}
?>