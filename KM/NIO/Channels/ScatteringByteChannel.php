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

/**
 * A channel that can read bytes into a sequence of buffers.
 *
 * <p> A <i>scattering</i> read operation reads, in a single invocation, a sequence of bytes into
 * one or more of a given sequence of buffers. Scattering reads are often useful when implementing
 * network protocols or file formats that, for example, group data into segments consisting of one
 * or more fixed-length headers followed by a variable-length body. Similar <i>gathering</i> write
 * operations are defined in the <code>GatheringByteChannel</code> interface. </p>
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface ScatteringByteChannel extends ReadableByteChannel {

	/**
	 * Reads a sequence of bytes from this channel into a subsequence of the
	 * given buffers.
	 *
	 * <p> An invocation of this method attempts to read up to <i>r</i> bytes
	 * from this channel, where <i>r</i> is the total number of bytes remaining
	 * the specified subsequence of the given buffer array, that is,
	 *
	 * <blockquote><pre>
	 * dsts[offset].remaining()
	 * + dsts[offset+1].remaining()
	 * + ... + dsts[offset+length-1].remaining()</pre></blockquote>
	 *
	 * at the moment that this method is invoked.
	 *
	 * <p> Suppose that a byte sequence of length <i>n</i> is read, where
	 * <tt>0</tt>&nbsp;<tt>&lt;=</tt>&nbsp;<i>n</i>&nbsp;<tt>&lt;=</tt>&nbsp;<i>r</i>.
	 * Up to the first <tt>dsts[offset].remaining()</tt> bytes of this sequence
	 * are transferred into buffer <tt>dsts[offset]</tt>, up to the next
	 * <tt>dsts[offset+1].remaining()</tt> bytes are transferred into buffer
	 * <tt>dsts[offset+1]</tt>, and so forth, until the entire byte sequence
	 * is transferred into the given buffers. As many bytes as possible are
	 * transferred into each buffer, hence the final position of each updated
	 * buffer, except the last updated buffer, is guaranteed to be equal to
	 * that buffer's limit.
	 *
	 * <p> This method may be invoked at any time. If another thread has
	 * already initiated a read operation upon this channel, however, then an
	 * invocation of this method will block until the first operation is
	 * complete. </p>
	 * @param array $dsts
	 * @param int $offset The offset within the buffer array of the first buffer into which bytes
	 *        are to be transferred; must be non-negative and no larger than
	 *        <tt>dsts.length</tt>
	 * @param int $length The maximum number of buffers to be accessed; must be non-negative and no
	 *        larger than <tt>dsts.length</tt>&nbsp;-&nbsp;<tt>offset</tt>
	 * @return int The number of bytes read, possibly zero.
	 * @throws IndexOutOfBoundsException If the preconditions on the <tt>offset</tt> and
	 *         <tt>length</tt> parameters do not hold
	 * @throws NonReadableChannelException If this channel was not opened for reading.
	 * @throws ClosedChannelException If this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 */
	public function read(array &$dsts, $offset = 0, $length = null);
}
?>