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

use KM\Lang\ClassNotFoundException;

/**
 * ObjectInput extends the DataInput interface to include the reading of
 * objects. DataInput includes methods for the input of primitive types,
 * ObjectInput extends that interface to include objects, arrays, and Strings.
 *
 * @author Blair
 */
interface ObjectInput extends DataInput
{

    /**
     * Read and return an object. The class that implements this interface
     * defines where the object is "read" from.
     *
     * @return \KM\Lang\Object The object read from the stream.
     * @throws ClassNotFoundException if the class of a serialized object cannot
     *         be found.
     * @throws IOException if an I/O error has occurred.
     */
    public function readObject();

    /**
     * Reads into an array of bytes. This method will block until some input is
     * available.
     *
     * @param array $b The buffer into which the data is read.
     * @param int $off The start offset of the data.
     * @param int $len The maximum number of bytes read.
     * @return int The actual number of bytes read. -1 is returned when the end
     *         of the stream is reached.
     * @throws IOException if an I/O error has occurred.
     */
    public function read(array &$b, $off = 0, $len = null);

    /**
     * Skips n bytes of input.
     *
     * @param int $n The number of bytes to be skipped.
     * @return int The actual number of bytes skipped.
     * @throws IOException if an I/O error has occurred.
     */
    public function skip($n);

    /**
     * Returns the number of bytes that can be read without blocking.
     *
     * @return int THe number of available bytes.
     * @throws IOException if an I/O error has occurred.
     */
    public function available();

    /**
     * Closes the input stream. Must be called to release any resources
     * associated with the stream.
     *
     * @throws IOException if an I/O error has occurred.
     */
    public function close();
}
?>