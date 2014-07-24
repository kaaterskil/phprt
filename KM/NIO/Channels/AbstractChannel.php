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
use KM\IO\IOException;

/**
 * Base implementation class for channels.
 *
 * @package KM\NIO\Channels
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class AbstractChannel extends Object implements Channel {
	
	/**
	 * The GC lock on this channel.
	 * @var Object
	 */
	private $closeLock;
	
	/**
	 * Tells whether this channel is open or not.
	 * @var boolean
	 */
	private $open = true;

	/**
	 * Initializes a new instance of this class.
	 */
	protected function __construct() {
		$this->closeLock = new Object();
	}

	/**
	 * Closes this channel.
	 *
	 * <p> If the channel has already been closed then this method returns immediately. Otherwise it
	 * marks the channel as closed and then invokes the <code>implCloseChannel</code> method in
	 * order to complete the close operation. </p>
	 * @throws IOException if an I/O error occurs.
	 * @see \KM\NIO\Channels\Channel::close()
	 */
	public function close() {
		if (!$this->open) {
			return;
		}
		$this->open = false;
		$this->implCloseChannel();
	}

	/**
	 * Closes this channel.
	 *
	 * <p> This method is invoked by the <code>close</code> method in order * to perform the actual
	 * work of closing the channel. This method is only invoked if the channel has not yet been
	 * closed, and it is never invoked more than once.
	 * @throws IOException if an I/O error occurs while closing the channel.
	 */
	protected abstract function implCloseChannel();

	public function isOpen() {
		return $this->open;
	}
}
?>