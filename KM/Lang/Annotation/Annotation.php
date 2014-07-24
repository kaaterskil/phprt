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
namespace KM\Lang\Annotation;

use KM\Lang\Object;

/**
 * The common interface extended by all annotation types. Note that an interface
 * that manually extends this one does <i>not</i> define an annotation type.
 * Also note that this interface does not itself define an annotation type.
 *
 * @author Blair
 */
interface Annotation
{

    /**
     * Returns true if the specified object represents an annotation that is
     * logically equivalent to this one. In other words, returns true if the
     * specified object is an instance of the same annotation type as this
     * instance, all of whose members are equal to the corresponding member of
     * this annotation
     *
     * @param Object $o The object being compared.
     * @return boolean true if the specified object represents an annotation
     *         that is logically equivalent to this one, otherwise false
     */
    public function equals(Object $o = null);

    /**
     * Returns the hash code of this annotation
     *
     * @return string
     */
    public function hashCode();

    /**
     * Returns a string representation of this annotation.
     *
     * @return string A string representation of this annotation.
     */
    public function __toString();

    /**
     * Returns the annotation type of this annotation.
     *
     * @return \KM\Lang\Clazz The annotation type of this annotation.
     */
    public function annotationType();
}
?>