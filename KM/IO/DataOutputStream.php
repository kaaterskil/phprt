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

use KM\IO\DataOutput;
use KM\IO\FilterOutputStream;

/**
 * A data output stream lets an application write primitive data types to an
 * output stream in a portable way. An application can then use a data input
 * stream to read the data back in.
 *
 * @author Blair
 */
class DataOutputStream extends FilterOutputStream implements DataOutput
{

    /**
     * The number of bytes written to the data output stream so far. If this
     * counter overflows, it will be wrapped to PHP_INT_MAX.
     *
     * @var int
     */
    protected $written;

    /**
     * Creates a new data output stream to write data to the specified
     * underlying output stream. The counter <code>written</code> is set to
     * zero.
     *
     * @param OutputStream $out The underlying out stream to be saved for later
     *            use.
     */
    public function __construct(OutputStream $out)
    {
        parent::__construct($out);
    }

    /**
     * Increases the written counter by the specified value until it reaches
     * Integer.MAX_VALUE.
     *
     * @param int $value
     */
    private function incCount($value)
    {
        $temp = $this->written + (int) $value;
        if ($temp < 0) {
            $temp = PHP_INT_MAX;
        }
        $this->written = $temp;
    }

    /**
     * Writes the specified byte (the low eight bits of the argument
     * <code>b</code>) to the underlying output stream. If no exception is
     * thrown, the counter <code>written</code> is incremented by
     * <code>1</code>. <p> Implements the <code>write</code> method of
     * <code>OutputStream</code>.
     *
     * @param int $b The <code>byte</code> to be written.
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\FilterOutputStream::writeByte()
     */
    public function writeByte($b)
    {
        $this->out->writeByte($b);
        $this->incCount(1);
    }

