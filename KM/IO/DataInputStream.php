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

use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\NullPointerException;

/**
 * A data input stream lets an application read primitive PHP data types from an
 * underlying input stream in a machine-independent way. An application uses a
 * data output stream to write data that can later be read by a data input
 * stream.
 *
 * @author Blair
 */
class DataInputStream extends FilterInputStream implements DataInput
{

    /**
     * Creates a DataInputStream that uses the specified underlying input
     * stream.
     *
     * @param InputStream $in
     */
    public function __construct(InputStream $in)
    {
        parent::__construct($in);
    }

    /**
     * Reads up to <code>len</code> bytes of data from the contained input
     * stream into an array of bytes. An attempt is made to read as many as
     * <code>len</code> bytes, but a smaller number may be read, possibly zero.
     * The number of bytes actually read is returned as an integer. <p> This
     * method blocks until input data is available, end of file is detected, or
     * an exception is thrown. <p> If <code>len</code> is zero, then no bytes
     * are read and <code>0</code> is returned; otherwise, there is an attempt
     * to read at least one byte. If no byte is available because the stream is
     * at end of file, the value <code>-1</code> is returned; otherwise, at
     * least one byte is read and stored into <code>b</code>. <p> The first byte
     * read is stored into element <code>b[off]</code>, the next one into
     * <code>b[off+1]</code>, and so on. The number of bytes read is, at most,
     * equal to <code>len</code>. Let <i>k</i> be the number of bytes actually
     * read; these bytes will be stored in elements <code>b[off]</code> through
     * <code>b[off+</code><i>k</i><code>-1]</code>, leaving elements
     * <code>b[off+</code><i>k</i><code>]</code> through
     * <code>b[off+len-1]</code> unaffected. <p> In every case, elements
     * <code>b[0]</code> through <code>b[off]</code> and elements
     * <code>b[off+len]</code> through <code>b[b.length-1]</code> are
     * unaffected.
     *
     * @param array $b The buffer into which the data is read.
     * @param int $off The start offset in the destination array <code>b</code>.
     * @param int $len The maximum number of bytes read.
     * @return int The total number of bytes read into the buffer, or
     *         <code>-1</code> if there is no more data because the end of the
     *         stream has been reached.
     * @throws NullPointerException if <code>b</code> is <code>null</code>.
     * @throws IndexOutOfBoundsException If <code>off</code> is negative,
     *         <code>len</code> is negative or greater than <code>b.length -
     *         off</code>.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\FilterInputStream::read()
     */
    public final function read(array &$b, $off = 0, $len = null)
    {
        if ($len === null) {
            $lem = count($b);
        }
        return $this->in->read($b, $off, $len);
    }

