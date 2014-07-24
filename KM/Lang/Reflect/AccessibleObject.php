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

/**
 * The AccessibleObject interface for Field and Method objects provides the
 * ability to flag a reflected object as suppressing default access control
 * checks when it is used. The access checks -- for public, protected and
 * private members -- are performed when Fields or Methods are used to set or
 * get field, to invoke methods or to create and initialize new instances of
 * classes.
 *
 * @author Blair
 */
interface AccessibleObject extends AnnotatedElement
{
    
    /**
     * Returns the underlying reflector
     * @return mixed
     */
    public function getReflector();

    /**
     * Set the <code>accessible</code> flag for this object tp the indicated
     * boolean value. A value of <code>true</code> indicates that the reflected
     * object should suppress access checking when it is used. A value of
     * <code>false</code> indicates that the reflected object should enforce
     * access checks.
     *
     * @param boolean $flag The new value for the <code>accessible</code> flag.
     */
    public function setAccessible($flag);

    /**
     * Checks if the method is private.
     *
     * @return boolean <code>true</code> if the member is private,
     *         <code>false</code> otherwise,
     */
    public function isPrivate();

    /**
     * Checks if the method is protected.
     *
     * @return boolean <code>true</code> if the member is protected,
     *         <code>false</code> otherwise,
     */
    public function isProtected();

    /**
     * Checks if the method is public.
     *
     * @return boolean <code>true</code> if the member is public,
     *         <code>false</code> otherwise,
     */
    public function isPublic();
}
?>