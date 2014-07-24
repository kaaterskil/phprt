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
namespace Apache\Commons\IO\Input;

use KM\IO\InputStream;
use KM\IO\IOException;
use KM\Lang\IllegalStateException;

/**
 * A decorating input stream that counts the number of bytes that have passed
 * through the stream so far, A typical use case would be during debugging to
 * ensure that data is being read as expected.
 *
 * @author Blair
 */
class CountingInputStream extends ProxyInputStream
{

    /**
     * THe count of bytes that have passed through the underlying stream.
     *
     * @var int
     */
    private $count;

    /**
     * Constructs a new CountingInputStream with the given underlying stream.
     *
     * @param InputStream $in The underlying input stream.
     */
    public function __construct(InputStream $in)
    {
        parent::__construct($in);
    }

    /**
     * Reads the next byte of data adding to the count of bytes received if a
     * byte is successfully read.
     *
     * @return int The byte read or -1 if end of stream.
     * @throws IOException is an I/O exception occurs.
     * @see \Apache\Commons\IO\Input\ProxyInputStream::readByte()
     */
    public function readByte()
    {
        $found = parent::readByte();
        $this->count += ($found >= 0) ? 1 : 0;
        return $found;
    }

    /**
     * Reads a number of bytes into the byte array at a specific offset, keeping
     * count of the number read.
     *
     * @param array $b The buffer into which the data id read.
     * @param int $off THe start offset in the buffer.
     * @param int $len The maximum number of bytes read.
     * @return int The actual number of bytes read.
     * @throws IOException is an I/O exception occurs.
     * @see \Apache\Commons\IO\Input\ProxyInputStream::read()
     */
    public function read(array &$b, $off = 0, $len = null)
    {
        $found = parent::read($b, $off, $len);
        $this->count += ($found >= 0) ? $found : 0;
        return $found;
    }

    /**
     * Skips the stream over the specified number of bytes, adding the skipped
     * amount to the count.
     *
     * @param int $length The number of bytes to skip.
     * @return int The actual number of bytes skipped.
     * @throws IOException is an I/O exception occurs.
     * @see \Apache\Commons\IO\Input\ProxyInputStream::skip()
     */
    public function skip($length)
    {
        $skip = parent::skip($length);
        $this->count += $skip;
        return $skip;
    }

    /**
     * Returns the number of bytes that have passed through this stream.
     *
     * @throws IllegalStateException if the byte count is too large.
     * @return int THe number of bytes accumulated.
     */
    public function getCount()
    {
        $result = $this->getByteCount();
        if ($result > PHP_INT_MAX) {
            throw new IllegalStateException('The byte count is too large to be converted to an integer');
        }
        return $result;
    }

    /**
     * Sets the byte count back to zero.
     *
     * @throws IllegalStateException if the byte count is too large.
     * @return int THe count previous to resetting.
     */
    public function resetCount()
    {
        $result = $this->resetByteCount();
        if ($result > PHP_INT_MAX) {
            throw new IllegalStateException('The byte count is too large to be converted to an integer');
        }
        return $result;
    }

    /**
     * Returns the number of bytes that have passed through this stream.
     *
     * @return int THe number of bytes accumulated.
     */
    public function getByteCount()
    {
        return $this->count;
    }

    /**
     * Sets the byte count back to zero.
     *
     * @return int The count previous to resetting.
     */
    public function resetByteCount()
    {
        $tmp = $this->count;
        $this->count = 0;
        return $tmp;
    }
}
?>