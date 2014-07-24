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

use KM\IO\Closeable;
use KM\IO\IOException;

/**
 * A nexus for I/O operations.
 *
 * <p> A channel represents an open connection to an entity such as a hardware device, a file, a
 * network socket, or a program component that is capable of performing one or more distinct I/O
 * operations, for example reading or writing.
 *
 * <p> A channel is either open or closed. A channel is open upon creation, and once closed it
 * remains closed. Once a channel is closed, any attempt to invoke an I/O operation upon it will
 * cause a <code>ClosedChannelException</code> to be thrown. Whether or not a channel is open may be
 * tested by invoking its <code>isOpen</code> method.
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface Channel extends Closeable {

	/**
	 * Tells whether or not this channel is open.
	 * @return boolean <tt>true</tt> if, and only if, this channel is open
	 */
	public function isOpen();

	/**
	 * Closes this channel.
	 *
	 * <p> After a channel is closed, any further attempt to invoke I/O operations upon it will
	 * cause a <code>ClosedChannelException</code> to be thrown.
	 *
	 * <p> If this channel is already closed then invoking this method has no effect.
	 *
	 * <p> This method may be invoked at any time.
	 * @throws IOException if an I/O error occurs.
	 * @see \KM\IO\Closeable::close()
	 */
	public function close();
}
?>