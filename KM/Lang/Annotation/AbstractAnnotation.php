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

use KM\Lang\Clazz;
use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;

/**
 * Abstract implementation of the Annotation interface.
 *
 * @author Blair
 */
abstract class AbstractAnnotation extends Object implements Annotation
{

    /**
     * Value property. Common among all derived classes.
     *
     * @var string
     */
    public $value;

    /**
     * Constructor
     *
     * @param array $data Key-value for properties to be defined in this class
     */
    public final function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Error handler for unknown property accessor in Annotation class.
     *
     * @param string $name Unknown property name
     * @throws UnsupportedOperationException
     */
    public function __get($name)
    {
        throw new UnsupportedOperationException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this)));
    }

    /**
     * Error handler for unknown property mutator in Annotation class.
     *
     * @param string $name Unknown property name
     * @param mixed $value Property value
     * @throws UnsupportedOperationException
     */
    public function __set($name, $value)
    {
        throw new UnsupportedOperationException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this)));
    }

    /**
     * Error handler for unknown property accessor in Annotation class.
     *
     * @param string $name Unknown property name
     * @throws UnsupportedOperationException
     */
    public function __unset($name)
    {
        throw new UnsupportedOperationException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this)));
    }

    /**
     * Returns the annotation type of this annotation.
     *
     * @return \KM\Lang\Clazz The annotation type of this annotation.
     * @see \KM\Lang\Annotation\Annotation::annotationType()
     */
    public function annotationType()
    {
        return $this->getClass();
    }
}
?>