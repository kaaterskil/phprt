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
namespace KM\Lang;

/**
 * ThreadLocal Class
 *
 * @author Blair
 */
class ThreadLocal extends Object
{

    /**
     * ThreadLocals rely on per-thread linear-probe hash maps attached to each
     * thread (Thread.threadLocals and inheritableThreadLocals). The ThreadLocal
     * objects act as keys, searched via threadLocalHashCode. This is a custom
     * hash code (useful only within ThreadLocalMaps) that eliminates collisions
     * in the common case where consecutively constructed ThreadLocals are used
     * by the same threads, while remaining well-behaved in less common cases.
     *
     * @var float
     */
    private static $currentHashCode = 0;

    /**
     * The difference between successively generated hash codes - turns implicit
     * sequential thread-local IDs into near-optimally spread multiplicative
     * hash values for power-of-two-sized tables.
     *
     * @var float
     */
    private static $hashIncrement = 0x61c88647;

    /**
     * Returns the next hash code.
     *
     * @return float
     */
    private static function nextHashCode()
    {
        $currentHashCode = self::$currentHashCode;
        self::$currentHashCode += self::$hashIncrement;
        return $currentHashCode;
    }

    /**
     * The hash code for this instance.
     *
     * @var float
     */
    private $threadLocalHashCode;

    /**
     * Constructs a new instance of this class.
     */
    public function __construct()
    {
        $this->threadLocalHashCode = self::nextHashCode();
        $GLOBALS[$this->threadLocalHashCode] = null;
    }

    /**
     * Unsets the global variable upon destruction of this instance.
     */
    public function __destruct()
    {
        unset($GLOBALS[$this->threadLocalHashCode]);
    }

    /**
     * Returns the value in the current thread's copy of this thread-local
     * variable.
     *
     * @return mixed
     */
    public function get()
    {
        return $GLOBALS[$this->threadLocalHashCode];
    }

    /**
     * Sets the current thread's copy of this thread-local variable to the
     * specified value.
     *
     * @param mixed $value
     */
    public function set($value)
    {
        $GLOBALS[$this->threadLocalHashCode] = $value;
    }

    /**
     * Removes the current thread's value for this thread-local variable.
     */
    public function remove()
    {
        $GLOBALS[$this->threadLocalHashCode] = null;
    }
}
?>