    /**
     * See the general contract of the <code>readFully</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @param array $b The buffer into which the data is read.
     * @param int $off The start offset of the data.
     * @param int $len The number of bytes to read.
     * @throws IndexOutOfBoundsException
     * @throws EOFException if this input stream reaches the end before reading
     *         all the bytes.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\DataInput::readFully()
     */
    public function readFully(array &$b, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($b);
        }
        if ($len < 0) {
            throw new IndexOutOfBoundsException();
        }
        $n = 0;
        while ($n < $len) {
            $count = $this->in->read($b, $off + $n, $len - $n);
            if ($count < 0) {
                throw new EOFException();
            }
            $n += $count;
        }
    }

    /**
     * See the general contract of the <code>skipBytes</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @param int $n The number of bytes to be skipped.
     * @return int The actual number of bytes skipped.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\DataInput::skipBytes()
     */
    public function skipBytes($n)
    {
        $total = 0;
        $cur = 0;
        while (($total < $n) && (($cur = $this->in->skip($n - $total)) > 0)) {
            $total += $cur;
        }
        return $total;
    }

    /**
     * See the general contract of the <code>readBoolean</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @return boolean The <code>boolean</code> value read.
     * @see \KM\IO\DataInput::readBoolean()
     */
    public final function readBoolean()
    {
        $ch = $this->in->readByte();
        if ($ch < 0) {
            throw new EOFException();
        }
        return ($ch != 0);
    }

    /**
     * See the general contract of the <code>readSingleByte</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @return int The next byte of this input.
     * @see \KM\IO\DataInput::readSingleByte()
     */
    public final function readSingleByte()
    {
        $ch = $this->in->readByte();
        if ($ch < 0) {
            throw new EOFException();
        }
        return $ch;
    }

    /**
     * See the general contract of the <code>readShort</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @return int The next two bytes of this input stream, interpreted as a
     *         signed 16-bit number with machine dependent byte order.
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\DataInput::readShort()
     */
    public function readShort()
    {
        $bin = [];
        for ($i = 0; $i < 2; $i ++) {
            $bin[$i] = $this->in->readByte();
        }
        
        $val = unpack('s', implode('', $bin));
        $int = (int) $val[1];
        return $int;
    }

    /**
     * See the general contract of the <code>readShort</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @return int The next two bytes of this input stream, interpreted as an
     *         unsigned 16-bit integer.
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\DataInput::readUnsignedShort()
     */
    public function readUnsignedShort()
    {
        $bin = [];
        for ($i = 0; $i < 2; $i ++) {
            $bin[$i] = $this->in->readByte();
        }
        
        $val = unpack('n', implode('', $bin));
        $int = (int) $val[1];
        return $int;
    }

    /**
     * See the general contract of the <code>readShort</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @return int The next four bytes of this input stream, interpreted as an
     *         unsigned integer.
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\DataInput::readInt()
     */
    public final function readInt()
    {
        $bin = [];
        for ($i = 0; $i < 4; $i ++) {
            $bin[$i] = $this->in->readByte();
        }
        if (($bin[0] | $bin[1] | $bin[2] | $bin[3]) < 0) {
            throw new EOFException();
        }

        $val = unpack('N', implode('', $bin));
        $int = (int) $val[1];
        return $int;
    }

    /**
     * See the general contract of the <code>readLong</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @return int The next eight bytes of this input stream, interpreted as an
     *         unsigned integer.
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\DataInput::readLong()
     * @see http://stackoverflow.com/questions/14405751/pack-and-unpack-64-bit-integer
     */
    public function readLong()
    {
        $bin = [];
        for ($i = 0; $i < 8; $i ++) {
            $bin[$i] = $this->in->readByte();
        }
        $tmp = unpack('N2', implode('', $bin));
        $long = $tmp[1] << 32 | $tmp[2];
        return $long;
    }

    /**
     * See the general contract of the <code>readFloat</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @return int The next four bytes of this input stream, interpreted as a
     *         float.
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\DataInput::readFloat()
     */
    public function readFloat()
    {
        $bin = [];
        for ($i = 0; $i < 4; $i ++) {
            $bin[$i] = $this->in->readByte();
        }
        if (($bin[0] | $bin[1] | $bin[2] | $bin[3]) < 0) {
            throw new EOFException();
        }
        
        $val = unpack('f', implode('', $bin));
        $float = (float) $val[1];
        return $float;
    }

    /**
     * See the general contract of the <code>readUTF</code> method of
     * <code>DataInput</code>. <p> Bytes for this operation are read from the
     * contained input stream.
     *
     * @return string An ISO-8859-1 string.
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     * @see \KM\IO\DataInput::readUTF()
     */
    public function readUTF()
    {
        return self::readUTFString($this);
    }

    /**
     * Reads from the stream <code>in</code> a representation of of a character
     * string encoded in ISO-8859-1 format.
     *
     * @param DataInput $in A data input stream.
     * @return string An ISO-8859-1 encoded string.
     * @throws EOFException if this input stream has reached the end.
     * @throws IOException if the first byte cannot be read for any reason other
     *         than the end of file, the stream has been closed and the
     *         underlying input stream does not support reading after close, or
     *         another I/O error occurs.
     */
    public static function readUTFString(DataInput $in)
    {
        $utflen = $in->readUnsignedShort();
        if ($utflen === 0) {
            return null;
        }
        $bytearr = array_fill(0, $utflen, null);
        $in->readFully($bytearr, 0, $utflen);
        
        $data = implode('', $bytearr);
        $fromEncoding = mb_detect_encoding($data);
        if ($fromEncoding === false) {
            throw new IOException('Stream encoding could not be detected');
        }
        return mb_convert_encoding($data, 'ISO-8859-1', $fromEncoding);
    }
}
?>