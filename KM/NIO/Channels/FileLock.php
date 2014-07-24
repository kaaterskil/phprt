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

use KM\Lang\Object;
use KM\IO\Closeable;
use KM\IO\IOException;

/**
 * A token representing a lock on a file.
 *
 * <p> A file-lock object is initially valid. It remains valid until the lock is released by
 * invoking the <tt>release</tt> method, by closing the channel that was used to acquire it, or by
 * the termination of the PHP session, whichever comes first. The validity of a lock may be tested
 * by invoking its <tt>isValid</tt> method.
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class FileLock extends Object implements Closeable {
	
	/**
	 * The channel to which this lock is attached.
	 * @var FileChannel
	 */
	private $channel;

	/**
	 * Initializes a new instance of this class.
	 * @param FileChannel $channel The channel upon whose file this lock is held
	 */
	protected function __construct(FileChannel $channel) {
		$this->channel = $channel;
	}

	public function __destruct() {
		$this->close();
	}

	/**
	 * Returns the file channel upon whose file this lock was acquired.
	 * @return \KM\NIO\Channels\FileChannel The file channel, or <tt>null</tt> if the file lock was
	 *         not acquired by a file channel.
	 */
	public function getChannel() {
		return $this->channel;
	}

	/**
	 * Returns the channel upon which whose file file this lock was acquired.
	 * @return \KM\NIO\Channels\FileChannel The channel upon whose file this lock was acquired.
	 */
	public function acquiredBy() {
		return $this->channel;
	}

	/**
	 * Tells whether or not this lock is valid.
	 * A lock object remains valid until it is released or the associated file channel is closed,
	 * whichever comes first.
	 * @return boolean <tt>true</tt> if, and only if, this lock is valid.
	 */
	public abstract function isValid();

	/**
	 * Releases this lock.
	 * If this lock object is valid then invoking this method releases the lock and renders the
	 * object invalid. If this lock object is invalid, then invoking this method has no effect.
	 * @throws ClosedChannelException if the channel that was used to acquire this lock is no longer
	 *         open.
	 * @throws IOException is an I/O error occurs.
	 */
	public abstract function release();

	/**
	 * This method invokes the <tt>release()</tt> method.
	 *
	 * @see \KM\IO\Closeable::close()
	 */
	public function close() {
		$this->release();
	}

	/**
	 * Returns a string describing the validity of this lock.
	 * @return string
	 * @see \KM\Lang\Object::__toString()
	 */
	public function __toString() {
		return ($this->getClass()->getName() . '[' . ($this->isValid() ? 'valid' : 'invalid') . ']');
	}
}
?>