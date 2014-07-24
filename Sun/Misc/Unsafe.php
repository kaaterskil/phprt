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
namespace Sun\Misc;

use KM\Lang\Clazz;
use KM\Lang\Object;
use KM\Lang\Reflect\Field;
use KM\Lang\Reflect\MixedType;
use KM\Lang\Reflect\PrimitiveType;
use KM\Lang\RuntimeException;

/**
 * A collection of methods for performing low-level, unsafe operations. Although
 * the class and all methods are public, use of this class is limited because
 * only trusted code can obtain instances of it.
 *
 * @author Blair
 */
final class Unsafe extends Object
{

    /**
     * The singleton instance
     *
     * @var Unsafe
     */
    private static $theUnsafe;

    /**
     * Return the singleton instance
     *
     * @return \Sun\Misc\Unsafe
     */
    public static function getUnsafe()
    {
        if (self::$theUnsafe === null) {
            self::$theUnsafe = new self();
        }
        return self::$theUnsafe;
    }

    /**
     * Private constructor.
     */
    private function __construct()
    {}

    /**
     * Returns a reference value from the given variable.
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @throws RuntimeException
     * @return boolean The value fetched from the indicated variable.
     */
    public function getBoolean(Object $o, $key)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::BOOLEAN()) {
                $f->setAccessible(true);
                return (boolean) $f->get($o);
            }
            return null;
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Stores a value into a given variable
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @param boolean $x The value to store into the indicated variable.
     * @throws RuntimeException
     */
    public function putBoolean(Object $o, $key, $x = null)
    {
        $x = (boolean) $x;
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::BOOLEAN()) {
                $f->setAccessible(true);
                $f->set($o, $x);
            }
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Returns a reference value from the given variable.
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @throws RuntimeException
     * @return int The value fetched from the indicated variable.
     */
    public function getInt(Object $o, $key)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::INTEGER()) {
                $f->setAccessible(true);
                return (int) $f->get($o);
            }
            return null;
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Stores a value into a given variable
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @param int $x The value to store into the indicated variable.
     * @throws RuntimeException
     */
    public function putInt(Object $o, $key, $x = null)
    {
        $x = (int) $x;
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::INTEGER()) {
                $f->setAccessible(true);
                $f->set($o, $x);
            }
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Returns a reference value from the given variable.
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @throws RuntimeException
     * @return float The value fetched from the indicated variable.
     */
    public function getFloat(Object $o, $key)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::FLOAT()) {
                $f->setAccessible(true);
                return (float) $f->get($o);
            }
            return null;
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Stores a value into a given variable
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @param float $x The value to store into the indicated variable.
     * @throws RuntimeException
     */
    public function putFloat(Object $o, $key, $x = null)
    {
        $x = (float) $x;
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::FLOAT()) {
                $f->setAccessible(true);
                $f->set($o, $x);
            }
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Returns a reference value from the given variable.
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @throws RuntimeException
     * @return float The value fetched from the indicated variable.
     */
    public function getDouble(Object $o, $key)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::DOUBLE()) {
                $f->setAccessible(true);
                return (float) $f->get($o);
            }
            return null;
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Stores a value into a given variable
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @param float $x The value to store into the indicated variable.
     * @throws RuntimeException
     */
    public function putDouble(Object $o, $key, $x)
    {
        $x = (float) $x;
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::DOUBLE()) {
                $f->setAccessible(true);
                $f->set($o, $x);
            }
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Returns a reference value from the given variable.
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @throws RuntimeException
     * @return string The value fetched from the indicated variable.
     */
    public function getString(Object $o, $key)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::STRING()) {
                $f->setAccessible(true);
                return (string) $f->get($o);
            }
            return null;
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Stores a value into a given variable
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @param string $x The value to store into the indicated variable.
     * @throws RuntimeException
     */
    public function putString(Object $o, $key, $x = null)
    {
        $x = (string) $x;
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::STRING()) {
                $f->setAccessible(true);
                $f->set($o, $x);
            }
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Returns a reference value from the given variable.
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @throws RuntimeException
     * @return array The value fetched from the indicated variable.
     */
    public function getArray(Object $o, $key)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::ARRAY_TYPE()) {
                $f->setAccessible(true);
                $result = $f->get($o);
                if (! is_array($result)) {
                    $result = [
                        $result
                    ];
                }
                return $result;
            }
            return null;
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Stores a value into a given variable
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @param array $x The value to store into the indicated variable.
     * @throws RuntimeException
     */
    public function putArray(Object $o, $key, array $x = null)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == PrimitiveType::ARRAY_TYPE()) {
                $f->setAccessible(true);
                $f->set($o, $x);
            }
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Returns a reference value from the given variable.
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @throws RuntimeException
     * @return mixed The value fetched from the indicated variable.
     */
    public function getMixed(Object $o, $key)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == MixedType::MIXED()) {
                $f->setAccessible(true);
                return $f->get($o);
            }
            return null;
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Stores a value into a given variable
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @param mixed $x The value to store into the indicated variable.
     * @throws RuntimeException
     */
    public function putMixed(Object $o, $key, $x = null)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() == MixedType::MIXED()) {
                $f->setAccessible(true);
                $f->set($o, $x);
            }
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Returns a reference value from the given variable.
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @throws RuntimeException
     * @return Object The value fetched from the indicated variable.
     */
    public function getObject(Object $o, $key)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() instanceof Clazz) {
                $f->setAccessible(true);
                return $f->get($o);
            }
            return null;
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }

    /**
     * Stores a value into a given variable
     *
     * @param Object $o The object in which the variable resides.
     * @param string $key The name of the object field.
     * @param Object $x The value to store into the indicated variable.
     * @throws RuntimeException
     */
    public function putObject(Object $o, $key, Object $x = null)
    {
        try {
            $cl = $o->getClass();
            $f = $cl->getField($key);
            if ($f->getType() instanceof Clazz) {
                $f->setAccessible(true);
                $f->set($o, $x);
            }
        } catch (\Exception $e) {
            throw new RuntimeException();
        }
    }
}
?>