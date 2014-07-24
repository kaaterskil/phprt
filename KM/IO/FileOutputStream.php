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

use KM\Lang\NullPointerException;
use KM\Util\Logging\LogManager\Beans;

/**
 * A file output stream is an output stream for writing data to a file. Whether
 * or not a file is available or may be created depends upon the underlying
 * platform. Some platforms, in particular, allow a file to be opened for
 * writing by only on e FileOutputStream (or other file-writing object) as a
 * time. In such situations, the constructor in this class will fail if the file
 * involved is already open. FileOutputStream is meant for writing streams of
 * raw bytes such as image data. For writing streams of characters, consider
 * using FileWriter.
 *
 * @author Blair
 */
class FileOutputStream extends OutputStream
{

    /**
     * The system dependent resource.
     *
     * @var resource
     */
    private $fd = null;

    /**
     * True if the file is opened for append.
     *
     * @var boolean
     */
    private $append = false;

    /**
     * True if the file is closed.
     *
     * @var boolean
     */
    private $closed = false;

    /**
     * Creates a file output stream to write to the file represented by the
     * specified $fname. iF the second argument is true, the bytes will be
     * written to the end of the file rather than the beginning, A new resource
     * is created to represent this file connection.
     *
     * @param string $fname The system-dependent file name.
     * @param boolean $append If true, then bytes will be written to the end of
     *            the file rather than the beginning.
     * @throws NullPointerException
     * @throws FileNotFoundException If the file exists but is a directory
     *         rather than a regular file, does not exist but cannot be created,
     *         or cannot be opened for any other reason.
     */
    public function __construct($filename, $append = false)
    {
        if ($filename == null) {
            throw new NullPointerException();
        }
        $filename = (string) $filename;
        if (is_dir($filename) || (is_file($filename) && ! is_writable($filename))) {
            throw new FileNotFoundException('Invalid file path');
        }
        $this->append = (boolean) $append;
        $this->open($filename, $append);
    }

    /**
     * Cleans up the connection to the file and ensures that the close() methos
     * of this file output stream is called when there are no more references to
     * this stream.
     */
    public function __destruct()
    {
        if ($this->fd != null) {
            $this->flush();
            $this->close();
        }
    }

    /**
     * Opens a file with the specified name for overwriting or appending.
     *
     * @param string $fname The name of the file to be opened.
     * @param boolean $append Whether the file is to be opened in append mode.
     * @throws IOException
     */
    private function open($fname, $append)
    {
        if ($append === true) {
            $this->fd = @fopen($fname, 'ab');
        } else {
            $this->fd = @fopen($fname, 'wb');
        }
        if ($this->fd === false) {
            throw new IOException();
        }
    }

    /**
     * Writes the specified byte to this file output stream.
     *
     * @param int $b The byte to be written.
     * @see \KM\IO\OutputStream::writeByte()
     */
    public function writeByte($b)
    {
        $b = ! is_array($b) ? (array) $b : $b;
        $this->writeBytes($b, 0, 1);
    }

    /**
     * Writes a sub-array as a sequence of bytes.
     *
     * @param array $b The data to be written.
     * @param int $off The start offset in the data.
     * @param int $len The number of bytes to be written.
     * @throws IOException If an I/O error has occurred.
     */
    private function writeBytes(array $b, $off, $len)
    {
        if ($this->fd != null) {
            $bytearr = ($len == 0) ? [] : array_fill(0, $len, null);
            for ($i = 0; $i < $len; $i ++) {
                $bytearr[$i] .= $b[$off + $i];
            }
            $data = implode('', $bytearr);
            
            $fwrite = @fwrite($this->fd, $data);
            if ($fwrite === false) {
                throw new IOException();
            }
            return;
        }
        throw new IOException();
    }

    /**
     * Writes $len bytes from the specified byte array starting at offset $off
     * tp this file output stream.
     *
     * @param array $b The data.
     * @param int $off The start offset in the data.
     * @param int $len The number of bytes to write.
     * @see \KM\IO\OutputStream::write()
     */
    public function write(array &$b, $off = 0, $len = null)
    {
        $off = (int) $off;
        if ($len == null) {
            $len = count($b);
        }
        $len = (int) $len;
        $this->writeBytes($b, $off, $len);
    }

    /**
     * Closes this file output stream and releases any system resources
     * associated with this stream. The file output stream may no longer by used
     * for writing bytes.
     *
     * @see \KM\IO\OutputStream::close()
     */
    public function close()
    {
        if ($this->closed) {
            return;
        }
        $this->closed = true;
        $this->close0();
    }

    /**
     * Returns the resource associated with this stream.
     *
     * @throws IOException
     * @return resource
     */
    public function getFD()
    {
        if ($this->fd != null) {
            return $this->fd;
        }
        throw new IOException();
    }

    private function close0()
    {
        if ($this->fd != null) {
            $fclose = @fclose($this->fd);
            if ($fclose === false) {
                throw new IOException();
            }
            return;
        }
        throw new IOException();
    }
}
?>