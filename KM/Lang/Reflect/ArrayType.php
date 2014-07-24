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

use KM\Lang\Object;

/**
 * A reference enumeration for the array type.
 *
 * @author Blair
 */
class ArrayType extends Object implements Type
{

    /**
     * The component type, if any.
     *
     * @var Type
     */
    private $componentType;

    /**
     * Creates a new ArrayType.
     * If <code>name</code> is specified in the format 'componentType[]', the
     * specified component type will be stored in this ArrayType object. If
     * <code>name</code> omits the '[]' identifier, the component type will be
     * set to 'mixed'.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $pos = strpos($name, '[]');
        if ($pos !== false && $pos > 0) {
            $componentType = ReflectionUtility::typeFor(substr($name, 0, $pos));
        } else {
            $componentType = MixedType::MIXED();
        }
        $this->componentType = $componentType;
    }

    /**
     * Tells whether the given <code>name</code> is defined by this enumeration.
     *
     * @param string $name The name to check.
     * @return boolean True if the name matches one of this enumeration's type
     *         values, false otherwise.
     */
    public static function isKnown($name)
    {
        if ((strpos(strtolower($name), 'array') === 0) ||
             (strpos($name, '[]') !== false)) {
            return true;
        }
        return false;
    }

    /**
     * Returns the <code>Type</code> representing the component type of an
     * array.
     * If this type does not represent an array this method returns null.
     *
     * @return \KM\Lang\Reflect\Type The <code>Type</code> representing the
     *         component type of this type if this type represents an array.
     * @see \KM\Lang\Reflect\Type::getComponentType()
     */
    public function getComponentType()
    {
        return $this->componentType;
    }

    /**
     * Returns an informative string for the name of this type.
     *
     * @return string
     * @see \KM\Lang\Reflect\Type::getTypeName()
     */
    public function getTypeName()
    {
        return 'array_type';
    }

    /**
     * Determines if this <code>Type</code> object represents an array type.
     *
     * @return boolean Always returns <code>true</code>
     * @see \KM\Lang\Reflect\Type::isArray()
     */
    public function isArray()
    {
        return true;
    }

    /**
     * Determines of this <code>Type</code> object represents a mixed type.
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
     * @return boolean Always returns <code>false</code>
     * @see \KM\Lang\Reflect\Type::isPrimitive()
     */
    public function isPrimitive()
    {
        return false;
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
        return $this->getComponentType()->getTypeName() . '[]';
    }
}
?>