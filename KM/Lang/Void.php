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

use KM\Lang\Reflect\Type;

/**
 * The <code>Void</code> class is an uninstantiable placeholder class to hold a
 * reference to the <code>Clazz</code> object representing the keyword void.
 *
 * @author Blair
 */
class Void extends Object implements Type
{

    /**
     * THe singleton instance of the <code>Clazz</code> object.
     *
     * @var unknown
     */
    private static $instance;

    /**
     * Returns the <code>Clazz</code> object representing the pseudo-type
     * corresponding to the keyword void.
     *
     * @return \KM\Lang\Clazz
     */
    public static function TYPE()
    {
        if (self::$instance === null) {
            self::$instance = Clazz::getPrimitiveClass('VOID');
        }
        return self::$instance;
    }

    /**
     * The Void class cannot be instantiated.
     */
    private function __construct()
    {}

    public function getTypeName()
    {
        return 'void';
    }
    
    public function getComponentType() {
        return null;
    }

    public function isArray()
    {
        return false;
    }

    public function isMixed()
    {
        return false;
    }

    public function isObject()
    {
        return false;
    }

    public function isPrimitive()
    {
        return true;
    }
}
?>