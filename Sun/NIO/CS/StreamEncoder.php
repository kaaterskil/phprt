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
namespace Sun\NIO\CS;

use KM\IO\Writer;
use KM\IO\IOException;
use KM\IO\OutputStream;
use KM\IO\UnsupportedEncodingException;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\Object;
use KM\NIO\ByteBuffer;
use KM\NIO\CharBuffer;
use KM\NIO\Charset\CharsetEncoder;
use KM\NIO\Charset\CoderResult;
use KM\NIO\Charset\IllegalCharsetNameException;

/**
 * StreamEncoder Class
 *
 * @author Blair
 */
class StreamEncoder extends Writer
{

    private static $DEFAULT_BYTE_BUFFER_SIZE = 8192;

    private $isOpen = true;

    private function ensureOpen()
    {
        if (! $this->isOpen) {
            throw new IOException('Stream closed');
        }
    }

    /**
     * Factory for OutputStreamWriter
     *
     * @param OutputStream $out
     * @param Object $lock
     * @param string $newCharset
     * @throws IllegalCharsetNameException
     * @throws UnsupportedEncodingException
     * @return \Sun\NIO\CS\StreamEncoder
     */
    public static function forOutputStreamWriter(OutputStream $out, Object $lock, $newCharset = '')
    {
        $csn = (string) $newCharset;
        if (empty($csn)) {
            $csn = iconv_get_encoding('output_encoding');
        }
        try {
            if (in_array($csn, mb_list_encodings())) {
                return new self($out, $lock, $csn);
            }
        } catch (IllegalCharsetNameException $e) {
            // Drop through.
        }
        throw new UnsupportedEncodingException($csn);
    }
    
    /*
     * ---------- Public methods corresponding to OutputStreamWriter ----------
     */
    
    // All synchronization and state/argument checking is done in these public
    // methods; the concrete
    // stream encoder subclass defined below need not do any such checking.
    public function getEncoding()
    {
        if ($this->isOpen()) {
            return $this->encodingName();
        }
        return null;
    }

    public function flushBuffer()
    {
        if ($this->isOpen()) {
            $this->implFlushBuffer();
        } else {
            throw new IOException('Stream closed');
        }
    }

    public function writeChar($c)
    {
        $cbuf = array();
        $cbuf[0] = $c;
        $this->write($cbuf, 0, 1);
    }

    public function write(array &$cbuf, $off = 0, $len = null)
    {
        $off = (int) $off;
        if ($len == null) {
            $len = count($cbuf);
        }
        $len = (int) $len;
        
        $this->ensureOpen();
        if (($off < 0) || ($off > count($cbuf)) || ($len < 0) || (($off + $len) > count($cbuf)) || (($off + $len) < 0)) {
            throw new IndexOutOfBoundsException();
        } elseif ($len == 0) {
            return;
        }
        $this->implWrite($cbuf, $off, $len);
    }

    public function writeString($str, $off = 0, $len = null)
    {
        $off = (int) $off;
        if ($len == null) {
            $len = strlen($str);
        }
        $len = (int) $len;
        
        if ($len < 0) {
            throw new IndexOutOfBoundsException();
        }
        $cbuf = array();
        for ($i = 0; $i < $len; $i ++) {
            $cbuf[$i] = $str[$off + $i];
        }
        $this->write($cbuf, 0, $len);
    }

    public function flush()
    {
        $this->ensureOpen();
        $this->implFlush();
    }

    public function close()
    {
        if (! $this->isOpen) {
            return;
        }
        $this->implClose();
        $this->isOpen = false;
    }

    private function isOpen()
    {
        return $this->isOpen;
    }
    
    /* ---------- Charset based stream encoder implementation ---------- */
    
    /**
     * The charset
     *
     * @var string
     */
    private $cs;

    /**
     * The charset encoder
     *
     * @var CharsetEncoder
     */
    private $encoder;

    /**
     * The byte buffer
     *
     * @var ByteBuffer
     */
    private $bb;

    /**
     * The output stream.
     *
     * @var OutputStream
     */
    private $out;

