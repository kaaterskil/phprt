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
 * Thrown to indicate that a method has been passed an illegal or inappropriate
 * argument.
 *
 * @author Blair
 */
class IllegalArgumentException extends RuntimeException
{

    /**
     * Constructs a new exception with the specified detail message, exception
     * code and cause. Note that the detail message associated with
     * <code>cause</code> is <em>not</em> automatically incorporated in this
     * exception's detail message.
     *
     * @param string $message The detail message (which is saved for later
     *            retrieval by the <code>getMessage()</code> method).
     * @param int $code A user-defined exception code.
     * @param \Exception $cause The cause (which is saved for later retrieval by
     *            the <code>getPrevious()</code> method). A null value is
     *            permitted and indicates that the cause is nonexistent or
     *            unknown.
     */
    public function __construct($message = '', $code = 0, \Exception $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
?>