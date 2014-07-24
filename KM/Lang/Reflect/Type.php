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
 * Basic interface for type references.
 *
 * @author Blair
 */
interface Type
{

    /**
     * Return an informative string for the name of this type.
     *
     * @return string An informative string for the name of this type.
     */
    public function getTypeName();

    /**
     * Returns the <code>Type</code> representing the component type of an
     * array.
     * If this type does not represent an array this method returns null.
     *
     * @return \KM\Lang\Reflect\Type The <code>Type</code> representing the
     *         component type of this type if this type represents an array.
     */
    public function getComponentType();

    /**
     * Determines if this <code>Type</code> object represents an array type.
     *
     * @return boolean <code>True</code> if this object represents an array
     *         type; <code>false> otherwise.
     */
    public function isArray();

    /**
     * Determines of this <code>Type</code> object represents a mixed type.
     * A mixed type in PHP is a pseudo type that accepts multiple types, both
     * primitive, array and object types.
     *
     * @return boolean <code>True</code> if this object represents a mixed
     *         type; <code>false> otherwise.
     */
    public function isMixed();

    /**
     * Determines if the specified <code>Type</code> object represents a
     * primitive type.
     *
     * There are five predefined <code>Type</code> objects to represent the four
     * primitive PHP types and Void, namely <code>boolean</code>,
     * <code>integer</code>, <code>float</code>, and <code>string</code>.
     *
     * The 2-byte <code>short</code> and 8-byte <code>long</code> integer
     * resolve to the 4-byte PHP <code>integer</code> type. Similarly, the
     * 8-byte <code>double</code> resolves to the 4-byte PHP <code>float</code>
     * type.
     *
     * @return boolean <code>True</code> if this object represents a primitive
     *         type; <code>false> otherwise.
     */
    public function isPrimitive();

    /**
     * Returns true if the type is an object, false otherwise.
     *
     * @return boolean <code>True</code> if this object represents an object
     *         type; <code>false> otherwise.
     */
    public function isObject();
}
?>