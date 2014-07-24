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
use KM\NIO\ByteBuffer;
use KM\Lang\IllegalArgumentException;

/**
 * IOUtil Class
 *
 * @author Blair
 */
class IOUtil extends Object
{

    private function __construct()
    {}

    public static function write($fd, ByteBuffer $src, $position, NativeDispatcher $nd, Object $lock = null)
    {
        
        // Substitute a native buffer
        $pos = $src->getPosition();
        $lim = $src->getLimit();
        $rem = ($pos <= $lim ? $lim - $pos : 0);
        
        $bb = ByteBuffer::allocate($rem);
        $bb->put($src);
        $bb->flip();
        
        // Do not update src until we see how many bytes were written
        $src->setPosition($pos);
        $n = self::writeFromNativeBuffer($fd, $bb, $position, $nd, $lock);
        if ($n > 0) {
            // Now update src
            $src->setPosition($pos + $n);
        }
        return $n;
    }

    private static function writeFromNativeBuffer($fd, ByteBuffer $bb, $position, NativeDispatcher $nd,
        Object $lock = null)
    {
        $pos = $bb->getPosition();
        $lim = $bb->getLimit();
        $rem = ($pos <= $lim ? $lim - $pos : 0);
        
        $written = 0;
        if ($rem == 0) {
            return 0;
        }
        if ($position != - 1) {
            $written = $nd->pwrite($fd, $bb, $pos, $rem, $position, $lock);
        } else {
            $written = $nd->write($fd, $bb, $pos, $rem);
        }
        if ($written > 0) {
            $bb->setPosition($pos + $written);
        }
        return $written;
    }

    public static function read($fd, ByteBuffer $dst, $position, NativeDispatcher $nd, Object $lock = null)
    {
        if ($dst->isReadOnly()) {
            throw new IllegalArgumentException('Read-only buffer');
        }
        
        // Substitute a native buffer
        $bb = ByteBuffer::allocate($dst->remaining());
        $n = self::readIntoNativeBuffer($fd, $bb, $position, $nd, $lock);
        $bb->flip();
        if ($n > 0) {
            $dst->put($bb);
        }
        return $n;
    }

    private static function readIntoNativeBuffer($fd, ByteBuffer $bb, $position, NativeDispatcher $nd,
        Object $lock = null)
    {
        $pos = $bb->getPosition();
        $lim = $bb->getLimit();
        $rem = ($pos <= $lim ? $lim - $pos : 0);
        if ($rem == 0) {
            return 0;
        }
        $n = 0;
        if ($position != - 1) {
            $n = $nd->pread($fd, $pos, $rem, $position, $lock);
        } else {
            $n = $nd->read($fd, $pos, $rem);
        }
        if ($n > 0) {
            $bb->setPosition($pos + $n);
        }
        return $n;
    }
}
?>