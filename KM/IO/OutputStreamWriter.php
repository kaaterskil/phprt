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

use KM\IO\Writer;
use Sun\NIO\CS\StreamEncoder;

/**
 * An OutputStreamWriter is a bridge from character streams to byte streams:
 * Characters written to it are encoded into bytes using a specified
 * php.nio.charset.Charset charset. The charset that it uses may be specified by
 * name or may be given explicitly, or the platform's default charset may be
 * accepted. <p> Each invocation of a write() method causes the encoding
 * converter to be invoked on the given character(s). The resulting bytes are
 * accumulated in a buffer before being written to the underlying output stream.
 * The size of this buffer may be specified, but by default it is large enough
 * for most purposes. Note that the characters passed to the write() methods are
 * not buffered. <p> For top efficiency, consider wrapping an OutputStreamWriter
 * within a BufferedWriter so as to avoid frequent converter invocations. For
 * example: <pre> Writer out = new BufferedWriter(new
 * OutputStreamWriter(System.out)); </pre> <p> A <i>surrogate pair</i> is a
 * character represented by a sequence of two <tt>char</tt> values: A
 * <i>high</i> surrogate in the range '&#92;uD800' to '&#92;uDBFF' followed by a
 * <i>low</i> surrogate in the range '&#92;uDC00' to '&#92;uDFFF'. <p> A
 * <i>malformed surrogate element</i> is a high surrogate that is not followed
 * by a low surrogate or a low surrogate that is not preceded by a high
 * surrogate. <p> This class always replaces malformed surrogate elements and
 * unmappable character sequences with the charset's default <i>substitution
 * sequence</i>. The php.nio.charset.CharsetEncoder class should be used when
 * more control over the encoding process is required.
 *
 * @author Blair
 */
class OutputStreamWriter extends Writer
{

    /**
     * The stream encoder.
     *
     * @var \Sun\NIO\CS\StreamEncoder
     */
    private $se;

    /**
     * Creates an output stream writer that uses the named character encoding.
     *
     * @param OutputStream $out
     */
    public function __construct(OutputStream $out, $charsetName = '')
    {
        parent::__construct($out);
        try {
            $this->se = StreamEncoder::forOutputStreamWriter($out, $this, $charsetName);
        } catch (UnsupportedEncodingException $e) {
            throw $e;
        }
    }

    /**
     * Returns the name of the character encoding being used by this stream.
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->se->getEncoding();
    }

    /**
     * Flushes the output buffer to the underlying byte stream, without flushing
     * the byte stream itself. This method is non-private only so that it may be
     * invoked by PrintStream.
     */
    public function flushBuffer()
    {
        $this->se->flushBuffer();
    }

    /**
     * Writes a single character.
     *
     * @param string $c
     * @see \KM\IO\Writer::writeChar()
     */
    public function writeChar($c)
    {
        $this->se->writeChar($c);
    }

    /**
     * Writes a portion of an array of characters.
     *
     * @param array $cbuf Buffer of characters.
     * @param int $off Offset from which to start writing characters.
     * @param int $len Number of characters to write.
     * @see \KM\IO\Writer::write()
     */
    public function write(array &$cbuf, $off = 0, $len = null)
    {
        $this->se->write($cbuf, $off, $len);
    }

    /**
     * Writes a portion of a string.
     *
     * @param string $str A string.
     * @param int $off Offset from which to start writing characters.
     * @param int $len Number of characters to write.
     * @see \KM\IO\Writer::writeString()
     */
    public function writeString($str, $off = 0, $len = null)
    {
        $this->se->writeString($str, $off, $len);
    }

    /**
     * Flushes the stream.
     *
     * @see \KM\IO\Writer::flush()
     */
    public function flush()
    {
        $this->se->flush();
    }

    public function close()
    {
        $this->se->close();
    }
}
?>