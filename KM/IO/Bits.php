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

use KM\Lang\Object;

/**
 * Utility methods for packing/unpacking primitive values in/out of byte arrays
 * using big-endian byte ordering.
 *
 * @author Blair
 */
class Bits extends Object
{

    /**
     * Utility class constructor
     */
    private function __construct()
    {}
    
    /*
     * Methods for unpacking primitive values from byte arrays starting at the
     * given offset.
     */
    public static function getBoolean(&$bytes, $off = 0)
    {
        return $bytes[$off] != 0;
    }

    public static function getShort(array &$bytes, $off)
    {
        $bin = [];
        for ($i = 0; $i < 2; $i++) {
            $bin[$i] = $bytes[$off + $i];
        }
        $val = unpack('n', implode('', $bin));
        return (int) $val[1];
    }

    public static function getInt(&$bytes, $off = 0)
    {
        $bin = [];
        for ($i = 0; $i < 4; $i++) {
            $bin[$i] = $bytes[$off + $i];
        }
        $val = unpack('N', implode('', $bin));
        return (int) $val[1];
    }

    public static function getLong(&$bytes, $off = 0)
    {
        $bin = [];
        for ($i = 0; $i < 8; $i++) {
            $bin[$i] = $bytes[$off + $i];
        }
        $tmp = unpack('N2', implode('', $bin));
        $long = $tmp[1] << 32 | $tmp[2];
        return $long;
    }

    public static function getFloat(&$bytes, $off = 0)
    {
        $bin = [];
        for ($i = 0; $i < 4; $i++) {
            $bin[$i] = $bytes[$off + $i];
        }
        $val = unpack('f', implode('', $bin));
        return (float) $val[1];
    }
    
    /*
     * Methods for packing primitive values into byte arrays starting at the
     * given offset.
     */
    public static function putBoolean(array &$b, $off = 0, $value)
    {
        $b[$off] = ($value ? 1 : 0);
    }

    public static function putShort(array &$b, $off = 0, $value)
    {
        $int = (int) $value;
        $bin = pack('n', $int);
        for ($i = 0; $i < 2; $i++) {
            $b[$off + $i] = $bin[$i];
        }
    }

    public static function putInt(array &$b, $off = 0, $value)
    {
        $int = (int) $value;
        $bin = pack('N', $int);
        for ($i = 0; $i < 4; $i++) {
            $b[$off + $i] = $bin[$i];
        }
    }

    public static function putFloat(array &$b, $off = 0, $value)
    {
        $float = (float) $value;
        $bin = pack('f', $float);
        for ($i = 0; $i < 4; $i++) {
            $b[$off + $i] = $bin[$i];
        }
    }
}
?>