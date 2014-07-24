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

use KM\Lang\System;

/**
 * FileSystem Class
 *
 * @author Blair
 */
class WinFileSystem extends FileSystem
{

    private $slash;

    private $altSlash;

    private $semicolon;

    public function __construct()
    {
        $this->slash = System::getProperty('file.separator');
        $this->semicolon = System::getProperty('path.separator');
        $this->altSlash = ($this->slash == '\\') ? '/' : '\\';
    }

    private function isSlash($c)
    {
        return ($c == '\\') || ($c == '/');
    }

    private function isLetter($c)
    {
        return (($c >= 'a') && ($c <= 'z')) || (($c >= 'A') && ($c <= 'Z'));
    }

    private function slashify($p)
    {
        $p = (string) $p;
        if ((strlen($p) > 0) && ($p[0] != $this->slash)) {
            return $this->slash . $p;
        }
        return $p;
    }
    
    /* ---------- Normalization and construction ---------- */
    
    /**
     * Return the local filesystem's name-separator character.
     *
     * @return string
     * @see \KM\IO\FileSystem::getSeparator()
     */
    public function getSeparator()
    {
        return $this->slash;
    }

    /**
     * Return the local filesystem's path-separator character.
     *
     * @return string
     * @see \KM\IO\FileSystem::getPathSeparator()
     */
    public function getPathSeparator()
    {
        return $this->semicolon;
    }

    /**
     * Check that the given pathname is normal. If not, invoke the real
     * normalizer on the part of the pathname that requires normalization. This
     * way we iterate through the whole pathname string only once.
     *
     * @param string $path
     * @return string
     * @see \KM\IO\FileSystem::normalize()
     */
    public function normalize($path)
    {
        $path = (string) $path;
        $n = strlen($path);
        $slash = $this->slash;
        $altSlash = $this->altSlash;
        $prev = 0;
        for ($i = 0; $i < $n; $i ++) {
            $c = $path[$i];
            if ($c == $altSlash) {
                return $this->normalize0($path, $n, ($prev == $slash) ? $i - 1 : $i);
            }
            if (($c == $slash) && ($prev == $slash) && ($i > 1)) {
                return $this->normalize0($path, $n, $i - 1);
            }
            if (($c == ':') && ($i > 1)) {
                return $this->normalize0($path, $n, 0);
            }
            $prev = $c;
        }
        if ($prev == $slash) {
            return $this->normalize0($path, $n, $n - 1);
        }
        return $path;
    }

    /**
     * Normalize the given pathname, whose length is $len, starting at the given
     * offset; everything before this offset is already normal.
     *
     * @param string $path
     * @param int $len
     * @param int $off
     * @return string
     */
    private function normalize0($path, $len, $off)
    {
        if ($len == 0) {
            return $path;
        }
        if ($off < 3) {
            $off = 0;
        }
        $src = null;
        $slash = $this->slash;
        $sb = '';
        
        if ($off == 0) {
            $src = $this->normalizePrefix($path, $len, $sb);
        } else {
            $src = $off;
            $sb .= substr($path, 0, $off);
        }
        
        // Remove redundant slashes from the remainder of the path, forcing all
        // slashes into the
        // preferred slash
        while ($src < $len) {
            $c = $path[$src ++];
            if ($this->isSlash($c)) {
                while (($src < $len) && $this->isSlash($path[$src])) {
                    $src ++;
                }
                if ($src == $len) {
                    $sn = strlen($sb);
                    if (($sn == 2) && ($sb[1] == ':')) {
                        /* "z:\\" */
                        $sb .= $slash;
                        break;
                    }
                    if ($sn == 0) {
                        /* "\\" */
                        $sb .= $slash;
                        break;
                    }
                    if (($sn == 1) && ($this->isSlash($sb[0]))) {
                        /*
                         * "\\\\" is not collapsed to "\\" because "\\\\" marks
                         * the beginning of a UNC pathname. Even though it is
                         * not, by itself, a valid UNC pathname, we leave it as
                         * is in order to be consistent with the win32 APIs,
                         * which treat this case as an invalid UNC pathname
                         * rather than as an alias for the root directory of the
                         * current drive.
                         */
                        $sb .= $slash;
                        break;
                    }
                    // Path does not denote a root directory, so do not append
                    // trailing slash.
                    break;
                } else {
                    $sb .= $slash;
                }
            } else {
                $sb .= $c;
            }
        }
        $rv = $sb;
        return $rv;
    }

