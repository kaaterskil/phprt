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

use KM\IO\File\PathStatus;
use KM\Lang\ClassCastException;
use KM\Lang\Comparable;
use KM\Lang\NullPointerException;
use KM\Lang\Object;

/**
 * An abstract representation of file and directory pathnames. <p> User
 * interfaces and operating systems use system-dependent <em>pathname
 * strings</em> to name files and directories. This class presents an abstract,
 * system-independent view of hierarchical pathnames. An <em>abstract
 * pathname</em> has two components: <ol> <li> An optional system-dependent
 * <em>prefix</em> string, such as a disk-drive specifier,
 * <code>"/"</code>&nbsp;for the UNIX root directory, or
 * <code>"\\\\"</code>&nbsp;for a Microsoft Windows UNC pathname, and <li> A
 * sequence of zero or more string <em>names</em>. </ol> The first name in an
 * abstract pathname may be a directory name or, in the case of Microsoft
 * Windows UNC pathnames, a hostname. Each subsequent name in an abstract
 * pathname denotes a directory; the last name may denote either a directory or
 * a file. The <em>empty</em> abstract pathname has no prefix and an empty name
 * sequence. <p> The conversion of a pathname string to or from an abstract
 * pathname is inherently system-dependent. When an abstract pathname is
 * converted into a pathname string, each name is separated from the next by a
 * single copy of the default <em>separator character</em>. The default
 * name-separator character is defined by the system property
 * <code>file.separator</code>, and is made available in the public static
 * fields <code>{@link #separator}</code> and <code>{@link
 * #separatorChar}</code> of this class. When a pathname string is converted
 * into an abstract pathname, the names within it may be separated by the
 * default name-separator character or by any other name-separator character
 * that is supported by the underlying system. <p> A pathname, whether abstract
 * or in string form, may be either <em>absolute</em> or <em>relative</em>. An
 * absolute pathname is complete in that no other information is required in
 * order to locate the file that it denotes. A relative pathname, in contrast,
 * must be interpreted in terms of information taken from some other pathname.
 * By default the classes in the <code>java.io</code> package always resolve
 * relative pathnames against the current user directory. This directory is
 * named by the system property <code>user.dir</code>, and is typically the
 * directory in which the Java virtual machine was invoked. <p> The
 * <em>parent</em> of an abstract pathname may be obtained by invoking the
 * {@link #getParent} method of this class and consists of the pathname's prefix
 * and each name in the pathname's name sequence except for the last. Each
 * directory's absolute pathname is an ancestor of any <tt>File</tt> object with
 * an absolute abstract pathname which begins with the directory's absolute
 * pathname. For example, the directory denoted by the abstract pathname
 * <tt>"/usr"</tt> is an ancestor of the directory denoted by the pathname
 * <tt>"/usr/local/bin"</tt>. <p> The prefix concept is used to handle root
 * directories on UNIX platforms, and drive specifiers, root directories and UNC
 * pathnames on Microsoft Windows platforms, as follows: <ul> <li> For UNIX
 * platforms, the prefix of an absolute pathname is always <code>"/"</code>.
 * Relative pathnames have no prefix. The abstract pathname denoting the root
 * directory has the prefix <code>"/"</code> and an empty name sequence. <li>
 * For Microsoft Windows platforms, the prefix of a pathname that contains a
 * drive specifier consists of the drive letter followed by <code>":"</code> and
 * possibly followed by <code>"\\"</code> if the pathname is absolute. The
 * prefix of a UNC pathname is <code>"\\\\"</code>; the hostname and the share
 * name are the first two names in the name sequence. A relative pathname that
 * does not specify a drive has no prefix. </ul> <p> Instances of this class may
 * or may not denote an actual file-system object such as a file or a directory.
 * If it does denote such an object then that object resides in a
 * <i>partition</i>. A partition is an operating system-specific portion of
 * storage for a file system. A single storage device (e.g. a physical
 * disk-drive, flash memory, CD-ROM) may contain multiple partitions. The
 * object, if any, will reside on the partition <a name="partName">named</a> by
 * some ancestor of the absolute form of this pathname. <p> A file system may
 * implement restrictions to certain operations on the actual file-system
 * object, such as reading, writing, and executing. These restrictions are
 * collectively known as <i>access permissions</i>. The file system may have
 * multiple sets of access permissions on a single object. For example, one set
 * may apply to the object's <i>owner</i>, and another may apply to all other
 * users. The access permissions on an object may cause some methods in this
 * class to fail. <p> Instances of the <code>File</code> class are immutable;
 * that is, once created, the abstract pathname represented by a
 * <code>File</code> object will never change.
 *
 * @author Blair
 */
