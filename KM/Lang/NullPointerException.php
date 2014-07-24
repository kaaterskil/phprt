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

/**
 * Thrown when an application attempts to use null in a case where an object is
 * required. These include: <ul> <li>Calling the instance method of a null
 * object.</li> <li>Accessing or modifying the field of a null object.</li>
 * <li>Taking the length of null as if it were an array.</li> <li>Accessing or
 * modifying the elements of null as if it were an array.</li> </ul>
 * Applications should throw instances of this class to indicate other illegal
 * uses of null.
 *
 * @author Blair
 */
class NullPointerException extends RuntimeException
{

    /**
     * Constructs a NullPointerException with the optional specified detail
     * message.
     *
     * @param string $s The detail message.
     */
    public function __construct($s = '')
    {
        parent::__construct($s);
    }
}
?>