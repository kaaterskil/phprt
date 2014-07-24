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
namespace KM\IO\ObjectInputStream;

use KM\IO\Bits;
use KM\IO\DataInput;
use KM\IO\DataInputStream;
use KM\IO\EOFException;
use KM\IO\InputStream;
use KM\IO\IOException;
use KM\IO\ObjectInputStream;
use KM\IO\ObjectStreamConstants;
use KM\IO\StreamCorruptedException;
use KM\Lang\IllegalStateException;
use KM\Lang\System;

/**
 * Input stream with two modes: in default mode, inputs data written in the same
 * format as DataOutputStream; in "block data" mode, inputs data bracketed by
 * block data markers (see object serialization specification for details).
 * Buffering depends on block data mode: when in default mode, no data is
 * buffered in advance; when in block data mode, all data for the current data
 * block is read in at once (and buffered).
 *
 * @author Blair
 */
class BlockDataInputStream extends InputStream implements DataInput,
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
     * Length of char buffer (for reading strings)
     *
     * @var int
     */
    private static $CHAR_BUF_SIZE = 256;

    /**
     * readBlockHeader() return value indicating header read may block
     *
     * @var int
     */
    private static $HEADER_BLOCKED = -2;

    /**
     * Buffer for reading general block data.
     *
     * @var array
     */
    private $buf;

    /**
     * Buffer for reading block data headers.
     *
     * @var array
     */
    private $hbuf;

    /**
     * Char buffer for fast string reads.
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
     * End of offset of valid data in the buffer, or -1 if no more block data.
     *
     * @var int
     */
    private $end = -1;

    /**
     * The number of bytes in the current block yet to be read from the stream.
     *
     * @var int
     */
    private $unread = 0;

    /**
     * The underlying stream
     *
     * @var PeekInputStream
     */
    private $in;

    /**
     * Loop back stream (for data reads that span data blocks).
     *
     * @var DataInputStream
     */
    private $din;

    /**
     * The parent object input stream
     *
     * @var ObjectInputStream
     */
    private $oin;

    /**
     * Creates a new BlockDataInputStream on the the given underlying streams.
     * Block data mode is turned off by default,
     *
     * @param InputStream $in
     * @param ObjectInputStream $oin
     */
    public function __construct(InputStream $in, ObjectInputStream $oin)
    {
        $this->buf = array_fill(0, self::$MAX_BLOCK_SIZE, null);
        $this->hbuf = array_fill(0, self::$MAX_HEADER_SIZE, null);
        $this->cbuf = array_fill(0, self::$CHAR_BUF_SIZE, null);
        
        $this->in = new PeekInputStream($in);
        $this->din = new DataInputStream($oin);
        $this->oin = $oin;
    }

    /**
     * Sets block data mode to the given mode (true == on, false == off) and
     * returns the previous mode value.
     * If the new mode is the same as the old mode, no action is taken. Throws
     * IllegalStateException if block data mode is being switched from on to off
     * while un-consumed block data is still present in the stream.
     *
     * @param boolean $newmode
     * @throws IllegalStateException
     * @return boolean
     */
    public function setBlockDataMode($newmode)
    {
        if ($this->blkmode == $newmode) {
            return $this->blkmode;
        }
        if ($newmode) {
            $this->pos = 0;
            $this->end = 0;
            $this->unread = 0;
        } elseif ($this->pos < $this->end) {
            throw new IllegalStateException('unread block data');
        }
        $this->blkmode = $newmode;
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

    /**
     * If in block data mode, skips to the end of the current group of data
     * blocks (but does not unset block data mode).
     * If not in block data mode,
     * throws an IllegalStateException.
     *
     * @throws IllegalStateException
     */
    public function skipBlockData()
    {
        if (!$this->blkmode) {
            throw new IllegalStateException('not in block data mode');
        }
        while ($this->end >= 0) {
            $this->refill();
        }
    }

    /**
     * Attempts to read in the next block data header (if any).
     * If canBlock is false and a full header cannot be read without possibly
     * blocking, returns HEADER_BLOCKED, else if the next element in the stream
     * is a block data header, returns the block data length specified by the
     * header, else returns -1.
     *
     * @param boolean $canBlock
     * @throws StreamCorruptedException
     * @return int
     */
    private function readBlockHeader($canBlock)
    {
        if ($this->oin->defaultDataEnd) {
            // Stream is currently at the end of a field value block written via
            // default serialization. Since there is no terminating
            // TC_ENDBLOCKDATA tag, simulate end-of-custom-data behavior
            // explicitly.
            return -1;
        }
        try {
            for (;;) {
                $avail = $canBlock ? PHP_INT_MAX : $this->in->available();
                if ($avail == 0) {
                    return self::$HEADER_BLOCKED;
                }
                
                $tc = $this->in->peek();
                switch ($tc) {
                    case self::TC_BLOCKDATA:
                        if ($avail < 2) {
                            return self::$HEADER_BLOCKED;
                        }
                        $this->in->readFully($this->hbuf, 0, 2);
                        return ord($this->hbuf[1] & 0xff);
                    
                    case self::TC_BLOCKDATALONG:
                        if ($avail < 5) {
                            return self::$HEADER_BLOCKED;
                        }
                        $this->in->readFully($this->hbuf, 0, 5);
                        $len = Bits::getInt($this->hbuf, 1);
                        if ($len < 0) {
                            throw new StreamCorruptedException(
                                'illegal block data header length: ' . $len);
                        }
                        return $len;
                    
                    case self::TC_RESET:
                        // TC_RESETs may occur in between data blocks.
                        // Unfortunately, this case must be parsed at a lower
                        // level than other type codes, since primitive data
                        // reads may span data blocks separated by a TC_RESET.
                        $this->in->readByte();
                        $this->oin->handleReset();
                        break;
                    
                    default:
                        if ($tc >= 0 &&
                             ($tc < self::TC_BASE || $tc > self::TC_MAX)) {
                            $format = __METHOD__ . ' Invalid type code: %02X';
                            throw new StreamCorruptedException(
                                sprintf($format, $tc));
                        }
                        return -1;
                }
            }
        } catch (EOFException $e) {
            throw new StreamCorruptedException(
                'unexpected EOF while reading block data header');
        }
    }

    /**
     * Refills internal buffer buf with block data.
     * Any data in buf at the time of the call is considered consumed. Sets the
     * pos, end, and unread fields to reflect the new amount of available block
     * data; if the next element in the stream is not a data block, sets pos and
     * unread to 0 and end to -1.
     *
     * @throws StreamCorruptedException
     * @throws IOException
     */
    private function refill()
    {
        try {
            do {
                $this->pos = 0;
                if ($this->unread > 0) {
                    $n = $this->in->read($this->buf, 0,
                        min(
                            array(
                                $this->unread,
                                self::$MAX_BLOCK_SIZE
                            )));
                    if ($n >= 0) {
                        $this->end = $n;
                        $this->unread -= $n;
                    } else {
                        throw new StreamCorruptedException(
                            'unexpected EOF in middle of data block');
                    }
                } else {
                    $n = $this->readBlockHeader(true);
                    if ($n >= 0) {
                        $this->end = 0;
                        $this->unread = $n;
                    } else {
                        $this->end = -1;
                        $this->unread = 0;
                    }
                }
            } while ($this->pos == $this->end);
        } catch (IOException $e) {
            $this->pos = 0;
            $this->end = -1;
            $this->unread = 0;
            throw $e;
        }
    }

    /**
     * If in block data mode, returns the number of un-consumed bytes remaining
     * in the current data block.
     * If not in block data mode, throws an
     * IllegalStateException.
     *
     * @throws IllegalStateException
     * @return int
     */
    public function currentBlockRemaining()
    {
        if ($this->blkmode) {
            return ($this->end >= 0) ? ($this->end - $this->pos) + $this->unread : 0;
        } else {
            throw new IllegalStateException();
        }
    }

    /**
     * Peeks at (but does not consume) and returns the next byte value in the
     * stream, or -1 if the end of the stream/block data (if in block data mode)
     * has been reached.
     *
     * @return int
     */
    public function peek()
    {
        if ($this->blkmode) {
            if ($this->pos == $this->end) {
                $this->refill();
            }
            return ($this->end >= 0) ? ord($this->buf[$this->pos] & 0xff) : -1;
        } else {
            return $this->in->peek();
        }
    }

    /**
     * Peeks at (but does not consume) and returns the next byte value in the
     * stream, or throws EOFException if end of stream/block data has been
     * reached.
     *
     * @throws EOFException
     * @return string
     */
    public function peekByte()
    {
        $val = $this->peek();
        if ($val < 0) {
            throw new EOFException();
        }
        return $val;
    }
    
    /* ----------------- generic input stream methods ------------------ */
	
	/*
	 * The following methods are equivalent to their counterparts in
	 * InputStream, except that they interpret data block boundaries and
	 * read the requested data from within data blocks when in block data
	 * mode.
	 */
	
	public function readByte()
    {
        if ($this->blkmode) {
            if ($this->pos == $this->end) {
                $this->refill();
            }
            return ($this->end >= 0) ? ord($this->buf[$this->pos++] & 0xff) : -1;
        } else {
            return $this->in->readByte();
        }
    }

    public function read(array &$b, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($b);
        }
        return $this->readAndCopy($b, $off, $len, false);
    }

    public function skip($len)
    {
        $remain = $len;
        while ($remain > 0) {
            if ($this->blkmode) {
                if ($this->pos == $this->end) {
                    $this->refill();
                }
                if ($this->end < 0) {
                    break;
                }
                $nread = min(
                    array(
                        $remain,
                        $this->end - $this->pos
                    ));
                $remain -= $nread;
                $this->pos += $nread;
            } else {
                $nread = min(
                    array(
                        $remain,
                        self::$MAX_BLOCK_SIZE
                    ));
                if (($nread = $this->in->read($this->buf, 0, $nread)) < 0) {
                    break;
                }
                $remain -= $nread;
            }
        }
        return $len - $remain;
    }

    public function available()
    {
        if ($this->blkmode) {
            if (($this->pos == $this->end) && ($this->unread == 0)) {
                $n;
                while (($n = $this->readBlockHeader(false)) == 0) {
                    // Noop
                }
                switch ($n) {
                    case self::$HEADER_BLOCKED:
                        break;
                    
                    case -1:
                        $this->pos = 0;
                        $this->end = -1;
                        break;
                    
                    default:
                        $this->pos = 0;
                        $this->end = 0;
                        $this->unread = $n;
                        break;
                }
            }
            // avoid unnecessary call to in.available() if possible
            $unreadAvail = ($this->unread > 0) ? min(
                array(
                    $this->in->available(),
                    $this->unread
                )) : 0;
            return ($this->end >= 0) ? ($this->end - $this->pos) + $unreadAvail : 0;
        } else {
            return $this->in->available();
        }
    }

    public function close()
    {
        if ($this->blkmode) {
            $this->pos = 0;
            $this->end = -1;
            $this->unread = 0;
        }
        $this->in->close();
    }

    /**
     * Attempts to read len bytes into byte array b at offset off.
     * Returns the number of bytes read, or -1 if the end of stream/block data
     * has been reached. If copy is true, reads values into an intermediate
     * buffer before copying them to b (to avoid exposing a reference to b).
     *
     * @param array $b
     * @param int $off
     * @param int $len
     * @param boolean $copy
     * @return int
     */
    public function readAndCopy(array &$b, $off, $len, $copy)
    {
        if ($len == 0) {
            return 0;
        } elseif ($this->blkmode) {
            if ($this->pos == $this->end) {
                $this->refill();
            }
            if ($this->end < 0) {
                return -1;
            }
            $nread = min(
                array(
                    $len,
                    $this->end - $this->pos
                ));
            System::arraycopy($this->buf, $this->pos, $b, $off, $nread);
            $this->pos += $nread;
            return $nread;
        } elseif ($copy) {
            $nread = $this->in->read($this->buf, 0,
                min(
                    array(
                        $len,
                        self::$MAX_BLOCK_SIZE
                    )));
            if ($nread > 0) {
                System::arraycopy($this->buf, 0, $b, $off, $nread);
            }
            return $nread;
        } else {
            return $this->in->read($b, $off, $len);
        }
    }
    
    /* ----------------- primitive data input methods ------------------ */
    
    /*
     * The following methods are equivalent to their counterparts in
     * DataInputStream, except that they interpret data block boundaries
     * and read the requested data from within data blocks when in block
     * data mode.
     */

    public function readFully(array &$b, $off = 0, $len = null, $copy = false)
    {
        if ($len == null) {
            $len = count($b);
        }
        while ($len > 0) {
            $n = $this->readAndCopy($b, $off, $len, $copy);
            if ($n < 0) {
                throw new EOFException();
            }
            $off += $n;
            $len -= $n;
        }
    }

    public function skipBytes($n)
    {
        return $this->din->skipBytes($n);
    }

    public function readBoolean()
    {
        $v = $this->readByte();
        if ($v < 0) {
            throw new EOFException();
        }
        return ($v != 0);
    }

    public function readSingleByte()
    {
        $v = $this->readByte();
        if ($v < 0) {
            throw new EOFException();
        }
        return $v;
    }

    public function readShort()
    {
        if (!$this->blkmode) {
            $this->pos = 0;
            $this->in->readFully($this->buf, 0, 2);
        } elseif ($this->end - $this->pos < 2) {
            return $this->din->readShort();
        }
        $v = Bits::getShort($this->buf, $this->pos);
        $this->pos += 2;
        return $v;
    }

    public function readUnsignedShort()
    {
        if (!$this->blkmode) {
            $this->pos = 0;
            $this->in->readFully($this->buf, 0, 2);
        } elseif ($this->end - $this->pos < 2) {
            return $this->din->readUnsignedShort();
        }
        $v = Bits::getShort($this->buf, $this->pos) & 0xffff;
        $this->pos += 2;
        return $v;
    }

    public function readInt()
    {
        if (!$this->blkmode) {
            $this->pos = 0;
            $this->in->readFully($this->buf, 0, 4);
        } elseif ($this->end - $this->pos < 4) {
            return $this->din->readInt();
        }
        $v = Bits::getInt($this->buf, $this->pos);
        $this->pos += 4;
        return $v;
    }

    public function readFloat()
    {
        if (!$this->blkmode) {
            $this->pos = 0;
            $this->in->readFully($this->buf, 0, 4);
        } elseif ($this->end - $this->pos < 4) {
            return $this->din->readFloat();
        }
        $v = Bits::getFloat($this->buf, $this->pos);
        $this->pos += 4;
        return $v;
    }

    public function readLong()
    {
        if (!$this->blkmode) {
            $this->pos = 0;
            $this->in->readFully($this->buf, 0, 8);
        } elseif ($this->end - $this->pos < 8) {
            return $this->din->readLong();
        }
        $v = Bits::getLong($this->buf, $this->pos);
        $this->pos += 8;
        return $v;
    }
    
    /* ---------- Primitive data array input methods ---------- */
    /*
     * The following methods read in spans of primitive data values.
     * Though equivalent to calling the corresponding primitive read
     * methods repeatedly, these methods are optimized for reading groups
     * of primitive data values more efficiently.
     */
    
    public function readBooleans(&$arr, $off, $len) {
        $stop = 0;
        $endoff = $off + $len;
        while($off < $endoff) {
            if(!$this->blkmode) {
                $span = min([$endoff - $off, self::$MAX_BLOCK_SIZE]);
                $this->in->readFully($this->buf, 0, $span);
                $stop = $off + $span;
                $this->pos = 0;
            } elseif ($this->end - $this->pos < 1) {
                $arr[$off++] = $this->din->readBoolean();
                continue;
            } else {
                $stop = min([$endoff, $off + $this->end - $pos]);
            }
            while($off < $stop) {
                $arr[$off++] = Bits::getBoolean($this->buf, $this->pos++);
            }
        }
    }
    
    public function readInts(&$arr, $off, $len) {
        $stop = 0;
        $endoff = $off + $len;
        while($off < $endoff) {
            if(!$this->blkmode) {
                $span = min([$endoff - $off, self::$MAX_BLOCK_SIZE >> 2]);
                $this->in->readFully($this->buf, 0, $span << 2);
                $stop = $off + $span;
                $this->pos = 0;
            } elseif ($this->end - $this->pos < 4) {
                $arr[$off++] = $this->din->readInt();
                continue;
            } else {
                $stop = min([$endoff, $off + (($this->end - $this->pos) >> 2)]);
            }
            while ($off < $stop) {
                $arr[$off++] = Bits::getInt($this->buf, $this->pos);
                $this->pos += 4;
            }
        }
    }
    
    public function readFloats(&$arr, $off, $len) {
        $stop = 0;
        $endoff = $off + $len;
        while($off < $endoff) {
            if(!$this->blkmode) {
                $span = min([$endoff - $off, self::$MAX_BLOCK_SIZE >> 2]);
                $this->in->readFully($this->buf, 0, $span << 2);
                $this->pos = 0;
            } elseif ($this->end - $this->pos < 4) {
                $arr[$off++] = $this->din->readFloat();
                continue;
            } else {
                $stop = min([$endoff - $off, (($this->end - $this->pos) >> 2)]);
            }
            while ($off < $stop) {
                $arr[$off++] = Bits::getFloat($this->buf, $this->pos);
                $this->pos += 4;
            }
        }
    }
    
    /* ---------- String methods ---------- */

    public function readUTF()
    {
        return $this->readUTFBody($this->readUnsignedShort());
    }

    /**
     * Reads in string written in "long" UTF format.
     * "Long" UTF format is identical to standard UTF, except that it uses an 8
     * byte header (instead of the standard 2 bytes) to convey the UTF encoding
     * length.
     *
     * @return string
     */
    public function readLongUTF()
    {
        return $this->readUTFBody($this->readLong());
    }

    /**
     * Reads in the "body" (i.e., the UTF representation minus the 2-byte
     * or 8-byte length header) of a UTF encoding, which occupies the next
     * utflen bytes.
     *
     * @param int $utflen
     * @return string
     */
    public function readUTFBody($utflen)
    {
        $sb = '';
        if (!$this->blkmode) {
            $this->end = $this->pos = 0;
        }
        
        while ($utflen > 0) {
            $avail = $this->end - $this->pos;
            if ($avail >= 3 || $avail == $utflen) {
                $utflen -= $this->readUTFSpan($sb, $utflen);
            } else {
                if ($this->blkmode) {
                    // Near block boundary, read one byte at a time.
                    $utflen -= $this->readUTFChar($sb, $utflen);
                } else {
                    // Shift and refill buffer manually
                    if ($avail > 0) {
                        System::arraycopy($this->buf, $this->pos, $this->buf, 0,
                            $avail);
                    }
                    $this->pos = 0;
                    $this->end = min(
                        [
                            self::$MAX_BLOCK_SIZE,
                            $utflen
                        ]);
                    $this->in->readFully($this->buf, $avail,
                        $this->end - $avail);
                }
            }
        }
        return utf8_decode($sb);
    }

    /**
     * Reads span of UTF-encoded characters out of internal buffer (starting at
     * offset pos and ending at or before offset end), consuming no more than
     * utflen bytes.
     * Appends read characters to sbuf. Returns the number of bytes consumed.
     *
     * @param string $sb The data buffer into which the bytes are stored.
     * @param int $utflen The length of the input string
     * @return int The number of bytes read.
     */
    private function readUTFSpan(&$sb, $utflen)
    {
        $cpos = 0;
        $start = $this->pos;
        $avail = min(
            [
                $this->end - $this->pos,
                self::$CHAR_BUF_SIZE
            ]);
        $stop = $this->pos + (($utflen > $avail) ? $avail - 2 : $utflen);
        $outOfBounds = false;
        
        try {
            while ($this->pos < $stop) {
                $this->cbuf[$cpos++] = $this->buf[$this->pos++];
            }
        } finally {
            if (($this->pos - $start) > $utflen) {
                $this->pos = $start + $utflen;
            }
        }
        $sb .= substr(implode('', $this->cbuf), 0, $cpos);
        return $this->pos - $start;
    }

    /**
     * Reads in single UTF-encoded character one byte at a time, appends
     * the character to sbuf, and returns the number of bytes consumed.
     * This method is used when reading in UTF strings written in block
     * data mode to handle UTF-encoded characters which (potentially)
     * straddle block-data boundaries.
     *
     * @param string $sb The string buffer into which the data is written.
     * @param int $utflen The length of the input string.
     */
    private function readUTFChar(&$sb, $utflen)
    {
        $sb .= $this->readSingleByte();
        return 1;
    }
}
?>