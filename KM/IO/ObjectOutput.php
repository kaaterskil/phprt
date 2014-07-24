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

use KM\Lang\Object;

/**
 * ObjectOutput extends the DataOutput interface to include writing of objects.
 * DataOutput includes methods for output of primitive types, ObjectOutput
 * extends that interface to include objects.
 *
 * @author Blair
 */
interface ObjectOutput extends DataOutput
{

    /**
     * Write an object to the underlying storage or stream. The class that
     * implements this interface defines how the object is written.
     *
     * @param Object $o The object to be written.
     * @throws IOException If an I/O error has occurred.
     */
    public function writeObject(Object $o);

    /**
     * Flushes the stream. This will write any buffered output bytes.
     *
     * @throws IOException If an I/O error has occurred.
     */
    public function flush();

    /**
     * Closes the stream. This method must be called to release any resources
     * associated with the stream.
     *
     * @throws IOException If an I/O error has occurred.
     */
    public function close();
}
?>