    /**
     * Writes <code>len</code> bytes from the specified byte array starting at
     * offset <code>off</code> to the underlying output stream. If no exception
     * is thrown, the counter <code>written</code> is incremented by
     * <code>len</code>.
     *
     * @param array $b The data.
     * @param int $off The start offset in the data.
     * @param int $len The number of bytes to write.
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\FilterOutputStream::write()
     */
    public function write(array &$b, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($b);
        }
        $this->out->write($b, $off, $len);
        $this->incCount($len);
    }

    /**
     * Flushes this data output stream. This forces any buffered output bytes to
     * be written out to the stream. <p> The <code>flush</code> method of
     * <code>DataOutputStream</code> calls the <code>flush</code> method of its
     * underlying output stream.
     *
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\FilterOutputStream::flush()
     */
    public function flush()
    {
        $this->out->flush();
    }

    /**
     * Writes a <code>boolean</code> to the underlying output stream as a 1-byte
     * value. The value <code>true</code> is written out as the value
     * <code>(byte)1</code>; the value <code>false</code> is written out as the
     * value <code>(byte)0</code>. If no exception is thrown, the counter
     * <code>written</code> is incremented by <code>1</code>.
     *
     * @param boolean $v A <code>boolean</code> value to be written.
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\DataOutput::writeBoolean()
     */
    public function writeBoolean($v)
    {
        $v = (boolean) $v;
        $this->out->writeByte($v ? 1 : 0);
        $this->incCount(1);
    }

    /**
     * Writes out a <code>byte</code> to the underlying output stream as a
     * 1-byte value. If no exception is thrown, the counter <code>written</code>
     * is incremented by <code>1</code>.
     *
     * @param int $b A <code>byte</code> value to be written.
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\DataOutput::writeSingleByte()
     */
    public function writeSingleByte($b)
    {
        $this->out->writeByte($b);
        $this->incCount(1);
    }

    /**
     * Writes a <code>short</code> to the underlying output stream as two bytes,
     * high byte first. If no exception is thrown, the counter
     * <code>written</code> is incremented by <code>2</code>.
     *
     * @param int $v
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\DataOutput::writeShort()
     */
    public function writeShort($v)
    {
        $int = (int) $v;
        $bin = str_split(pack('n', $int));
        foreach ($bin as $byte) {
            $this->out->writeByte($byte);
        }
        $this->incCount(2);
    }

    /**
     * Writes an <code>int</code> to the underlying output stream as four bytes,
     * high byte first. If no exception is thrown, the counter
     * <code>written</code> is incremented by <code>4</code>.
     *
     * @param int $v An <code>int</code> to be written.
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\DataOutput::writeInt()
     */
    public function writeInt($v)
    {
        $int = (int) $v;
        $bin = str_split(pack('N', $int));
        foreach ($bin as $byte) {
            $this->out->writeByte($byte);
        }
        $this->incCount(4);
    }

    /**
     * Converts the float argument to an <code>int</code> using the PHP
     * <code>pack('f', v)</code> function, and then writes that <code>int</code>
     * value to the underlying output stream as a 4-byte quantity, high byte
     * first using the PHP <code>unpack('C*', value)</code> function. If no
     * exception is thrown, the counter <code>written</code> is incremented by
     * <code>4</code>.
     *
     * @param float $v A <code>float</code> value to be written.
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\DataOutput::writeFloat()
     */
    public function writeFloat($v)
    {
        $float = (float) $v;
        $bin = str_split(pack('f', $float));
        foreach ($bin as $byte) {
            $this->out->writeByte($byte);
        }
        $this->incCount(4);
    }

    /**
     * Writes out the string to the underlying output stream as a sequence of
     * bytes. Each character in the string is written out, in sequence, by
     * discarding its high eight bits. If no exception is thrown, the counter
     * <code>written</code> is incremented by the length of <code>s</code>.
     *
     * @param string $s A string of bytes to be written.
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\DataOutput::writeBytes()
     */
    public function writeBytes($s)
    {
        $len = strlen($s);
        for ($i = 0; $i < $len; $i ++) {
            $this->out->writeByte($s[$i]);
        }
        $this->incCount($len);
    }

    /**
     * Writes a string to the underlying output stream using modified UTF-8
     * encoding in a machine-independent manner. <p> First, two bytes are
     * written to the output stream as if by the <code>writeShort</code> method
     * giving the number of bytes to follow. This value is the number of bytes
     * actually written out, not the length of the string. Following the length,
     * each character of the string is output, in sequence, using the modified
     * UTF-8 encoding for the character. If no exception is thrown, the counter
     * <code>written</code> is incremented by the total number of bytes written
     * to the output stream. This will be at least two plus the length of
     * <code>str</code>, and at most two plus thrice the length of
     * <code>str</code>.
     *
     * @param string $s The string to be written.
     * @throws IOException If an I/O error occurs.
     * @see \KM\IO\DataOutput::writeUTF()
     */
    public function writeUTF($str)
    {
        self::writeUTFString($str, $this);
    }

    /**
     * Writes s string to the specified DataOutput using UTF-8 encoding. First,
     * two bytes are written to out as if by the <code>sriteShort()</code>
     * method giving the number of bytes to follow. This value is number of
     * bytes actually written out, not the length of the string. Following the
     * length, each character of the string is output, in sequence, using the
     * UTF-8 encoding for the character. If no exception is thrown, the counter
     * <code>written</code> is incremented by the total number of bytes written
     * to the output stream. This will be at least two plus the length of the
     * <code>str</code>, and at most two plus thrice the length of
     * <code>str</code>.
     *
     * @param string $str
     * @param DataOutput $out
     * @throws UTFDataFormatException
     * @return int
     */
    public static function writeUTFString($str, DataOutput $out)
    {
        $s = $str;
        if (! mb_check_encoding($str, 'UTF-8')) {
            $s = mb_convert_encoding($str, 'UTF-8');
        }
        
        // PHP function strlen() computes the number of bytes in a given string;
        // the function mb_strlen() computes the number of characters.
        $utflen = strlen($s);
        if ($utflen > 65535) {
            throw new UTFDataFormatException('encoded string too long: ' . $utflen . ' bytes');
        }
        
        // Write out the length of the UTF string as a 16-bit unsigned short
        // (big endian)
        $bin = str_split(pack('n', $utflen));
        foreach ($bin as $byte) {
            $out->writeByte($byte);
        }
        
        // Now prepare and write the UTF body
        $bytearr = array_fill(0, $utflen, null);
        for ($i = 0; $i < $utflen; $i ++) {
            $bytearr[$i] = $s[$i];
        }
        $out->write($bytearr, 0, $utflen);
        return $utflen;
    }

    /**
     * Returns the current value of the counter <code>written</code>, the number
     * of bytes written to this data output stream so far. If the counter
     * overflows, it will be wrapped to PHP_INT_MAX.
     *
     * @return int The value of the <code>written</code> field.
     */
    public function size()
    {
        return $this->written;
    }
}
?>