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
namespace KM\IO\ObjectStreamClass;

use KM\IO\InvalidClassException;
use KM\Lang\Object;

/**
 * Contains information about InvalidClassException instances to be thrown when
 * attempting operations on an invalid class. Note that instances of this class
 * are immutable and are potentially shared among ObjectStreamClass instances.
 *
 * @author Blair
 */
class ExceptionInfo extends Object
{

    private $className;

    private $message;

    public function __construct($cn, $msg)
    {
        $this->className = (string) $cn;
        $this->message = (string) $msg;
    }

    /**
     * Returns (does not throw) an InvalidClassException instance created from
     * the information in this object, suitable for being thrown by the caller.
     *
     * @return \KM\IO\InvalidClassException
     */
    public function newInvalidClassException()
    {
        return new InvalidClassException($this->message, $this->className);
    }
}
?>