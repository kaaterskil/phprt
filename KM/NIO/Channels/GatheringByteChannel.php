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

use KM\Lang\IndexOutOfBoundsException;
use KM\IO\IOException;

/**
 * A channel that can write bytes from a sequence of buffers.
 *
 * <p> A <i>gathering</i> write operation writes, in a single invocation, a sequence of bytes from
 * one or more of a given sequence of buffers. Gathering writes are often useful when implementing
 * network protocols or file formats that, for example, group data into segments consisting of one
 * or more fixed-length headers followed by a variable-length body. Similar <i>scattering</i> read
 * operations are defined in the <code>ScatteringByteChannel</code> interface. </p>
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface GatheringByteChannel extends WriteableByteChannel {

	/**
	 * Writes a sequence of bytes to this channel from a subsequence of the given buffers.
	 *
	 * <p> An attempt is made to write up to <i>r</i> bytes to this channel, where <i>r</i> is the
	 * total number of bytes remaining in the specified subsequence of the given buffer array, that
	 * is,
	 *
	 * <blockquote><pre>
	 * srcs[offset].remaining()
	 * + srcs[offset+1].remaining()
	 * + ... + srcs[offset+length-1].remaining()</pre></blockquote>
	 *
	 * at the moment that this method is invoked.
	 *
	 * <p> Suppose that a byte sequence of length <i>n</i> is written, where
	 * <tt>0</tt>&nbsp;<tt>&lt;=</tt>&nbsp;<i>n</i>&nbsp;<tt>&lt;=</tt>&nbsp;<i>r</i>. Up to the
	 * first <tt>srcs[offset].remaining()</tt> bytes of this sequence are written from buffer
	 * <tt>srcs[offset]</tt>, up to the next <tt>srcs[offset+1].remaining()</tt> bytes are written
	 * from buffer <tt>srcs[offset+1]</tt>, and so forth, until the entire byte sequence is written.
	 * As many bytes as possible are written from each buffer, hence the final position of each
	 * updated buffer, except the last updated buffer, is guaranteed to be equal to that buffer's
	 * limit.
	 *
	 * <p> Unless otherwise specified, a write operation will return only after writing all of the
	 * <i>r</i> requested bytes. Some types of channels, depending upon their state, may write only
	 * some of the bytes or possibly none at all. A socket channel in non-blocking mode, for
	 * example, cannot write any more bytes than are free in the socket's output buffer.
	 *
	 * <p> This method may be invoked at any time.</p>
	 * @param array $srcs The buffers from which bytes are to be retrieved.
	 * @param int $offset The offset within the buffer array of the first buffer from which bytes
	 *        are to be retrieved; must be non-negative and no larger than <tt>srcs.length</tt>
	 * @param int $length The maximum number of buffers to be accessed; must be non-negative and no
	 *        larger than <tt>srcs.length</tt>&nbsp;-&nbsp;<tt>offset</tt>
	 * @return int The number of bytes written, possibly zero.
	 * @throws IndexOutOfBoundsException If the preconditions on the <tt>offset</tt> and
	 *         <tt>length</tt> parameters do not hold
	 * @throws NonWriteableChannelException If this channel was not opened for writing.
	 * @throws ClosedChannelException If this channel is closed.
	 * @throws IOException if some other I/O error occurs.
	 */
	public function write(array &$srcs, $offset = 0, $length = null);
}
?>