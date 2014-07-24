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

/**
 * WriteableByteChannel interface
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface WriteableByteChannel extends Channel {

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
	 */
	public function write(ByteBuffer $src);
}
?>