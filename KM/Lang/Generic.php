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

use KM\Lang\Clazz;

/**
 * Generic interface that, in particular, permits extended interfaces to impose
 * the contract of the basic object including the <code>getClass</code> method.
 *
 * @author Blair
 */
interface Generic
{

    /**
     * Indicates whether some other object is "equal to" this one. The equals
     * method implements an equivalence relation on non-null object references:
     * It is reflexive: for any non-null reference value x, x.equals(x) should
     * return true. It is symmetric: for any non-null reference values x and y,
     * x.equals(y) should return true if and only if y.equals(x) returns true.
     * It is transitive: for any non-null reference values x, y, and z, if
     * x.equals(y) returns true and y.equals(z) returns true, then x.equals(z)
     * should return true. It is consistent: for any non-null reference values x
     * and y, multiple invocations of x.equals(y) consistently return true or
     * consistently return false, provided no information used in equals
     * comparisons on the objects is modified. For any non-null reference value
     * x, x.equals(null) should return false. The equals method for class Object
     * implements the most discriminating possible equivalence relation on
     * objects; that is, for any non-null reference values x and y, this method
     * returns true if and only if x and y refer to the same object (x == y has
     * the value true).
     *
     * @param Object $obj
     * @return boolean
     */
    public function equals(Object $obj = null);

    /**
     * Returns a hash code value for the object.
     *
     * @return string
     */
    public function hashCode();

    /**
     * Returns the runtime class of this Object. The returned Class object is
     * the object that is locked by static synchronized methods of the
     * represented class.
     *
     * @return \KM\Lang\Clazz The RefleactionClass object that represents the
     *         runtime class of this object.
     */
    public function getClass();
}
?>