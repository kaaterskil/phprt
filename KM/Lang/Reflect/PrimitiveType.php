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
namespace KM\Lang\Reflect;

use KM\Lang\Enum;

/**
 * A reference enumeration for primitive types.
 *
 * @author Blair
 */
class PrimitiveType extends Enum implements Type
{

    /**
     * Designates a boolean.
     *
     * @var string
     */
    const BOOLEAN = 'boolean';

    /**
     * Designates a 16-bit (2 byte) integer.
     * This type is expressed as a 32-bit integer within a running PHP machine
     * but may be packed to and unpacked from a 16-bit persistent sate.
     *
     * @var string
     */
    const SHORT = 'short';

    /**
     * Designates a 32-bit (4 byte) integer.
     *
     * @var string
     */
    const INTEGER = 'integer';

    /**
     * Designates an 64-bit (8 byte) integer.
     * This type is expressed as a 32-bit integer within a running PHP machine.
     *
     * @var string
     */
    const LONG = 'long';

    /**
     * Designates a 32-bit (4 byte) floating point number.
     *
     * @var string
     */
    const FLOAT = 'float';

    /**
     * Designates an 64-bit (8 byte) double floating point number.
     * This type is expressed as a 32-bit float within a running PHP machine.
     *
     * @var string
     */
    const DOUBLE = 'double';

    /**
     * Designates a variable length primitive string (byte array).
     *
     * @var string
     */
    const STRING = 'string';

    /**
     * The list of accepted primitive names.
     *
     * @var string[]
     */
    private static $acceptedNames = [
        'bool',
        'boolean',
        'int',
        'integer',
        'double',
        'float',
        'string',
        'void',
        'null'
    ];

    /**
     * Tells whether the given <code>name</code> is defined by this enumeration.
     *
     * @param string $name The name to check.
     * @return boolean True if the name matches one of this enumeration's type
     *         values, false otherwise.
     */
    public static function isKnown($name)
    {
        return in_array(strtolower($name), self::$acceptedNames);
    }

    /**
     * Returns an informative string for the name of this type.
     *
     * @return string
     * @see \KM\Lang\Reflect\Type::getTypeName()
     */
    public function getTypeName()
    {
        return $this->getValue();
    }

    /**
     * Returns the <code>Type</code> representing the component type of an
     * array.
     * If this type does not represent an array this method returns null.
     *
     * @return Type The <code>Type</code> representing the component type of
     *         this type if this type represents an array.
     * @see \KM\Lang\Reflect\Type::getComponentType()
     */
    public function getComponentType()
    {
        return null;
    }

    /**
     * Determines if this <code>Type</code> object represents an array type.
     *
     * @return boolean Always returns <code>false</code>
     * @see \KM\Lang\Reflect\Type::isArray()
     */
    public function isArray()
    {
        return false;
    }

    /**
     * Determines of this <code>Type</code> object represents a mixed type.
     * A mixed type in PHP is a pseudo type that accepts multiple types, both
     * primitive, array and object types.
     *
     * @return boolean Always returns <code>false</code>
     * @see \KM\Lang\Reflect\Type::isMixed()
     */
    public function isMixed()
    {
        return false;
    }

    /**
     * Determines if the specified <code>Type</code> object represents a
     * primitive type.
     *
     * @return boolean Always returns <code>true</code>
     * @see \KM\Lang\Reflect\Type::isPrimitive()
     */
    public function isPrimitive()
    {
        return true;
    }

    /**
     * Returns true if the type is an object, false otherwise.
     *
     * @return boolean Always returns <code>false</code>
     * @see \KM\Lang\Reflect\Type::isObject()
     */
    public function isObject()
    {
        return false;
    }

    /**
     * Returns a string representation of this type.
     *
     * @return string A string representation of this type.
     * @see \KM\Lang\Enum::__toString()
     */
    public function __toString()
    {
        return __CLASS__ . '[' . $this->getName() . ']';
    }
}
?>