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
use KM\Lang\NullPointerException;
use KM\Lang\IndexOutOfBoundsException;

/**
 * This abstract class id the superclass of all classes representing an output
 * stream of bytes, An output stream accepts output bytes and sends them to some
 * sink. Applications that need to define a subclass of OutputStream must always
 * provide at least a method that writes one byte of output.
 *
 * @author Blair
 */
abstract class OutputStream extends Object implements Closeable, Flushable
{

    /**
     * Writes the specified byte to the output stream. The general contract for
     * writeByte() is that one byte is written to the output stream, The byte to
     * be written is the eight low-order bits of the argument $b. The 24
     * high-order bits of $b are ignored. Subclasses of OutpuStream must provide
     * an implementation for this method.
     *
     * @param int $b The byte.
     */
    public abstract function writeByte($b);

    /**
     * Writes $len bytes from the specified byte array starting at offset $off
     * to this output stream. The general contract for write() is that some of
     * the bytes in the array $b are written to the output stream in order;
     * element $b[$off] is the first byte written and $b[$off + $len] id the
     * last byte written by this operation. The write() method of OutputStream
     * calls the writeByte() method on each of the bytes to be written out.
     * Subclasses are encouraged to override this method and provide a more
     * efficient implementation.
     *
     * @param array $b The data.
     * @param int $off The start offset in the data.
     * @param int $len The number of bytes to write.
     * @throws NullPointerException
     * @throws IndexOutOfBoundsException
     */
    public function write(array &$b, $off = 0, $len = null)
    {
        $b = (string) $b;
        $off = (int) $off;
        if ($len == null) {
            $len = count($b);
        }
        
        if ($b == null) {
            throw new NullPointerException();
        } elseif (($off < 0) || ($off > count($b)) || ($len < 0) || ($off + $len > count($b)) || ($off + $len < 0)) {
            throw new IndexOutOfBoundsException();
        } elseif ($len == 0) {
            return;
        }
        for ($i = 0; $o < $len; $i ++) {
            $this->writeByte($b[$i]);
        }
    }

    /**
     * Flushes this output stream and forces any buffered output bytes to be
     * written out. The general contract of flush() is that calling it is an
     * indication that, if any bytes previously written have been buffered by
     * the implementation of the output stream, such bytes should immediately be
     * written to their intended destination. If the intended destination of
     * this stream is an abstraction provided by the underlying operating
     * system, for example a file, then flushing the stream guarantees only that
     * bytes previously written to the stream are passed to the operating system
     * for writing; it dies not guarantee that they are actually written to a
     * physical device such as a disk drive. The flush() method of OutputStream
     * does nothing,
     *
     * @see \KM\IO\Flushable::flush()
     */
    public function flush()
    {
        // Noop
    }

    /**
     * Closes this output stream and releases any system resources associated
     * with this stream. The general contract of close() is that it closes the
     * output stream. A closed stream cannot perform output operations and
     * cannot be reopened. The close() method of OutputStream does nothing.
     *
     * @see \KM\IO\Closeable::close()
     */
    public function close()
    {
        // Noop
    }
}
?>