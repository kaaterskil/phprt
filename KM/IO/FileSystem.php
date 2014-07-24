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
 * FileSystem Class
 *
 * @author Blair
 */
abstract class FileSystem extends Object
{

    /**
     * Return the local filesystem's name separator character.
     *
     * @return string
     */
    public abstract function getSeparator();

    /**
     * Return the local filesystem's path separator character.
     *
     * @return string
     */
    public abstract function getPathSeparator();

    /**
     * Convert the given pathname string to normal form. If the string is
     * already in normal form than it is simply returned.
     *
     * @param string $path
     * @return string
     */
    public abstract function normalize($path);

    /**
     * Computes the length of this pathname string's prefix. The pathname must
     * be in normal form.
     *
     * @param string $path
     * @return int
     */
    public abstract function prefixLength($path);

    /**
     * Resolve the child pathname string against the parent. Both strings must
     * be in normal form, and the result will be in normal form.
     *
     * @param string $parent
     * @param string $child
     * @return string
     */
    public abstract function resolve($parent, $child);

    /**
     * Returns the parent pathname string to be used when the parent-directory
     * argument in the File constructor is the empty pathname.
     *
     * @return string
     */
    public abstract function getDefaultParent();

    /**
     * Delete the file or directory denoted by the given abstract pathname,
     * returning true is an only if the operation was successful.
     *
     * @param File $f
     * @return boolean
     */
    public abstract function delete(File $f);

    /**
     * Rename the file or directory denoted by the first abstract pathname to
     * the second abstract pathname, returning <code>true</code> if and only if
     * the operation succeeds.
     *
     * @param File $f1
     * @param File $f2
     * @return boolean
     */
    public abstract function rename(File $f1, File $f2);

    /**
     * Compare two abstract pathnames lexicographically.
     *
     * @param File $f1
     * @param File $f2
     * @return int
     */
    public abstract function compare(File $f1, File $f2);
}
?>