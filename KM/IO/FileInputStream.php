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
use KM\Lang\IndexOutOfBoundsException;

/**
 * A <code>FileInputStream</code> obtains input bytes from a file in a file
 * system. What files are available depends on the host environment.
 * <p><code>FileInputStream</code> is meant for reading streams of raw bytes
 * such as image data. For reading streams of characters, consider using
 * <code>FileReader</code>.
 *
 * @author Blair
 */
class FileInputStream extends InputStream
{

    /**
     * The system dependent resource.
     *
     * @var resource
     */
    private $fd = null;

    /**
     * True if the file is closed.
     *
     * @var boolean
     */
    private $closed = false;

    /**
     * Creates a <code>FileInputStream</code> by opening a connection to an
     * actual file, the file named by the path name <code>name</code> in the
     * file system. A new <code>FileDescriptor</code> object is created to
     * represent this file connection. <p> If the named file does not exist, is
     * a directory rather than a regular file, or for some other reason cannot
     * be opened for reading then a <code>FileNotFoundException</code> is
     * thrown.
     *
     * @param string $filename The system-dependent file name.
     * @throws NullPointerException if the file name is null.
     * @throws FileNotFoundException if the file does not exist, is a directory
     *         rather than a regular file, or for some other reason cannot be
     *         opened for reading.
     */
    public function __construct($filename)
    {
        if ($filename == null) {
            throw new NullPointerException();
        }
        $file = new File($filename);
        if ($file->isInvalid()) {
            throw new FileNotFoundException('invalid file path');
        }
        $this->open($filename);
    }

    /**
     * Opens the specified file for reading.
     *
     * @throws FileNotFoundException if an I/O error has occurred.
     */
    private function open($fname)
    {
        $this->fd = @fopen($fname, 'rb');
        if ($this->fd === false) {
            throw new FileNotFoundException('Error opening file');
        }
    }

    /**
     * Reads a byte of data from this input stream. This method blocks if no
     * input is yet available.
     *
     * @return int The next byte of data, or <code>-1</code> if the end of the
     *         file is reached,
     * @throws IOException is an I/O error occurs.
     * @see \KM\IO\InputStream::readByte()
     */
    public function readByte()
    {
        if ($this->fd != null) {
            $c = @fread($this->fd, 1);
            if ($c === false) {
                return -1;
            }
            return $c;
        }
        throw new IOException();
    }

    /**
     * Reads a sub-array as a sequence of bytes.
     *
     * @param array $b The buffer into which the data is read.
     * @param int $off The start offset.
     * @param int $len The maximum number of bytes read.
     * @throws IOException is an I/O error occurs.
     * @return int The actual number of bytes read.
     */
    private function readBytes(array &$b, $off = 0, $len = null)
    {
        if ($this->fd != null) {
            $fread = @fread($this->fd, $len);
            if ($fread === false) {
                throw new IOException();
            }
            $size = strlen($fread);
            for ($i = 0; $i < $size; $i++) {
                $b[$off + $i] = $fread[$i];
            }
            return $size;
        }
        throw new IOException();
    }

    /**
     * Reads up to <code>len</code> bytes of data from this input stream into an
     * array of bytes. If <code>len</code> is not zero, the method blocks until
     * some input is available; otherwise, no bytes are read and <code>0</code>
     * is returned.
     *
     * @param array $b The buffer into which the data is read.
     * @param int $off The start offset in the destination array <code>b</code>.
     * @param int $len The maximum number of bytes read.
     * @throws IndexOutOfBoundsException if <code>off</code> is negative,
     *         <code>len</code> is negative, or <code>len</code> is greater than
     *         <code>b.length - off</code>.
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::read()
     */
    public function read(array &$b, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($b);
        }
        if ($off < 0 || $len < 0 || $len > (count($b) - $off)) {
            throw new IndexOutOfBoundsException();
        }
        return $this->readBytes($b, $off, $len);
    }

    /**
     * Skips over and discards <code>n</code> bytes of data from the input
     * stream. <p>The <code>skip</code> method may, for a variety of reasons,
     * end up skipping over some smaller number of bytes, possibly
     * <code>0</code>. If <code>n</code> is negative, the method will try to
     * skip backwards. In case the backing file does not support backward skip
     * at its current position, an <code>IOException</code> is thrown. The
     * actual number of bytes skipped is returned. If it skips forwards, it
     * returns a positive value. If it skips backwards, it returns a negative
     * value. <p>This method may skip more bytes than what are remaining in the
     * backing file. This produces no exception and the number of bytes skipped
     * may include some number of bytes that were beyond the EOF of the backing
     * file. Attempting to read from the stream after skipping past the end will
     * result in -1 indicating the end of the file.
     *
     * @param int $n The number of bytes to be skipped.
     * @throws IOException if <code>n</code. is negative, if the stream does not
     *         support seek, or if an I/O error occurs.
     * @see \KM\IO\InputStream::skip()
     */
    public function skip($n)
    {
        if ($this->fd != null) {
            $n = intval($n);
            if ($n < 0) {
                throw new IOException();
            }
            $result = @fseek($this->fd, intval($n), SEEK_CUR);
            if ($result < 0) {
                throw new IOException();
            }
        }
        throw new IOException();
    }

    /**
     * Returns an estimate of the number of remaining bytes that can be read (or
     * skipped over) from this input stream without blocking by the next
     * invocation of a method for this input stream. Returns 0 when the file
     * position is beyond EOF. The next invocation might be the same thread or
     * another thread. A single read or skip of this many bytes will not block,
     * but may read or skip fewer bytes. <p> In some cases, a non-blocking read
     * (or skip) may appear to be blocked when it is merely slow, for example
     * when reading large files over slow networks.
     *
     * @throws IOException if an I/O error occurs.
     * @return int an estimate of the number of remaining bytes that can be read
     *         (or skipped over) from this input stream without blocking.
     * @see \KM\IO\InputStream::available()
     */
    public function available()
    {
        if ($this->fd != null) {
            $cur = @ftell($this->fd);
            if ($cur === false) {
                throw new IOException();
            }
            $stats = @fstat($this->fd);
            $rem = $stats['size'] - $cur;
            return ($rem < 0) ? 0 : $rem;
        }
        throw new IOException();
    }

    /**
     * Closes this file input stream and releases any system resources
     * associated with the stream.
     *
     * @throws IOException if an I/O error occurs.
     * @see \KM\IO\InputStream::close()
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
     * @throws IOException if an I/O error occurs.
     * @return resource The resource associated with this stream.
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

    /**
     * Ensures that the <code>close</code> method of this file input stream is
     * called when there are no more references to it.
     */
    public function __destruct()
    {
        if ($this->fd != null) {
            $this->close();
        }
    }
}
?>