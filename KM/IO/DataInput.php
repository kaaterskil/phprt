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
 * The <code>DataInput</code> interface provides for reading bytes from a binary
 * stream and reconstructing from them data in any of the PHP primitive types.
 * There is also a facility for reconstructing a string from data in UTF_8
 * format. <p> It is generally true of all the reading routines in this
 * interface that if end of file is reached before the desired number of bytes
 * has been read, an <code>EOFException</code> (which is a kind of
 * <code>IOException</code>) is thrown. If any byte cannot be read for any
 * reason other than end of file, an <code>IOException</code> other than
 * <code>EOFException</code> is thrown. In particular, an
 * <code>IOException</code> may be thrown if the input stream has been closed.
 * <p> Implementations of the DataInput and DataOutput interfaces represent
 * ISO-8859-1 strings in a UTF-8 format. Note that in the following table, the
 * most significant bit appears in the far left-hand column.
 *
 * @author Blair
 */
interface DataInput
{

    /**
     * Reads <code>len</code> bytes from an input stream. <p> This method blocks
     * until one of the following conditions occurs: <ul> <li><code>len</code>
     * bytes of input data are available, in which case a normal return is made.
     * <li>End of file is detected, in which case an <code>EOFException</code>
     * is thrown. <li>An I/O error occurs, in which case an
     * <code>IOException</code> other than <code>EOFException</code> is thrown.
     * </ul> <p> If <code>b</code> is <code>null</code>, a
     * <code>NullPointerException</code> is thrown. If <code>off</code> is
     * negative, or <code>len</code> is negative, or <code>off+len</code> is
     * greater than the length of the array <code>b</code>, then an
     * <code>IndexOutOfBoundsException</code> is thrown. If <code>len</code> is
     * zero, then no bytes are read. Otherwise, the first byte read is stored
     * into element <code>b[off]</code>, the next one into
     * <code>b[off+1]</code>, and so on. The number of bytes read is, at most,
     * equal to <code>len</code>.
     *
     * @param array $b The buffer into which the data is read.
     * @param int $off An int specifying an offset into the data.
     * @param int $len An int specifying the number of bytes to read.
     * @throws EOFException if this stream reaches the end before reading all
     *         the bytes.
     * @throws IOException if an I/O error occurs.
     */
    public function readFully(array &$b, $off = 0, $len = null);

    /**
     * Makes an attempt to skip over <code>n</code> bytes of data from the input
     * stream, discarding the skipped bytes. However, it may skip over some
     * smaller number of bytes, possibly zero. This may result from any of a
     * number of conditions; reaching end of file before <code>n</code> bytes
     * have been skipped is only one possibility. This method never throws an
     * <code>EOFException</code>. The actual number of bytes skipped is
     * returned.
     *
     * @param int $n The number of bytes to be skipped.
     * @return int The number of bytes actually skipped.
     * @throws IOException if an I/O error occurs.
     */
    public function skipBytes($n);

    /**
     * Reads one input byte and returns <code>true</code> if that byte is
     * nonzero, <code>false</code> if that byte is zero. This method is suitable
     * for reading the byte written by the <code>writeBoolean</code> method of
     * interface <code>DataOutput</code>.
     *
     * @return boolean The <code>boolean</code> value read.
     * @throws IOException if an I/O error occurs.
     */
    public function readBoolean();

    /**
     * Reads and returns one input byte. The byte is treated as a signed value
     * in the range <code>-128</code> through <code>127</code>, inclusive. This
     * method is suitable for reading the byte written by the
     * <code>writeByte</code> method of interface <code>DataOutput</code>.
     *
     * @return string The 8-bit value read.
     * @throws IOException if an I/O error occurs.
     */
    public function readSingleByte();

    /**
     * Reads two input bytes and returns a <code>short</code> value. Let
     * <code>a</code> be the first byte read and <code>b</code> be the second
     * byte. The value returned is: <pre><code>unpack('s',
     * $data)][1]</code></pre> This method is suitable for reading the bytes
     * written by the <code>writeShort</code> method of interface
     * <code>DataOutput</code>.
     *
     * @return int The 16-bit value read.
     * @throws EOFException if this stream reaches the end before reading all
     *         the bytes.
     * @throws IOException if an I/O error occurs.
     */
    public function readShort();

    /**
     * Reads two input bytes and returns an <code>int</code> value in the range
     * <code>0</code> through <code>65535</code>. Let <code>a</code> be the
     * first byte read and <code>b</code> be the second byte. The value returned
     * is: <pre><code>unpack('n', $data)][1]</code></pre> This method is
     * suitable for reading the bytes written by the <code>writeShort</code>
     * method of interface <code>DataOutput</code> if the argument to
     * <code>writeShort</code> was intended to be a value in the range
     * <code>0</code> through <code>65535</code>.
     *
     * @return int The unsigned 16-bit value read.
     * @throws EOFException if this stream reaches the end before reading all
     *         the bytes.
     * @throws IOException if an I/O error occurs.
     */
    public function readUnsignedShort();

    /**
     * Reads four input bytes and returns an <code>int</code> value. Let
     * <code>a-d</code> be the first through fourth bytes read. The value
     * returned is: <pre><code>unpack('N', $data)[1]</code></pre> This method is
     * suitable for reading bytes written by the <code>writeInt</code> method of
     * interface <code>DataOutput</code>.
     *
     * @return int The <code>int</code> value read.
     * @throws IOException if an I/O error occurs.
     */
    public function readInt();

    /**
     * Reads eight input bytes and returns a <code>long</code> value. Let
     * <code>a-h</code> be the first through eighth bytes read. The value
     * returned is: <pre>{@code (((long)(a & 0xff) << 56) | ((long)(b & 0xff) <<
     * 48) | ((long)(c & 0xff) << 40) | ((long)(d & 0xff) << 32) | ((long)(e &
     * 0xff) << 24) | ((long)(f & 0xff) << 16) | ((long)(g & 0xff) << 8) |
     * ((long)(h & 0xff))) </code></pre> <p> This method is suitable for reading
     * bytes written by the <code>writeLong</code> method of interface
     * <code>DataOutput</code>.
     *
     * @return int The <code>long</code> value read.
     * @throws EOFException if this stream reaches the end before reading all
     *         the bytes.
     * @throws IOException if an I/O error occurs.
     */
    public function readLong();

    /**
     * Reads four input bytes and returns a <code>float</code> value. The value
     * returned is <pre><code>unpack('f', $data)[1]</code></pre>. This method is
     * suitable for reading bytes written by the <code>writeFloat</code> method
     * of interface <code>DataOutput</code>.
     *
     * @return float The <code>float</code> value read.
     * @throws IOException if an I/O error occurs.
     */
    public function readFloat();

    /**
     * Reads in a string that has been encoded using an UTF-8 format.
     *
     * @return string An ISO-8859-1 string.
     * @throws IOException if an I/O error occurs.
     */
    public function readUTF();
}
?>