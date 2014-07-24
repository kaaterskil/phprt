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

/**
 * The <code>DataOutput</code> interface provides for converting data from any
 * of the primitive types to a series of bytes and writing these bytes to a
 * binary stream. There is also a facility for converting a <code>String</code>
 * into <a href="DataInput.html#modified-utf-8">modified UTF-8</a> format and
 * writing the resulting series of bytes. <p> For all the methods in this
 * interface that write bytes, it is generally true that if a byte cannot be
 * written for any reason, an <code>IOException</code> is thrown.
 *
 * @author Blair
 */
interface DataOutput
{

    /**
     * Writes to the output stream the eight low-order bits of the argument
     * <code>b</code>. The 24 high-order bits of <code>b</code> are ignored.
     *
     * @param int $b The byte to be written
     * @throws IOException if an I/O error occurs.
     */
    public function writeByte($b);

    /**
     * Writes <code>len</code> bytes from array <code>b</code>, in order, to the
     * output stream. If <code>b</code> is <code>null</code>, a
     * <code>NullPointerException</code> is thrown. If <code>off</code> is
     * negative, or <code>len</code> is negative, or <code>off+len</code> is
     * greater than the length of the array <code>b</code>, then an
     * <code>IndexOutOfBoundsException</code> is thrown. If <code>len</code> is
     * zero, then no bytes are written. Otherwise, the byte <code>b[off]</code>
     * is written first, then <code>b[off+1]</code>, and so on; the last byte
     * written is <code>b[off+len-1]</code>.
     *
     * @param array $b The data.
     * @param int $off The start offset in the data.
     * @param int $len The number of bytes to write.
     * @throws IOException if an I/O error occurs.
     */
    public function write(array &$b, $off = 0, $len = null);

    /**
     * Writes a <code>boolean</code> to the underlying output stream as a 1-byte
     * value. The value <code>true</code> is written out as the value
     * <code>(byte)1</code>; the value <code>false</code> is written out as the
     * value <code>(byte)0</code>. If no exception is thrown, the counter
     * <code>written</code> is incremented by <code>1</code>.
     *
     * @param boolean $v A <code>boolean</code> value to be written.
     * @throws IOException if an I/O error occurs.
     */
    public function writeBoolean($v);

    /**
     * Writes to the output stream the eight low- order bits of the argument
     * <code>v</code>. The 24 high-order bits of <code>v</code> are ignored.
     * (This means that <code>writeByte</code> does exactly the same thing as
     * <code>write</code> for an integer argument.) The byte written by this
     * method may be read by the <code>readByte</code> method of interface
     * <code>DataInput</code>, which will then return a <code>byte</code> equal
     * to <code>(byte)v</code>.
     *
     * @param int $b The byte value to be written.
     * @throws IOException if an I/O error occurs.
     */
    public function writeSingleByte($b);

    /**
     * Writes a <code>short</code> to the underlying output stream as two bytes,
     * high byte first. If no exception is thrown, the counter
     * <code>written</code> is incremented by <code>2</code>.
     *
     * @param int $v
     */
    public function writeShort($v);

    /**
     * Writes an <code>int</code> to the underlying output stream as a signed
     * integer. If no exception is thrown, the counter <code>written</code> is
     * incremented by <code>4</code>.
     *
     * @param int $v An <code>int</code> to be written.
     * @throws IOException if an I/O error occurs.
     */
    public function writeInt($v);

    /**
     * Converts the float argument to a byte array using the pack('f', value)
     * method. If no exception is thrown, the counter <code>written</code> is
     * incremented by <code>4</code>.
     *
     * @param float $v A <code>float</code> to be written.
     * @throws IOException if an I/O error occurs.
     */
    public function writeFloat($v);

    /**
     * Writes a string to the output stream. For every character in the string
     * <code>s</code>, taken in order, one byte is written to the output stream.
     * If <code>s</code> is <code>null</code>, a
     * <code>NullPointerException</code> is thrown.<p> If <code>s.length</code>
     * is zero, then no bytes are written. Otherwise, the character
     * <code>s[0]</code> is written first, then <code>s[1]</code>, and so on;
     * the last character written is <code>s[s.length-1]</code>. For each
     * character, one byte is written, the low-order byte, in exactly the manner
     * of the <code>writeByte</code> method . The high-order eight bits of each
     * character in the string are ignored.
     *
     * @param string $s The string of bytes to be written.
     * @throws IOException if an I/O error occurs.
     */
    public function writeBytes($s);

    /**
     * Writes two bytes of length information to the output stream, followed by
     * the UTF-8 representation of every character in the string <code>s</code>.
     * If <code>s</code> is <code>null</code>, a
     * <code>NullPointerException</code> is thrown. Each character in the string
     * <code>s</code> is converted to a group of one, two, or three bytes,
     * depending on the value of the character. <p> First, the total number of
     * bytes needed to represent all the characters of <code>s</code> is
     * calculated. If this number is larger than <code>65535</code>, then a
     * <code>UTFDataFormatException</code> is thrown. Otherwise, this length is
     * written to the output stream in exactly the manner of the
     * <code>writeShort</code> method; after this, the one-, two-, or three-byte
     * representation of each character in the string <code>s</code> is
     * written.<p> The bytes written by this method may be read by the
     * <code>readUTF</code> method of interface <code>DataInput</code> , which
     * will then return a <code>String</code> equal to <code>s</code>.
     *
     * @param string $s The string value to be written.
     * @throws IOException if an I/O error occurs.
     */
    public function writeUTF($s);
}
?>