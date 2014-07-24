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

use KM\Lang\Clazz;

/**
 * Member is an interface that reflects identifying information about a single
 * member (a field or a method) or a constructor.
 *
 * @author Blair
 */
interface Member
{

    /**
     * Identifies the set of all public members of a class or interface
     * including inherited members.
     *
     * @var int
     */
    const PUBLIC_MEMBER = 0;

    /**
     * Identifies the set of declared members of a class or interface. Inherited
     * members are not included.
     *
     * @var int
     */
    const DECLARED_MEMBER = 1;

    /**
     * Returns the CLazz object representing the class or interface that
     * declares the member or constructor represented by this Member.
     *
     * @return \KM\Lang\Clazz
     */
    public function getDeclaringClass();

    /**
     * Returns the simple name of the underlying member or constructor
     * represented by this Member.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the modifiers for the member or constructor represented by this
     * Member as an integer,
     *
     * @return int
     */
    public function getModifiers();

    /**
     * Checks if the member is final.
     *
     * @return boolean <code>true</code> if the member is final,
     *         <code>false</code> otherwise,
     */
    public function isFinal();
}
?>