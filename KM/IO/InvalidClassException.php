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
namespace KM\IO;

/**
 * Thrown when the Serialization runtime detects one of the following problems
 * with a Class. <UL> <LI> The serial version of the class does not match that
 * of the class descriptor read from the stream <LI> The class contains unknown
 * data types <LI> The class does not have an accessible no-arg constructor
 * </UL>
 *
 * @author Blair
 */
class InvalidClassException extends ObjectStreamException
{

    /**
     * Name of the invalid class.
     *
     * @var string
     */
    public $className;

    /**
     * Constructs an InvalidClassException.
     *
     * @param string $reason A string describing the reason for the exception.
     * @param string $className A string naming the invalid class.
     */
    public function __construct($reason, $className = null)
    {
        if (! empty($className)) {
            $this->className = (string) $className;
            parent::__construct($className . ': ' . $reason);
        } else {
            parent::__construct($reason);
        }
    }
}
?>