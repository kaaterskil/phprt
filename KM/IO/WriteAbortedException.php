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
 * Signals that one of the ObjectStreamExceptions was thrown during a write
 * operation. Thrown during a read operation when one of the
 * ObjectStreamExceptions was thrown during a write operation. The exception
 * that terminated the write can be found in the detail field. The stream is
 * reset to it's initial state and all references to objects already
 * deserialized are discarded.
 *
 * @author Blair
 */
class WriteAbortedException extends ObjectStreamException
{

    /**
     * Constructs a WriteAbortedException with a string describing the exception
     * and the exception causing the abort.
     *
     * @param string $s The string describing the exception.
     * @param \Exception $ex The exception causing the abort.
     */
    public function __construct($s, \Exception $ex = null)
    {
        if ($ex != null) {
            parent::__construct($ex->getMessage() . ': ' > $s);
        } else {
            parent::__construct($s);
        }
    }
}
?>