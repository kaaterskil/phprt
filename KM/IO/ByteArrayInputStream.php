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

use KM\Lang\NullPointerException;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\System;

/**
 * A <code>ByteArrayInputStream</code> contains an internal buffer that contains
 * bytes that may be read from the stream. An internal counter keeps track of
 * the next byte to be supplied by the <code>read</code> method. <p> Closing a
 * <tt>ByteArrayInputStream</tt> has no effect. The methods in this class can be
 * called after the stream has been closed without generating an
 * <tt>IOException</tt>.
 *
 * @author Blair
 */
class ByteArrayInputStream extends InputStream
{

    protected $buf = [];

    protected $pos = 0;

    protected $mark = 0;

    protected $count = 0;

    public function __construct(array &$buf, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($buf);
        }
        $this->buf = $buf;
        $this->pos = (int) $off;
        $this->count = min(array(
            $off + $len,
            count($buf)
        ));
        $this->mark = (int) $off;
    }

    public function readByte()
    {
        return ($this->pos < $this->count) ? ($this->buf[$this->pos ++] & 0xff) : - 1;
    }

    public function read(array &$b, $off = 0, $len = null)
    {
        if ($b == null) {
            throw new NullPointerException();
        }
        if ($len == null) {
            $len = count($b);
        }
        if ($off < 0 || $len < 0 || $len > count($b) - $off) {
            throw new IndexOutOfBoundsException();
        }
        
        if ($this->pos > $this->count) {
            return - 1;
        }
        
        $avail = $this->count - $this->pos;
        if ($len > $avail) {
            $len = $avail;
        }
        if ($len <= 0) {
            return 0;
        }
        System::arraycopy($this->buf, $this->pos, $b, $off, $len);
        $this->pos += $len;
        return $len;
    }

    public function skip($n)
    {
        $k = $this->count - $this->pos;
        if ($n < $k) {
            $k = $n < 0 ? 0 : $n;
        }
        $this->pos += $k;
        return $k;
    }

    public function available()
    {
        return $this->count - $this->pos;
    }

    public function markSupported()
    {
        return true;
    }

    public function mark($readLimit)
    {
        $this->mark = $this->pos;
    }

    public function reset()
    {
        $this->pos = $this->mark;
    }

    public function close()
    {
        // Noop
    }
}
?>