class File extends Object implements Comparable
{

    /**
     * The filesystem object representing the platform's local file system.
     *
     * @var FileSystem
     */
    private static $fs;

    /**
     * This pathname's normalized pathname string. A normalized pathname string
     * uses the default name-separator character and does not contain any
     * duplicate or redundant separators.
     *
     * @var String
     */
    private $path;

    /**
     * The flag indicating whether the file path is invalid.
     *
     * @var string
     */
    private $status = null;

    /**
     * Check if the file has an invalid path. Currently, the inspection of a
     * file path is very limited, and it only covers null character check.
     * Returning true means the path is definitely invalid/garbage. But
     * returning false does not guarantee that the path is valid.
     *
     * @return boolean True if the file path is invalid.
     */
    public final function isInvalid()
    {
        if ($this->status === null) {
            if (file_exists($this->getPath())) {
                $this->status = 'checked';
            } else {
                $this->status = 'invalid';
            }
        }
        return $this->status == 'invalid';
    }

    /**
     * The length of this pathname's prefix, or zero if it has no prefix.
     *
     * @var int
     */
    private $prefixLength;

    /**
     * Returns the length of this pathname's prefix.
     *
     * @return int
     */
    public function getPrefixLength()
    {
        return $this->prefixLength;
    }

    /**
     * The system-dependent default name-separator character. This field is
     * initialized to contain the first character of the value of the system
     * property <code>file.separator</code>. On UNIX systems the value of this
     * field is <code>'/'</code>; on Microsoft Windows systems it is
     * <code>'\\'</code>.
     *
     * @var string
     */
    public static $separatorChar;

    /**
     * The system-dependent default name-separator character, represented as a
     * string for convenience. This string contains a single character, namely
     * $separatorChar.
     *
     * @var string
     */
    public static $separator;

    /**
     * The system-dependent path-separator character. This field is initialized
     * to contain the first character of the value of the system property
     * <code>path.separator</code>. This character is used to separate filenames
     * in a sequence of files given as a <em>path list</em>. On UNIX systems,
     * this character is <code>':'</code>; on Microsoft Windows systems it is
     * <code>';'</code>.
     *
     * @var string
     */
    public static $pathSeparatorChar;

    /**
     * The system-dependent path-separator character, represented as a string
     * for convenience. This string contains a single character, namely
     * $pathSeparatorChar.
     *
     * @var string
     */
    public static $pathSeparator;

    /**
     * Static constructor.
     */
    public static function clinit()
    {
        self::$fs = DefaultFileSystem::getFileSystem();
        self::$separatorChar = self::$fs->getSeparator();
        self::$separator = '' . self::$separatorChar;
        self::$pathSeparatorChar = self::$fs->getPathSeparator();
        self::$pathSeparator = '' . self::$pathSeparatorChar;
    }
    
    /* ---------- Constructor ---------- */
    
    /**
     * Creates a File instance by converting the given pathname into an abstract
     * pathname, If the given string is an empty string, then the result is the
     * empty abstract pathname.
     *
     * @param string $pathname A pathname string.
     * @throws NullPointerException if the $pathname argument is null.
     */
    public function __construct($parent, $child = null)
    {
        if ($parent === null) {
            // For truly null pathnames
            throw new NullPointerException();
        }
        
        $parentPath = '';
        if ($parent instanceof File) {
            $parentPath = $parent->path;
        } elseif (is_string($parent)) {
            $parentPath = $parent;
        } else {
            throw new ClassCastException();
        }
        
        if ($parentPath == '') {
            $this->path = self::$fs->resolve(self::$fs->getDefaultParent(), self::$fs->normalize($child));
        } else {
            if ($child === null) {
                $this->path = self::$fs->normalize($parentPath);
            } else {
                $this->path = self::$fs->resolve(self::$fs->normalize($parentPath), self::$fs->normalize($child));
            }
        }
        $this->prefixLength = self::$fs->prefixLength($this->path);
    }

    /**
     * Converts this abstract pathname into a pathname string. The resulting
     * string uses the default name-separator character to separate the names in
     * the name sequence.
     *
     * @return string The string for of this abstract pathname.
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /* ---------- Attribute accessors ---------- */
    
    /**
     * Tests whether the application can read the file denoted by this abstract
     * pathname.
     *
     * @return boolean
     */
    public function canRead()
    {
        return is_readable($this->path);
    }

