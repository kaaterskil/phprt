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
namespace KM\Util\Logging\FileHandler;

use KM\IO\OutputStream;

/**
 * A metered stream is a subclass of OutputStream that a) forwards all its output to a target
 * stream, and b) keeps track of how many bytes have been written.
 *
 * @package KM\Util\Logging\FileHandler
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class MeteredStream extends OutputStream {
	
	/**
	 * The underlying output stream.
	 * @var OutputStream
	 */
	public $out;
	
	/**
	 * The number of bytes written.
	 * @var int
	 */
	public $written;

	public function __construct(OutputStream $out, $written) {
		$this->out = $out;
		$this->written = (int) $written;
	}

	public function writeByte($b) {
		$this->out->writeByte( $b );
		$this->written++;
	}

	public function write(array &$b, $off = 0, $len = null) {
		if ($len == null) {
			$len = count( $b );
		}
		$this->out->write( $b, $off, $len );
		$this->written += (int) $len;
	}

	public function flush() {
		$this->out->flush();
	}

	public function close() {
		$this->out->close();
	}
}
?>