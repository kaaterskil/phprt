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

use KM\NIO\ByteBuffer;
use KM\IO\IOException;
use KM\IO\InputStream;

/**
 * A channel that can read bytes.
 *
 * <p> Only one read operation upon a readable channel may be in progress at any given time. If one
 * thread initiates a read operation upon a channel then any other thread that attempts to initiate
 * another read operation will block until the first operation is complete. Whether or not other
 * kinds of I/O operations may proceed concurrently with a read operation depends upon the type of
 * the channel. </p>
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface ReadableByteChannel {

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
	 * @throws IOException if some other I/O error occurs.
	 */
	public function read(ByteBuffer $dst);
}
?>