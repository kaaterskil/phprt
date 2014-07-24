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

use KM\IO\Serializable;

/**
 * Thrown when an application tries to create an instance of a class using the
 * newInstance() method in Clazz, but the specified class object cannot be
 * instantiated. The instantiation can fail for a variety of reasons including
 * but limited to <ul> <li>the class object represents an abstract class, an
 * interface, an array class, a primitive type or void.</li> <li>the class has
 * no null constructor</li> </ul>
 *
 * @author Blair
 */
class InstantiationException extends \Exception implements Serializable
{

    /**
     * Constructs an InstantionException with the given detail message.
     *
     * @param string $s
     */
    public function __construct($s)
    {
        parent::__construct($s);
    }
}
?>