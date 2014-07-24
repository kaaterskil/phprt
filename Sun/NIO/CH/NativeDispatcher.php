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
namespace Sun\NIO\CH;

use KM\Lang\Object;
use KM\IO\IOException;
use KM\NIO\ByteBuffer;

/**
 * Allows different platforms to call different native methods for read and
 * write operations.
 *
 * @author Blair
 */
abstract class NativeDispatcher extends Object
{

    public abstract function read($fd, $off, $len);

    public function pread($fd, $off, $len, $position, Object $lock)
    {
        throw new IOException('Operation unsupported');
    }

    public abstract function write($fd, ByteBuffer $buf, $off, $len);

    public function pwrite($fd, ByteBuffer $buf, $off, $len, $position, Object $lock)
    {
        throw new IOException('Operation unsupported');
    }

    public abstract function close($fd);

    public abstract function size($fd);

    public function preClose($fd)
    {
        // Noop
    }
}
?>