    /**
     * The leftover char buffer
     *
     * @var CharBuffer
     */
    private $lcb;

    /**
     * Tells if there is a leftover char.
     *
     * @var boolean
     */
    private $haveLeftoverChar = false;

    /**
     * The leftover char, if any
     *
     * @var string
     */
    private $leftoverChar;

    /**
     * Private constructor
     *
     * @param OutputStream $out
     * @param Object $lock
     * @param string $charset
     */
    protected function __construct(OutputStream $out, Object $lock, $charset)
    {
        parent::__construct($lock);
        $this->out = $out;
        $this->cs = (string) $charset;
        $this->encoder = new CharsetEncoder($charset);
        $this->bb = ByteBuffer::allocate(self::$DEFAULT_BYTE_BUFFER_SIZE);
    }

    private function writeBytes()
    {
        $this->bb->flip();
        $lim = $this->bb->getLimit();
        $pos = $this->bb->getPosition();
        assert($pos <= $lim);
        $rem = ($pos <= $lim ? $lim - $pos : 0);
        
        if ($rem > 0) {
            $this->out->write($this->bb->toArray(), $this->bb->arrayOffset() + $pos, $rem);
        }
        $this->bb->clear();
    }

    private function flushLeftoverChar(CharBuffer $cb = null, $endOfInput)
    {
        /* @var $cr CoderResult */
        if (! $this->haveLeftoverChar && ! $endOfInput) {
            return;
        }
        if ($this->lcb == null) {
            $this->lcb = CharBuffer::allocate(2);
        } else {
            $this->lcb->clear();
        }
        if ($this->haveLeftoverChar) {
            $this->lcb->putChar($this->leftoverChar);
        }
        if ($cb != null && $cb->hasRemaining()) {
            $this->lcb->putChar($cb->getChar());
        }
        $this->lcb->flip();
        while ($this->lcb->hasRemaining() || $endOfInput) {
            $cr = $this->encoder->encode($this->lcb, $this->bb, $endOfInput);
            if ($cr->isUnderflow()) {
                if ($this->lcb->hasRemaining()) {
                    $this->leftoverChar = $this->lcb->getChar();
                    if ($cb != null && $cb->hasRemaining()) {
                        $this->flushLeftoverChar($cb, $endOfInput);
                    }
                    return;
                }
                break;
            }
            if ($cr->isOverflow()) {
                $this->writeBytes();
                continue;
            }
            $cr->throwException();
        }
        $this->haveLeftoverChar = false;
    }

    public function implWrite(array &$cbuf, $off = 0, $len = null)
    {
        /* @var $cr CoderResult */
        $cb = CharBuffer::wrap($cbuf, $off, $len);
        
        if ($this->haveLeftoverChar) {
            $this->flushLeftoverChar($cb, false);
        }
        
        while ($cb->hasRemaining()) {
            $cr = $this->encoder->encode($cb, $this->bb, false);
            if ($cr->isUnderflow()) {
                if ($cb->remaining() == 1) {
                    $this->haveLeftoverChar = true;
                    $this->leftoverChar = $cb->getChar();
                }
                break;
            }
            if ($cr->isOverflow()) {
                $this->writeBytes();
                continue;
            }
            $cr->throwException();
        }
    }

    public function implFlushBuffer()
    {
        if ($this->bb->getPosition() > 0) {
            $this->writeBytes();
        }
    }

    public function implFlush()
    {
        $this->implFlushBuffer();
        if ($this->out != null) {
            $this->out->flush();
        }
    }

    public function implClose()
    {
        $this->flushLeftoverChar(null, true);
        try {
            for (;;) {
                $cr = $this->encoder->flush($this->bb);
                if ($cr->isUnderflow()) {
                    break;
                }
                if ($cr->isOverflow()) {
                    $this->writeBytes();
                    continue;
                }
                $cr->throwException();
            }
            if ($this->bb->getPosition() > 0) {
                $this->writeBytes();
            }
            $this->out->close();
        } catch (IOException $e) {
            $this->encoder->reset();
            throw $e;
        }
    }

    public function encodingName()
    {
        return $this->cs;
    }
}
?>