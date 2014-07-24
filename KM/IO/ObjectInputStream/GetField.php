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
namespace KM\IO\ObjectInputStream;

use KM\IO\IOException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;

/**
 * Provide access to the persistent fields read from the input stream.
 *
 * @author Blair
 */
abstract class GetField extends Object
{

    /**
     * Get the ObjectStreamClass that describes the fields in the stream.
     *
     * @return \KM\IO\ObjectStreamClass The descriptor class that describes the
     *         serializable fields.
     */
    public abstract function getObjectStreamClass();

    /**
     * Returns true if the named field is defaulted and has no value in this
     * stream.
     *
     * @param string $name The name of the field.
     * @return boolean True if and only if the named field is defaulted.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function defaulted($name);

    /**
     * Get the value of the named boolean field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param boolean $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return boolean The value of the named boolean field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getBoolean($name, $val);

    /**
     * Get the value of the named short field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param int $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return int The value of the named short field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getShort($name, $val);

    /**
     * Get the value of the named integer field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param int $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return int The value of the named integer field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getInt($name, $val);

    /**
     * Get the value of the named long field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param int $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return int The value of the named long field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getLong($name, $val);

    /**
     * Get the value of the named float field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param float $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return float The value of the named float field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getFloat($name, $val);

    /**
     * Get the value of the named double field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param float $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return float The value of the named float field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getDouble($name, $val);

    /**
     * Get the value of the named string field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param string $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return string The value of the named string field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getString($name, $val);

    /**
     * Get the value of the named mixed field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param mixed $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return mixed The value of the named mixed field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getMixed($name, $val);

    /**
     * Get the value of the named array field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param array $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return array The value of the named array field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getArray($name, array $val);

    /**
     * Get the value of the named Object field from the persistent field.
     *
     * @param string $name The name of the field.
     * @param Object $val The default value to use if <code>name</code> does
     *        not have a value.
     * @return \KM\Lang\Object The value of the named Object field.
     * @throws IOException if there are I/O errors while reading from the
     *         underlying <code>inputStream</code>.
     * @throws IllegalArgumentException if type of <code>name</code> is not
     *         serializable or if the field type id incorrect.
     */
    public abstract function getObject($name, Object $val);
}
?>