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
namespace KM\IO\ObjectOutputStream;

use KM\IO\Bits;
use KM\IO\DataOutput;
use KM\IO\DataOutputStream;
use KM\IO\ObjectOutputStream;
use KM\IO\ObjectStreamConstants;
use KM\IO\OutputStream;
use KM\IO\UTFDataFormatException;
use KM\Lang\System;

/**
 * Buffered output stream with two modes: in default mode, outputs data in same
 * format as DataOutputStream; in "block data" mode, outputs data bracketed by
 * block data markers (see object serialization specification for details).
 *
 * @author Blair
 */
class BlockDataOutputStream extends OutputStream implements DataOutput,
    ObjectStreamConstants
{

    /**
     * Maximum data block length
     *
     * @var int
     */
    private static $MAX_BLOCK_SIZE = 1024;

    /**
     * Maximum data block header length
     *
     * @var int
     */
    private static $MAX_HEADER_SIZE = 5;

    /**
     * Length of the char buffer for writing strings.
     *
     * @var int
     */
    private static $CHAR_BUF_SIZE = 256;

    /**
     * Buffer for writing general block data.
     *
     * @var array
     */
    private $buf;

    /**
     * Buffer for writing block data headers.
     *
     * @var array
     */
    private $hbuf;

    /**
     * Char buffer for fast string writes.
     *
     * @var array
     */
    private $cbuf;

    /**
     * Block data mode.
     *
     * @var boolean
     */
    private $blkmode = false;

    /**
     * Current offset into the buffer.
     *
     * @var int
     */
    private $pos = 0;

    /**
     * Underlying output stream.
     *
     * @var OutputStream;
     */
    private $out;

    /**
     * Loop back stream for data writes that span data blocks.
     *
     * @var DataOutputStream
     */
    private $dout;

    /**
     * Creates a new BlockDataOutputStream on top of the given underlying
     * stream.
     * Block data mode is turned off by default.
     *
     * @param OutputStream $out
     * @param ObjectOutputStream $dout
     */
    public function __construct(OutputStream $out, ObjectOutputStream $dout)
    {
        $this->buf = array_fill(0, self::$MAX_BLOCK_SIZE, null);
        $this->hbuf = array_fill(0, self::$MAX_HEADER_SIZE, null);
        $this->cbuf = array_fill(0, self::$CHAR_BUF_SIZE, null);
        
        $this->out = $out;
        $this->dout = new DataOutputStream($dout);
    }

    /**
     * Sets block data mode to the given mode (true == on, false == off) and
     * returns the previous mode value.
     * If the new mode is the same as the old
     * mode, no action is taken. If the new mode differs from the old mode, any
     * buffered data is flushed before switching to the new mode.
     *
     * @param boolean $mode
     * @return boolean
     */
    public function setBlockDataMode($mode)
    {
        $mode = (boolean) $mode;
        if ($this->blkmode == $mode) {
            return $this->blkmode;
        }
        $this->drain();
        $this->blkmode = $mode;
        return !$this->blkmode;
    }

    /**
     * Returns true if the stream is currently in block data mode, false
     * otherwise.
     *
     * @return boolean
     */
    public function getBlockDataMode()
    {
        return $this->blkmode;
    }
    
    /* ---------- Generic output stream methods ---------- */
	
	/*
	 * The following methods are equivalent to their counterparts in
	 * OutputStream, except that they partition written data into data
	 * blocks when in block data mode.
	 */
	
	public function writeByte($b)
    {
        if ($this->pos >= self::$MAX_BLOCK_SIZE) {
            $this->drain();
        }
        $this->buf[$this->pos++] = $b;
    }

    public function write(array &$b, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($b);
        }
        $len = (int) $len;
        $this->writeCopy($b, $off, $len, false);
    }

    public function flush()
    {
        $this->drain();
        $this->out->flush();
    }

    public function close()
    {
        $this->flush();
        $this->out->close();
    }

    /**
     * Writes specified span of byte values from given array.
     * If copy is true, copies the values to an intermediate buffer before
     * writing them to underlying stream (to avoid exposing a reference to the
     * original byte array).
     *
     * @param array $b
     * @param int $off
     * @param int $len
     * @param boolean $copy
     */
    public function writeCopy(array &$b, $off, $len, $copy)
    {
        if (!($copy || $this->blkmode)) {
            $this->drain();
            $this->out->write($b, $off, $len);
            return;
        }
        while ($len > 0) {
            if ($this->pos >= self::$MAX_BLOCK_SIZE) {
                $this->drain();
            }
            if ($len >= self::$MAX_BLOCK_SIZE && !$copy && $this->pos == 0) {
                // Avoid unnecessary copy.
                $this->writeBlockHeader(self::$MAX_BLOCK_SIZE);
                $this->out->write($b, $off, self::$MAX_BLOCK_SIZE);
                $off += self::$MAX_BLOCK_SIZE;
                $len -= self::$MAX_BLOCK_SIZE;
            } else {
                $wlen = min(
                    array(
                        $len,
                        self::$MAX_BLOCK_SIZE - $this->pos
                    ));
                System::arraycopy($b, $off, $this->buf, $this->pos, $wlen);
                $this->pos += $wlen;
                $off += $wlen;
                $len -= $wlen;
            }
        }
    }

    /**
     * Writes all buffered data from this stream to the underlying stream, but
     * does not flush underlying stream.
     */
    public function drain()
    {
        if ($this->pos == 0) {
            return;
        }
        if ($this->blkmode) {
            $this->writeBlockHeader($this->pos);
        }
        $this->out->write($this->buf, 0, $this->pos);
        $this->pos = 0;
    }

    /**
     * Writes block data header.
     * Data blocks shorter than 256 bytes are prefixed
     * with a 2-byte header; all others start with a 5-byte header.
     *
     * @param int $len
     */
    private function writeBlockHeader($len)
    {
        $len - (int) $len;
        if ($len <= 0xff) {
            $this->hbuf[0] = chr(self::TC_BLOCKDATA);
            $this->hbuf[1] = chr($len);
            $this->out->write($this->hbuf, 0, 2);
        } else {
            $this->hbuf[0] = chr(self::TC_BLOCKDATALONG);
            Bits::putInt($this->hbuf, 1, $len);
            $this->out->write($this->hbuf, 0, 5);
        }
    }
    
    /* ---------- Primitive data output methods ---------- */
	
	/*
	 * The following methods are equivalent to their counterparts in DataOutputStream,
	 * except that they partition written data into data blocks when in block data mode.
	 */
	
	public function writeBoolean($v)
    {
        if ($this->pos >= self::$MAX_BLOCK_SIZE) {
            $this->drain();
        }
        Bits::putBoolean($this->buf, $this->pos++, $v);
    }

    public function writeSingleByte($s)
    {
        if ($this->pos >= self::$MAX_BLOCK_SIZE) {
            $this->drain();
        }
        $this->buf[$this->pos++] = chr($s);
    }

    public function writeShort($v)
    {
        if ($this->pos + 2 <= self::$MAX_BLOCK_SIZE) {
            Bits::putShort($this->buf, $this->pos, $v);
            $this->pos += 2;
        } else {
            $this->dout->writeShort($v);
        }
    }

    public function writeInt($v)
    {
        if ($this->pos + 4 <= self::$MAX_BLOCK_SIZE) {
            Bits::putInt($this->buf, $this->pos, $v);
            $this->pos += 4;
        } else {
            $this->dout->writeInt($v);
        }
    }

    public function writeFloat($v)
    {
        if ($this->pos + 4 <= self::$MAX_BLOCK_SIZE) {
            Bits::putFloat($this->buf, $this->pos, floatval($v));
            $this->pos += 4;
        } else {
            $this->dout->writeFloat($v);
        }
    }

    public function writeBytes($s)
    {
        $endoff = strlen($s);
        $cpos = 0;
        $csize = 0;
        for ($off = 0; $off < $endoff;) {
            if ($cpos >= $csize) {
                $cpos = 0;
                $csize = min(
                    array(
                        $endoff - $off,
                        self::$CHAR_BUF_SIZE
                    ));
                $this->cbuf = str_split(substr($s, $off, $off + $csize));
            }
            if ($this->pos >= self::$MAX_BLOCK_SIZE) {
                $this->drain();
            }
            $n = min(
                array(
                    $csize - $cpos,
                    self::$MAX_BLOCK_SIZE - $this->pos
                ));
            $stop = $this->pos + $n;
            while ($this->pos < $stop) {
                $this->buf[$this->pos++] = $this->cbuf[$cpos++];
            }
            $off += $n;
        }
    }
    
    /* ---------- Primitive data array output methods ---------- */
    /*
     * The following methods write out spans of primitive data values.
     * Though equivalent to calling the corresponding primitive write
     * methods repeatedly, these methods are optimized for writing groups
     * of primitive data values more efficiently.
     */
    public function writeBooleans(&$arr, $off, $len)
    {
        $endoff = $off + $len;
        while ($off < $endoff) {
            if ($this->pos >= self::$MAX_BLOCK_SIZE) {
                $this->drain();
            }
            $stop = min(
                [
                    $endoff,
                    $off + (self::$MAX_BLOCK_SIZE - $this->pos)
                ]);
            while ($off < $stop) {
                Bits::putBoolean($this->buf, $this->pos++, $arr[$off++]);
            }
        }
    }

    public function writeInts(&$arr, $off, $len)
    {
        $limit = self::$MAX_BLOCK_SIZE - 4;
        $endoff = $off + $len;
        while ($off < $endoff) {
            if ($this->pos <= $limit) {
                $avail = (self::$MAX_BLOCK_SIZE - $this->pos) >> 2;
                $stop = min(
                    [
                        $endoff,
                        $off + $avail
                    ]);
                while ($off < $stop) {
                    Bits::putInt($this->buf, $this->pos, $arr[$off++]);
                    $this->pos += 4;
                }
            } else {
                $this->dout->writeInt($arr[$off++]);
            }
        }
    }

    public function writeFloats(&$arr, $off, $len)
    {
        $limit = self::$MAX_BLOCK_SIZE - 4;
        $endoff = $off + $len;
        while ($off < $endoff) {
            if ($this->pos <= $limit) {
                $avail = (self::$MAX_BLOCK_SIZE - $this->pos) >> 2;
                $stop = min(
                    [
                        $endoff,
                        $off + $avail
                    ]);
                while ($off < $stop) {
                    Bits::putFloat($this->buf, $this->pos, $arr[$off++]);
                    $this->pos += 4;
                }
            } else {
                $this->dout->writeFloat($arr[$off++]);
            }
        }
    }
    
    /* ---------- String methods ---------- */
    
    /**
     * Returns the length in bytes of the UTF encoding of the given string.
     *
     * @param string $s The string to analyze.
     * @return int THe length in bytes of the given string.
     */
    public function getUTFLength($s)
    {
        $str = $s;
        if (!mb_check_encoding($s, 'UTF-8')) {
            $str = mb_convert_encoding($s, 'UTF-8');
        }
        // PHP function strlen() returns the number of bytes in the given
        // string. Function mb_strlen returns the number of characters.
        return strlen($str);
    }

    /**
     * Writes the given string in UTF format.
     * This method is used in situations
     * where the UTF encoding length of the string is already known; specifying
     * it explicitly avoids a prescan of the string to determine its UTF length.
     *
     * @param string $s The string to write.
     * @param int $utflen The length of the string in bytes.
     * @throws UTFDataFormatException if the string is longer than 65,535 bytes.
     * @see \KM\IO\DataOutput::writeUTF()
     */
    public function writeUTF($s, $utflen = -1)
    {
        if ($utflen == -1) {
            $utflen = $this->getUTFLength($s);
        }
        if ($utflen > 0xffff) {
            throw new UTFDataFormatException();
        }
        $this->writeShort($utflen);
        if ($utflen == strlen($s)) {
            $this->writeBytes($s);
        } else {
            $this->writeUTFBody($s);
        }
    }

    /**
     * Writes the given string in 'long' UTF format.
     * Long UTF format is
     * identival to standard UTF except that it uses a 4-byte int header
     * (instead of the standard 2-byte short) to convery the UTF encoding
     * length.
     *
     * @param string $s The string to write.
     * @param int $utflen The length of the string in bytes.
     */
    public function writeLongUTF($s, $utflen = -1)
    {
        if ($utflen == -1) {
            $utflen = $this->getUTFLength($s);
        }
        $this->writeInt($utflen);
        if ($utflen == strlen($s)) {
            $this->writeBytes($s);
        } else {
            $this->writeUTFBody($s);
        }
    }

    /**
     * Writes the body (i.e.
     * the UTF representation minus the 2-byte or 4-byte
     * length header) of the UTF encoding for the given string.
     *
     * @param string $s The string to write.
     */
    private function writeUTFBody($s)
    {
        $str = $s;
        if (!mb_check_encoding($s, 'UTF-8')) {
            $str = mb_convert_encoding($s, 'UTF-8');
        }
        $bytearr = str_split($str);
        
        $limit = self::$MAX_BLOCK_SIZE - 3;
        $len = strlen($str);
        for ($off = 0; $off < $len;) {
            $csize = min(
                [
                    $len - $off,
                    self::$CHAR_BUF_SIZE
                ]);
            System::arraycopy($bytearr, $off, $this->cbuf, 0, $csize);
            for ($cpos = 0; $cpos < $csize; $cpos++) {
                $c = $this->cbuf[$cpos];
                if ($this->pos <= $limit) {
                    $this->buf[$this->pos++] = $c;
                } else {
                    // Write one byte at a time to normalize block
                    $this->writeByte($c);
                }
            }
            $off += $csize;
        }
    }
}
?>