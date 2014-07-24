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
use Doctrine\Common\Annotations\Annotation;

/**
 * Represents an annotated element. This interface allows annotations to be read
 * reflectively. All annotations returned by this interface are immutable.
 *
 * @author Blair
 */
interface AnnotatedElement
{

    /**
     * Returns true if an annotation for the specified type is present on this
     * element, else false. This method is designed primarily for convenient
     * access to marker annotations. The truth value returned by this method is
     * equivalent to <code>getAnnoptation(annotationType) != null</code>.
     *
     * @param string $annotationName The name of the annotation.
     * @return boolean True if an annotation for the specified annotation type
     *         is present on this element, else false.
     */
    public function isAnnotationPresent($annotationName);

    /**
     * Returns this element's annotation for the specified name is such an
     * annotation is present, else null.
     *
     * @param string $annotationName The name of the annotation to query for and
     *            return if present.
     * @return \Doctrine\Common\Annotations\Annotation This element's annotation
     *         for the specified annotation type if present on this element,
     *         else null.
     */
    public function getAnnotation($annotationName);

    /**
     * Returns annotations that are present on this element. If there are no
     * annotations present on this element, the return value is an array of
     * length 0.
     *
     * @return \Doctrine\Common\Annotations\Annotation[]
     */
    public function getAnnotations();
}
?>