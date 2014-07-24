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
 * Writer Class
 *
 * @author Blair
 */
abstract class Writer extends Object implements Closeable, Flushable
{

    /**
     * Temporary buffer use to hold writes of strings and single characters.
     *
     * @var array
     */
    private $writeBuffer;

    /**
     * The size of the write buffer, must be >= 1.
     *
     * @var int
     */
    private static $WRITE_BUFFER_SIZE = 1024;

    /**
     * THe object used to synchronize operations on this stream. For efficiency,
     * a character-stream object may use an object other than itself to protect
     * critical sections. A subclass should therefore use the object in this
     * field rather than $this.
     *
     * @var Object
     */
    protected $lock;

    /**
     * Creates a new character-stream writer whose critical sections will
     * synchronize on the given object or on the writer itself if an object is
     * not specified.
     *
     * @param $lock Object to synchronize on.
     */
    protected function __construct(Object $lock = null)
    {
        if ($lock == null) {
            $lock = $this;
        }
        $this->lock = $lock;
    }

    /**
     * Writes a single character. The character to be written is contained in
     * the 16 low-order bits of the given integer value; the 16 higher-order
     * bits are ignored. Subclasses that intend to support efficient
     * single-character output should override this method.
     *
     * @param string $c
     */
    public function writeChar($c)
    {
        if ($this->writeBuffer == null) {
            $this->writeBuffer = array_fill(0, self::$WRITE_BUFFER_SIZE, null);
        }
        $this->writeBuffer[0] = chr($c);
        $this->write($this->writeBuffer, 0, 1);
    }

    /**
     * Writes a portion of an array of characters (a PHP string).
     *
     * @param array $cbuf An array of characters
     * @param int $off Offset from which to start writing characters, or 0 if
     *            none is specified.
     * @param int $len Number of characters to write, or the length of the given
     *            array if none is specified.
     */
    abstract public function write(array &$cbuf, $off = 0, $len = null);

    /**
     * Writes a portion of a string.
     *
     * @param string $str A string.
     * @param int $off Offset from which to start writing characters.
     * @param int $len Number of characters to write.
     */
    public function writeString($str, $off = 0, $len = null)
    {
        $off = (int) $off;
        if ($len == null) {
            $len = strlen($str);
        }
        $len = (int) $len;
        
        $cbuf = null;
        if ($len <= self::$WRITE_BUFFER_SIZE) {
            if ($this->writeBuffer == null) {
                $this->writeBuffer = array_fill(0, self::$WRITE_BUFFER_SIZE, null);
            }
            $cbuf = $this->writeBuffer;
        } else {
            $cbuf = array_fill(0, $len, null);
        }
        for ($i = 0; $i < $len; $i ++) {
            $cbuf[$i] = chr($str[$off + $i]);
        }
        $this->write($cbuf, 0, $len);
    }

    /**
     * Appends a subsequence of the specified character sequence (string) to
     * this writer.
     *
     * @param string $csq The character sequence (string) from which a
     *            sub-sequence will be appended. If $csq is null, then
     *            characters will be appended as if $csq contained the four
     *            characters "null".
     * @param int $start The index of the first character in the sequence, or 0
     *            if not specified.
     * @param int $end The index of the character following the last character
     *            in the subsequence, or the length of the sequence if none is
     *            specified.
     * @return \KM\IO\Writer
     */
    public function append($csq, $start = 0, $end = null)
    {
        $cs = ($csq == null) ? 'null' : $csq;
        if ($len == null) {
            $len = strlen($cs);
        }
        $this->write(substr($cs, $off, $len));
        return $this;
    }

    /**
     * Flushes the stream. If the stream has saved any characters from the
     * various write() methods in a buffer, write them immediately to their
     * intended destination. Then, if that destination is another character or
     * byte stream, flush it. Thus one flush() invocation will flush all the
     * buffers in a chain of Writers and OutputStreams. <p> If the intended
     * destination of this stream is an abstraction provided by the underlying
     * operating system, for example a file, then flushing the stream guarantees
     * only that bytes previously written to the stream are passed to the
     * operating system for writing; it does not guarantee that they are
     * actually written to a physical device such as a disk drive.
     *
     * @see \KM\IO\Flushable::flush()
     */
    abstract public function flush();

    /**
     * Closes the stream, flushing it first. Once the stream has been closed,
     * further write() or flush() invocations will cause an IOException to be
     * thrown. Closing a previously closed stream has no effect.
     *
     * @see \KM\IO\Closeable::close()
     */
    abstract public function close();
}
?>