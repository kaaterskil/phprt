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

use KM\IO\FilterInputStream;
use KM\IO\InputStream;

/**
 * ProxyInputStream Class
 *
 * @author Blair
 */
abstract class ProxyInputStream extends FilterInputStream
{

    public function __construct(InputStream $in)
    {
        parent::__construct($in);
    }

    public function readByte()
    {
        return $this->in->readByte();
    }

    public function read(array &$bts, $st = 0, $end = null)
    {
        return $this->in->read($bts, $st, $end);
    }

    public function skip($ln)
    {
        return $this->in->skip($ln);
    }

    public function available()
    {
        return $this->in->available();
    }

    public function close()
    {
        $this->in->close();
    }

    public function mark($idx)
    {
        $this->in->mark($idx);
    }

    public function reset()
    {
        $this->in->reset();
    }

    public function markSupported()
    {
        return $this->markSupported();
    }
}
?>