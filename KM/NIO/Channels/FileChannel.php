<?php

/**
 * Kaaterskil Library
 *
 * PHP version 5.5
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KAATERSKIL MANAGEMENT, LLC BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Kaaterskil
 * @copyright   Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version     SVN $Id$
 */
namespace KM\NIO\Channels;

use KM\Util\Set;
use Sun\NIO\FS\ChannelFactory;
use KM\Lang\IllegalArgumentException;
use KM\Lang\UnsupportedOperationException;
use KM\IO\IOException;
use KM\NIO\ByteBuffer;
use KM\IO\ByteArrayInputStream;

/**
 * A channel for reading, writing, mapping, and manipulating a file.
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class FileChannel extends AbstractChannel implements SeekableByteChannel {

	/**
	 * Initializes a new instance of this class.
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Opens or creates a file, returning a file channel to access the file.
	 *
	 * <p> The <code>options</code> parameter determines how the file is opened. The
	 * <code>StandardOpenOption#READ</code> and <code>StandardOpenOption#WRITE</code> options
	 * determine if the file should be opened for reading and/or writing. If neither option (or the
	 * <code>StandardOpenOption#APPEND</code> option) is contained in the array then the file is
	 * opened for reading. By default reading or writing commences at the beginning of the file.
	 *
	 * <p> In the addition to <code>READ</code> and <code>WRITE</code>, the following options may be
	 * present:
	 *
	 * <table border=1 cellpadding=5 summary="">
	 * <tr> <th>Option</th> <th>Description</th> </tr>
	 * <tr>
	 * <td> <code>StandardOpenOption#APPEND</code> </td>
	 * <td> If this option is present then the file is opened for writing and each invocation of the
	 * channel's <code>write</code> method first advances the position to the end of the file and
	 * then writes the requested data. This option may not be used in conjunction with the
	 * <code>READ</code> or <code>TRUNCATE_EXISTING</code> options. </td>
	 * </tr>
	 * <tr>
	 * <td> <code>StandardOpenOption#TRUNCATE_EXISTING</code> </td>
	 * <td> If this option is present then the existing file is truncated to a size of 0 bytes. This
	 * option is ignored when the file is opened only for reading. </td>
	 * </tr>
	 * <tr>
	 * <td> <code>StandardOpenOption#CREATE_NEW</code> </td>
	 * <td> If this option is present then a new file is created, failing if the file already
	 * exists. When creating a file the check for the existence of the file and the creation of the
	 * file if it does not exist is atomic with respect to other file system operations. This option
	 * is ignored when the file is opened only for reading. </td>
	 * </tr>
	 * <tr>
	 * <td > <code>StandardOpenOption#CREATE</code> </td>
	 * <td> If this option is present then an existing file is opened if it exists, otherwise a new
	 * file is created. When creating a file the check for the existence of the file and the
	 * creation of the file if it does not exist is atomic with respect to other file system
	 * operations. This option is ignored if the <code>CREATE_NEW</code> option is also present or
	 * the file is opened only for reading. </td>
	 * </tr>
	 * <tr>
	 * <td > <code>StandardOpenOption#DELETE_ON_CLOSE</code> </td>
	 * <td> When this option is present then the implementation makes a <em>best effort</em> attempt
	 * to delete the file when closed by the the <code>close</code> method.</td>
	 * </tr>
	 * </table>
	 *
	 * <p> An implementation may also support additional options.
	 *
	 * <p> The new channel is created by invoking the
	 * <code>Sun\NIO\FS\ChannelFactoryr#newFileChannel</code> method.
	 * @param string $path The path of the file to open or create.
	 * @param Set $options Options specifying how the file is opened.
	 * @return \KM\NIO\Channels\FileChannel A new file channel.
	 * @throws IllegalArgumentException if the set contains an invalid combination of options.
	 * @throws UnsupportedOperationException if an unsupported open option is specified.
	 * @throws IOException if an I/O error occurs.
	 */
	public static function openNewFileChannel($path, Set $options) {
		return ChannelFactory::newFileChannel( $path, $options );
	}
	
	/* ---------- Channel Operations ---------- */
	
	/**
	 * Reads a sequence of bytes from this channel into the given buffer.
	 *
	 * <p> An attempt is made to read up to <i>r</i> bytes from the channel, where <i>r</i> is the
	 * number of bytes remaining in the buffer, that is, <tt>dst.remaining()</tt>, at the moment
	 * this method is invoked.
	 *
	 * <p> Suppose that a byte sequence of length <i>n</i> is read, where
	 * <tt>0</tt>&nbsp;<tt>&lt;=</tt>&nbsp;<i>n</i>&nbsp;<tt>&lt;=</tt>&nbsp;<i>r</i>. This byte
	 * sequence will be transferred into the buffer so that the first byte in the sequence is at
	 * index <i>p</i> and the last byte is at index
	 * <i>p</i>&nbsp;<tt>+</tt>&nbsp;<i>n</i>&nbsp;<tt>-</tt>&nbsp;<tt>1</tt>, where <i>p</i> is the
	 * buffer's position at the moment this method is invoked. Upon return the buffer's position
	 * will be equal to <i>p</i>&nbsp;<tt>+</tt>&nbsp;<i>n</i>; its limit will not have changed.
	 *
	 * <p> A read operation might not fill the buffer, and in fact it might not read any bytes at
	 * all. Whether or not it does so depends upon the nature and state of the channel. A socket
	 * channel in non-blocking mode, for example, cannot read any more bytes than are immediately
	 * available from the socket's input buffer; similarly, a file channel cannot read any more
	 * bytes than remain in the file. It is guaranteed, however, that if a channel is in blocking
	 * mode and there is at least one byte remaining in the buffer then this method will block until
	 * at least one byte is read.
	 *
	 * <p> This method may be invoked at any time.</p>
	 * @param ByteBuffer $dst The buffer into which bytes are to be transferred.
	 * @return int The number of bytes read, possibly zero, or <tt>-1</tt> if the channel has
	 *         reached end-of-stream.array.
	 * @throws NonReadableChannelException if this channel was not opened for reading.
	 * @throws ClosedChannelException if this channel is closed.
	 * @see \KM\NIO\Channels\ReadableByteChannel::read()
	 */
	public abstract function read(ByteBuffer $dst);

	/**
	 * Writes a sequence of bytes to this channel from the given buffer.
	 *
	 * <p> An attempt is made to write up to <i>r</i> bytes to the channel, where <i>r</i> is the
	 * number of bytes remaining in the buffer, that is, <tt>src.remaining()</tt>, at the moment
	 * this method is invoked.
	 *
	 * <p> Suppose that a byte sequence of length <i>n</i> is written, where
	 * <tt>0</tt>&nbsp;<tt>&lt;=</tt>&nbsp;<i>n</i>&nbsp;<tt>&lt;=</tt>&nbsp;<i>r</i>. This byte
	 * sequence will be transferred from the buffer starting at index <i>p</i>, where <i>p</i> is
	 * the buffer's position at the moment this method is invoked; the index of the last byte
	 * written will be <i>p</i>&nbsp;<tt>+</tt>&nbsp;<i>n</i>&nbsp;<tt>-</tt>&nbsp;<tt>1</tt>. Upon
	 * return the buffer's position will be equal to <i>p</i>&nbsp;<tt>+</tt>&nbsp;<i>n</i>; its
	 * limit will not have changed.
	 *
	 * <p> Unless otherwise specified, a write operation will return only after writing all of the
	 * <i>r</i> requested bytes. Some types of channels, depending upon their state, may write only
	 * some of the bytes or possibly none at all. A socket channel in non-blocking mode, for
	 * example, cannot write any more bytes than are free in the socket's output buffer.
	 *
	 * <p> This method may be invoked at any time.</p>
	 * @param ByteBuffer $src The buffer from which bytes area to be retrieved.
	 * @return int The number of bytes written, possibly zero.
	 * @throws NonWriteableChannelException if this channel was not opened for writing.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 * @see \KM\NIO\Channels\WriteableByteChannel::write()
	 */
	public abstract function write(ByteBuffer $src);
	
	/* ---------- Other operations ---------- */
	
	/**
	 * Returns this channel's position.
	 * @return int This channel's position, a non-negative integer counting the number of bytes from
	 *         the beginning of the entity to the current position
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 * @see \KM\NIO\Channels\SeekableByteChannel::getPosition()
	 */
	public abstract function getPosition();

	/**
	 * Sets this channel's position.
	 *
	 * <p> Setting the position to a value that is greater than the current size is legal but does
	 * not change the size of the entity. A later attempt to read bytes at such a position will
	 * immediately return an end-of-file indication. A later attempt to write bytes at such a
	 * position will cause the entity to grow to accommodate the new bytes; the values of any bytes
	 * between the previous end-of-file and the newly-written bytes are unspecified.
	 * @param int $newPosition The new position, a non-negative integer counting the number of bytes
	 *        from the beginning of the entity
	 * @return \KM\NIO\Channels\SeekableByteChannel This channel.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IllegalArgumentException if the new position is
	 * @see \KM\NIO\Channels\SeekableByteChannel::setPosition()
	 */
	public abstract function setPosition($newPosition);

	/**
	 * Returns the current size of entity to which this channel is connected.
	 * @return int The current size, measured in bytes.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 * @see \KM\NIO\Channels\SeekableByteChannel::size()
	 */
	public abstract function size();

	/**
	 * Truncates the entity, to which this channel is connected, to the given size.
	 *
	 * <p> If the given size is less than the current size then the entity is truncated, discarding
	 * any bytes beyond the new end. If the given size is greater than or equal to the current size
	 * then the entity is not modified. In either case, if the current position is greater than the
	 * given size then it is set to that size.
	 * @param int $size The new size, a non-negative byte count.
	 * @return \KM\NIO\Channels\SeekableByteChannel This channel.
	 * @throws NonWriteableChannelException if this channel was not opened for writing.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IllegalArgumentException if the new size is negative.
	 * @throws IOException if some other I/O error occurs.
	 * @see \KM\NIO\Channels\SeekableByteChannel::truncate()
	 */
	public abstract function truncate($size);

	/**
	 * Reads a sequence of bytes from this channel into the given buffer, starting at the given file
	 * position.
	 *
	 * <p> This method works in the same manner as the <code>#read(ByteBuffer)</code> method, except
	 * that bytes are read starting at the given file position rather than at the channel's current
	 * position. This method does not modify this channel's position. If the given position is
	 * greater than the file's current size then no bytes are read. </p>
	 * @param ByteBuffer $src The buffer into which bytes are to be transferred.
	 * @param int $position The file position at which the transfer is to begin; must be
	 *        non-negative.
	 * @return int The number of bytes written, possibly zero, or <tt>-1</tt> if the given position
	 *         is greater than or equal to the file/s current size..
	 * @throws IllegalArgumentException if the position is negative.
	 * @throws NonReadableChannelException if this channel was not opened for reading.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 */
	public abstract function readFromPosition(ByteBuffer $dst, $position);

	/**
	 * Writes a sequence of bytes to this channel from the given buffer, starting at the given file
	 * position.
	 *
	 * <p> This method works in the same manner as the <code>#write(ByteBuffer)</code> method,
	 * except that bytes are written starting at the given file position rather than at the
	 * channel's current position. This method does not modify this channel's position. If the given
	 * position is greater than the file's current size then the file will be grown to accommodate
	 * the new bytes; the values of any bytes between the previous end-of-file and the newly-written
	 * bytes are unspecified. </p> The
	 * @param ByteBuffer $src The buffer from which bytes are to be transferred.
	 * @param int $position The file position at which the transfer is to begin; must be
	 *        non-negative.
	 * @return int The number of bytes written, possibly zero.
	 * @throws IllegalArgumentException if the position is negative.
	 * @throws NonWriteableChannelException if this channel was not opened for writing.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 */
	public abstract function writeFromPosition(ByteBuffer $src, $position);
	
	/* ---------- Locks ---------- */

	/**
	 * Acquires an exclusive lock on this channel's file.
	 * @return \KM\NIO\Channels\FileLock A lock objecT representing the newly-acquired lock.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws NonWriteableChannelException if this channel was not opened for writing.
	 * @throws IOException if some other I/O error occurs.
	 */
	public abstract function lock();

	/**
	 * Attempts to acquire an exclusive lock on this channel's file.
	 * @return \KM\NIO\Channels\FileLock A lock objecT representing the newly-acquired lock, or
	 *         <tt>null</tt> if the lock could not be acquired.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 */
	public abstract function tryLock();
}
?>