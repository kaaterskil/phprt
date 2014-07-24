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
 * Exception indicating the failure of an object read operation due to unread
 * primitive data, or the end of data belonging to a serialized object in the
 * stream. This exception may be thrown in two cases:
 * <ul> <li>An attempt was made to read an object when the next element in the
 * stream is primitive data. In this case, the OptionalDataException's length
 * field is set to the number of bytes of primitive data immediately readable
 * from the stream, and the eof field is set to false.
 * <li>An attempt was made to read past the end of data consumable by a
 * class-defined readObject or readExternal method. In this case, the
 * OptionalDataException's eof field is set to true, and the length field is set
 * to 0. </ul>
 *
 * @author Blair
 */
class OptionalDataException extends ObjectStreamException
{

    /**
     * The number of bytes of primitive data available to be read in the current
     * buffer.
     *
     * @var int
     */
    public $length;

    /**
     * True if there is no more data in the buffered part of the stream.
     *
     * @var boolean
     */
    public $eof;

    public function __construct($val)
    {
        if (is_bool($val)) {
            $this->eof = $val ? true : false;
            $this->length = 0;
        } else {
            $this->eof = false;
            $this->length = (int) $val;
        }
    }
}
?>