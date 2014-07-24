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
namespace KM\IO\ObjectInputStream;

use KM\IO\InputStream;
use KM\IO\EOFException;

/**
 * Input stream supporting single-byte peek operations.
 *
 * @author Blair
 */
class PeekInputStream extends InputStream
{

    /**
     * The underlying stream.
     *
     * @var InputStream
     */
    private $in;

    /**
     * Peeked byte
     *
     * @var int
     */
    private $peekb = -1;

    /**
     * Creates a PeekInputStream on top of the given underlying stream.
     *
     * @param InputStream $in
     */
    public function __construct(InputStream $in)
    {
        $this->in = $in;
    }

    /**
     * Peeks at next byte value in stream. Similar to read(), except that it
     * does not consume the read value.
     *
     * @return int
     */
    public function peek()
    {
        return ($this->peekb >= 0) ? $this->peekb : ($this->peekb = ord($this->in->readByte()));
    }

    public function readByte()
    {
        if ($this->peekb >= 0) {
            $v = $this->peekb;
            $this->peekb = -1;
            return $v;
        } else {
            return $this->in->readByte();
        }
    }

    public function read(array &$b, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($b);
        }
        if ($len == 0) {
            return 0;
        } elseif ($this->peekb < 0) {
            return $this->in->read($b, $off, $len);
        } else {
            $b[$off++] = $this->peekb;
            $len--;
            $this->peekb = -1;
            $n = $this->in->read($b, $off, $len);
            return ($n >= 0) ? ($n + 1) : 1;
        }
    }

    public function readFully(array &$b, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($b);
        }
        $n = 0;
        while ($n < $len) {
            $count = $this->read($b, $off + $n, $len - $n);
            if ($count < 0) {
                throw new EOFException();
            }
            $n += $count;
        }
    }

    public function skip($n)
    {
        if ($n <= 0) {
            return 0;
        }
        $skipped = 0;
        if ($this->peekb >= 0) {
            $this->peekb = -1;
            $skipped++;
            $n--;
        }
        return $skipped + $this->skip($n);
    }

    public function available()
    {
        return $this->in->available() + (($this->peekb >= 0) ? 1 : 0);
    }

    public function close()
    {
        $this->in->close();
    }
}
?>