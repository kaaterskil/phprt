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

use KM\IO\IOException;
use KM\Lang\IllegalArgumentException;

/**
 * A byte channel that maintains a current <i>position</i> and allows the position to be changed.
 *
 * <p> A seekable byte channel is connected to an entity, typically a file, that contains a
 * variable-length sequence of bytes that can be read and written. The current position can be
 * <i>queried</i> and <i>modified</i>. The channel also provides access to the current <i>size</i>
 * of the entity to which the channel is connected. The size increases when bytes are written beyond
 * its current size; the size decreases when it is <i>truncated</i>.
 *
 * <p> The <tt>position</tt> and <tt>truncate</tt> methods which do not otherwise have a value to
 * return are specified to return the channel upon which they are invoked. This allows method
 * invocations to be chained. Implementations of this interface should specialize the return type so
 * that method invocations on the implementation class can be chained.
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface SeekableByteChannel extends ByteChannel {

	/**
	 * Returns this channel's position.
	 * @return int This channel's position, a non-negative integer counting the number of bytes from
	 *         the beginning of the entity to the current position
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 */
	public function getPosition();

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
	 * @throws IllegalArgumentException if the new position is negative.
	 * @throws IOException if some other I/O error occurs.
	 */
	public function setPosition($newPosition);

	/**
	 * Returns the current size of entity to which this channel is connected.
	 * @return int The current size, measured in bytes.
	 * @throws ClosedChannelException if this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 */
	public function size();

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
	 */
	public function truncate($size);
}
?>