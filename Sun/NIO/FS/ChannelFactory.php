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
namespace Sun\NIO\FS;

use KM\IO\IOException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\NIO\File\FileAlreadyExistsException;
use KM\Util\Set;
use Sun\NIO\CH\FileChannelImpl;
use Sun\NIO\FS\ChannelFactory\Flags;

/**
 * Factory to create FileChannels.
 *
 * @author Blair
 */
class ChannelFactory extends Object
{

    /**
     * Private constructor
     */
    private function __construct()
    {}

    /**
     * Open/creates file, returning FileChannel to access the file.
     *
     * @param string $path The path of the file to open/create.
     * @param Set $options The resource configuration
     * @throws IllegalArgumentException if the <tt>read</tt> and <tt>append</tt>
     *         are both set to <tt>true</tt>, or if <tt>append</tt> and
     *         <tt>truncate</tt> are both set to <tt>true</tt>.
     * @throws IOException if an I/O error occurs.
     * @return \Sun\NIO\CH\FileChannelImpl
     */
    public static function newFileChannel($path, Set $options)
    {
        $flags = Flags::toFlags($options);
        
        // default is reading and writing->append
        if (! $flags->read && ! $flags->write) {
            if ($flags->append) {
                $flags->write = true;
                $flags->truncateExisting = false;
            } else {
                $flags->read = true;
            }
        }
        if ($flags->write && ! $flags->create && $flags->createNew) {
            $flags->create = true;
        }
        
        // Validation
        if ($flags->read && $flags->append) {
            throw new IllegalArgumentException('READ and APPEND not allowed');
        }
        if ($flags->append && $flags->truncateExisting) {
            throw new IllegalArgumentException('APPEND and TRUNCATE_EXISTING not allowed');
        }
        
        $fd = self::open($path, $flags);
        return FileChannelImpl::open($fd, $flags->read, $flags->write, $flags->append, null);
    }

    /**
     * Opens file based on parameters and options, returning a resource handle
     * to the open file.
     *
     * @param string $path The path of the file to open/create.
     * @param Flags $flags the instance encapsulating the configuration options
     * @throws IOException if an I/O error occurs.
     * @return resource The handle to the open file.
     */
    private static function open($path, Flags $flags)
    {
        $truncateAfterOpen = false;
        
        $mode = '';
        if ($flags->read && ! $flags->write) {
            $mode = 'r';
        } elseif ($flags->write && ! $flags->read) {
            if ($flags->append) {
                $mode = 'a';
            } elseif ($flags->createNew) {
                $mode = 'x';
            } elseif (! $flags->truncateExisting) {
                $mode = 'c';
            } else {
                $mode = 'w';
            }
        } else {
            if ($flags->append) {
                $mode = 'a+';
            } elseif ($flags->createNew) {
                $mode = 'x+';
            } elseif ($flags->truncateExisting) {
                $mode = 'w+';
            } elseif ($flags->create) {
                $mode = 'c+';
            } else {
                $mode = 'r+';
            }
        }
        if ($mode === '') {
            $mode = 'w+';
        }
        
        // Add the binary designation to the access mode.
        $mode .= 'b';
        
        $handle = @fopen($path, $mode);
        if ($handle === false) {
            if ($mode == 'x' || $mode == 'x+') {
                throw new FileAlreadyExistsException($path);
            }
            throw new IOException();
        }
        return $handle;
    }
}
?>