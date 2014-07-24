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
namespace Sun\NIO\CH;

use KM\IO\IOException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\NIO\ByteBuffer;

/**
 * FileDispatcherImpl Class
 *
 * @author Blair
 */
class FileDispatcherImpl extends FileDispatcher
{

    public function __construct()
    {}

    public function read($fd, $off, $len)
    {
        return self::read0($fd, $off, $len);
    }

    public function pread($fd, $off, $len, $position, Object $lock = null)
    {
        return self::pread0($fd, $off, $len, $position);
    }

    public function write($fd, ByteBuffer $buf, $off, $len)
    {
        return self::write0($fd, $buf, $off, $len);
    }

    public function pwrite($fd, ByteBuffer $buf, $off, $len, $position, Object $lock)
    {
        return self::pwrite0($fd, $buf, $off, $len, $position);
    }

    public function truncate($fd, $size)
    {
        return self::truncate0($fd, $size);
    }

    public function size($fd)
    {
        return self::size0($fd);
    }

    public function lock($fd, $blocking)
    {
        return self::lock0($fd, $blocking);
    }

    public function release($fd)
    {
        self::release0($fd);
    }

    public function close($fd)
    {
        self::close0($fd);
    }

    public function preClose($fd)
    {
        self::preClose0($fd);
    }
    
    /* ---------- "Native" Methods ---------- */
    
    /**
     * Binary-safe file read
     *
     * @param resource $fd A filesystem pointer resource.
     * @param int $offset the start offset in the file
     * @param int $length The number of bytes to read.
     * @throws IOException if an I/O error occurs.
     * @return string The read string
     */
    public static function read0($fd, $offset, $length)
    {
        if ($fd != null) {
            if ($offset > 0) {
                self::seek0($fd, $offset);
            }
            $data = @fread($fd, $length);
            if ($data === false) {
                throw new IOException();
            }
            return $data;
        }
        throw new IOException();
    }

    public static function pread0($fd, $off, $len, $position)
    {
        // TODO Needs implementation
        throw new UnsupportedOperationException();
    }

    /**
     * Binary-safe file write
     *
     * @param resource $fd the file system pointer resource.
     * @param string $data the data to write to the stream as a string (PHP byte
     *            array)
     * @param int $offset the start offset in the file
     * @param int $length The maximum number of bytes to write.
     * @throws IOException if an I/O error occurs.
     * @return int The number of bytes actually written.
     */
    public static function write0($fd, ByteBuffer $buf, $offset, $length)
    {
        if ($fd != null) {
            if ($offset > 0) {
                self::seek0($fd, $offset);
            }
            $bytes = implode('', $buf->toArray());
            $result = @fwrite($fd, $bytes, $length);
            if ($result === false) {
                throw new IOException();
            }
            return $result;
        }
        throw new IOException();
    }

    public static function pwrite0($fd, ByteBuffer $buf, $off, $len, $position)
    {
        // TODO Needs implementation
        throw new UnsupportedOperationException();
    }

    /**
     * Sets the file position indicator for the file references by <tt>fd</tt>.
     * The new position, measured in bytes from the beginning of the file if
     * obtained by <tt>offset</tt>.
     *
     * @param unknown $fd
     * @param unknown $position
     */
    public static function seek0($fd, $offset)
    {
        if ($fd !== null) {
            if ($offset > 0) {
                $size = self::size0($fd);
                if ($offset > $size) {
                    throw new IOException('offset greater than filesize');
                }
                $result = @fseek($fd, $offset, SEEK_SET);
                if ($result < 0) {
                    throw new IOException();
                }
            }
            return;
        }
        throw new IOException();
    }

    /**
     * Truncates a file to a given length
     *
     * @param resource $fd The file pointer.
     * @param int $size The size to truncate to.
     * @throws IOException if an I/O error occurs.
     * @return boolean <tt>true</tt> on success, <tt>false</tt> otherwise.
     */
    public static function truncate0($fd, $size)
    {
        if ($fd != null) {
            $size = (int) $size;
            $result = @ftruncate($fd, $size);
            return $result;
        }
        throw new IOException();
    }

    /**
     * Returns the file size of the file opened by the file pointer handle.
     *
     * @param resource $fd The file pointer handle.
     * @throws IOException if an I/O error occurs.
     * @return int the size of the file opened by the file pointer handle.
     */
    public static function size0($fd)
    {
        if ($fd != null) {
            $stat = @fstat($fd);
            if (! is_array($stat)) {
                throw new IOException();
            }
            return $stat['size'];
        }
        throw new IOException();
    }

    public static function lock0($fd, $blocking)
    {
        if ($fd != null) {
            $operation = LOCK_SH;
            if ($blocking) {
                $operation = LOCK_EX;
            }
            $result = @flock($fd, $operation);
            if ($result === false) {
                throw new IOException();
            }
            return;
        }
        throw new IOException();
    }

    public static function release0($fd)
    {
        if ($fd != null) {
            $result = @flock($fd, LOCK_UN);
            if ($result === false) {
                throw new IOException();
            }
            return;
        }
        throw new IOException();
    }

    public static function close0($fd)
    {
        if ($fd !== null) {
            $result = @fclose($fd);
            if ($result === false) {
                throw new IOException();
            }
            return;
        }
        throw new IOException();
    }

    public static function preClose0($fd)
    {
        // Noop
    }
}
?>