    /**
     * A normal Win32 pathname contains no duplicate slashes, except possibly
     * for a UNC prefix, and does not end with a slash. It may be the empty
     * string. Normalized Win32 pathnames have the convenient property that the
     * length of the prefix almost uniquely identifies the type of the path and
     * whether it is absolute or relative: <ul> <li>0 relative to both drive and
     * directory</li> <li>1 drive-relative (begins with '\\')</li> <li>2
     * absolute UNC (if first char is '\\'), else directory-relative (has form
     * "z:foo")</li> <li>3 absolute local pathname (begins with "z:\\")</li>
     * </ul>
     *
     * @param string $path
     * @param int $len
     * @param string $sb
     * @return int
     */
    private function normalizePrefix($path, $len, &$sb)
    {
        $src = 0;
        while (($src < $len) && $this->isSlash($path[$src])) {
            $src ++;
        }
        $c = null;
        if (($len - $src >= 2) && $this->isLetter($c = $path[$src]) && $path[$src + 1] == ':') {
            /*
             * Remove leading slashes if followed by drive specifier. This hack
             * is necessary to support file URLs containing drive specifiers
             * (e.g. "file//c:/path"). As a side effect, "/c:/path" can be used
             * as an alternative to "c:/path"
             */
            $sb .= $c . ':';
            $src += 2;
        } else {
            $src = 0;
            if (($len >= 2) && $this->isSlash($path[0]) && $this->isSlash($path[1])) {
                /*
                 * UNC pathname: Retain the first slash; leave $src pointed at
                 * the second slash so that further slashes will be collapsed
                 * into the second slash. The result will be a pathname
                 * beginning with "\\\\" followed most likely by a host name.
                 */
                $src = 1;
                $sb .= $this->slash;
            }
        }
        return $src;
    }

    /**
     * Computes the length of this pathname string's prefix. The pathname must
     * be in normal form.
     *
     * @param string $path
     * @return int
     * @see \KM\IO\FileSystem::prefixLength()
     */
    public function prefixLength($path)
    {
        $path = (string) $path;
        $slash = $this->slash;
        $n = strlen($path);
        if ($n == 0) {
            return 0;
        }
        $c0 = $path[0];
        $c1 = ($n > 1) ? $path[1] : 0;
        if ($c0 == $slash) {
            if ($c1 == $slash) {
                return 2; // Absolute UNC pathname "\\\\foo".
            }
            return 1; // Drive relative "\\foo".
        }
        if ($this->isLetter($c0) && ($c1 == ':')) {
            if (($n > 2) && ($path[2] == $slash)) {
                return 3; // ; Absolute local pathname "z:\\foo".
            }
            return 2; // Directory-relative "z:foo".
        }
        return 0; // Completely relative.
    }

    /**
     * Resolve the child pathname string against the parent. Both strings must
     * be in normal form, and the result will be in normal form.
     *
     * @param string $parent
     * @param string $child
     * @return string
     * @see \KM\IO\FileSystem::resolve()
     */
    public function resolve($parent, $child)
    {
        $parent = (string) $parent;
        $child = (string) $child;
        $pn = strlen($parent);
        if ($pn == 0) {
            return $child;
        }
        $cn = strlen($child);
        if ($cn == 0) {
            return $parent;
        }
        
        $c = $child;
        $childStart = 0;
        $parentEnd = $pn;
        
        if (($cn > 1) && ($c[0] == $this->slash)) {
            if ($c[1] == $this->slash) {
                $childStart = 2;
            } else {
                $childStart = 1;
            }
            if ($cn == $childStart) {
                if ($parent[$pn - 1] == $this->slash) {
                    return substr($parent, 0, $pn - 1);
                }
                return $parent;
            }
        }
        
        if ($parent[$pn - 1] == $this->slash) {
            $parentEnd --;
        }
        
        $strlen = $parentEnd + $cn - $childStart;
        $theChars = '';
        if ($child[$childStart] == $this->slash) {
            $theChars = substr($parent, 0, $parentEnd);
            $theChars .= substr($child, $childStart, $cn);
        } else {
            $theChars = substr($parent, 0, $parentEnd);
            $theChars .= $this->slash;
            $theChars .= substr($child, $childStart, $cn);
        }
        return $theChars;
    }

    /**
     * Returns the parent pathname string to be used when the parent-directory
     * argument in the File constructor is the empty pathname.
     *
     * @return string
     * @see \KM\IO\FileSystem::getDefaultParent()
     */
    public function getDefaultParent()
    {
        return '' . $this->slash;
    }

    /**
     * Delete the file or directory denoted by the given abstract pathname,
     * returning true is an only if the operation was successful.
     *
     * @param File $f
     * @return boolean
     * @see \KM\IO\FileSystem::delete()
     */
    public function delete(File $f)
    {
        return $this->delete0($f);
    }

    private function delete0(File $f)
    {
        return unlink($f->getPath());
    }

    /**
     * Rename the file or directory denoted by the first abstract pathname to
     * the second abstract pathname, returning <code>true</code> if and only if
     * the operation succeeds.
     *
     * @param File $f1
     * @param File $f2
     * @return boolean
     * @see \KM\IO\FileSystem::rename()
     */
    public function rename(File $f1, File $f2)
    {
        return rename($f1->getPath(), $f2->getPath());
    }

    /**
     * Compares two abstract pathnames lexicographically.
     *
     * @param File $f1
     * @param File $f2
     * @return int
     * @see \KM\IO\FileSystem::compare()
     */
    public function compare(File $f1, File $f2)
    {
        return strtolower($f1->getPath()) - strtolower($f2->getPath());
    }
}
?>