    /**
     * Tests whether the application can modify the file denoted by this
     * abstract pathname.
     *
     * @return boolean
     */
    public function canWrite()
    {
        return is_writable($this->path);
    }

    /**
     * Tests whether the file or directory denoted by this abstract pathname
     * exists.
     *
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    /**
     * Tests whether the file denoted by this abstract pathname is a directory.
     *
     * @return boolean
     */
    public function isDirectory()
    {
        return is_dir($this->path);
    }

    /**
     * Tests whether the file denoted by this abstract pathname is a normal
     * file. A file is <em>normal</em> if it is not a directory and, in
     * addition, satisfies other system-dependent criteria. Any non-directory
     * file created by a Java application is guaranteed to be a normal file.
     *
     * @return boolean
     */
    public function isFile()
    {
        return is_file($this->path);
    }

    /**
     * Returns the length of the file denoted by this abstract pathname.
     *
     * @return int The length, in bytes, of the file denoted by this abstract
     *         pathname, or <tt>0</tt> if the file does not exist.
     */
    public function length()
    {
        if (! file_exists($this->getPath())) {
            return 0;
        }
        return filesize($this->getPath());
    }
    
    /* ---------- File operations ---------- */
    
    /**
     * Deletes the file or directory denoted by this abstract pathname. If this
     * pathname denotes a directory, then the directory must be empty in order
     * to be deleted.
     *
     * @return boolean True if and only if the file or directory is successfully
     *         deleted.
     */
    public function delete()
    {
        if ($this->isInvalid()) {
            return false;
        }
        if (! $this->canWrite()) {
            return false;
        }
        return self::$fs->delete($this);
    }

    /**
     * Creates the directory named by this abstract pathname, including any
     * necessary but nonexistent parent directories. Note that if this operation
     * fails it may have succeeded in creating some of the necessary parent
     * directories.
     *
     * @return boolean <code>true</code> if and only if the directory was
     *         created, along with all necessary parent directories;
     *         <code>false</code> otherwise
     */
    public function mkDirs()
    {
        if ($this->exists()) {
            return false;
        }
        if (mkdir($this->getPath())) {
            return true;
        }
        return false;
    }

    /**
     * Renames the file denoted by this abstract pathname. <p> Many aspects of
     * the behavior of this method are inherently platform-dependent: The rename
     * operation might not be able to move a file from one filesystem to
     * another, it might not be atomic, and it might not succeed if a file with
     * the destination abstract pathname already exists. The return value should
     * always be checked to make sure that the rename operation was successful.
     *
     * @param File $dest The new abstract pathname for the named file.
     * @throws NullPointerException if $dest is null.
     * @return boolean True if and only if the renaming succeeded, false
     *         otherwise.
     */
    public function renameTo(File $dest = null)
    {
        if ($dest == null) {
            throw new NullPointerException();
        }
        if ($this->isInvalid() || $dest->isInvalid()) {
            return false;
        }
        return self::$fs->rename($this, $dest);
    }
    
    /* ---------- Basic infrastructure ---------- */
    
    /**
     * Compares two abstract pathnames lexicographically. The ordering defined
     * by this method depends upon the underlying system. On UNIX systems,
     * alphabetic case is significant in comparing pathnames; on Microsoft
     * Windows systems it is not.
     *
     * @param File $pathname
     * @throws NullPointerException if the specified object is null.
     * @throws ClassCastException if the specified object's type prevents it
     *         from being compared to this object.
     * @return int
     * @see \KM\Lang\Comparable::compareTo()
     */
    public function compareTo(Object $pathname = null)
    {
        if ($pathname == null) {
            throw new NullPointerException();
        }
        if (! $pathname instanceof File) {
            throw new ClassCastException();
        }
        return self::$fs->compare($this, $pathname);
    }

    /**
     * Tests this abstract pathname for equality with the given object. Returns
     * <code>true</code> if and only if the argument is not <code>null</code>
     * and is an abstract pathname that denotes the same file or directory as
     * this abstract pathname. Whether or not two abstract pathnames are equal
     * depends upon the underlying system. On UNIX systems, alphabetic case is
     * significant in comparing pathnames; on Microsoft Windows systems it is
     * not.
     *
     * @param Object $obj
     * @return boolean
     * @see \KM\Lang\Object::equals()
     */
    public function equals(Object $obj = null)
    {
        if ($obj != null && $obj instanceof File) {
            return $this->compareTo($obj) == 0;
        }
        return false;
    }

    /**
     * Returns the pathname string of this abstract pathname,
     *
     * @return string
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        return $this->path;
    }
}
?>