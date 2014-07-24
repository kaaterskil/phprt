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
namespace KM\Util\Logging;

use KM\Lang\Object;

/**
 * ErrorManager objects can be attached to Handlers to process any error that occurs on a Handler
 * during Logging.
 * <p>
 * When processing logging output, if a Handler encounters problems then rather than throwing an
 * Exception back to the issuer of the logging call (who is unlikely to be interested) the Handler
 * should call its associated ErrorManager.
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ErrorManager extends Object {
	const GENERIC_FAILURE = 0;
	const WRITE_FAILURE = 1;
	const FLUSH_FAILURE = 2;
	const CLOSE_FAILURE = 3;
	const OPEN_FAILURE = 4;
	const FORMAT_FAILURE = 5;
	private $reported = false;

	/**
	 * The error method is called when a Handler failure occurs.
	 * This method may be overridden in subclasses. The default behavior in this base class is that
	 * the first call is echoed and subsequent calls are ignored.
	 * @param string $msg A descriptive string (may be null)
	 * @param \Exception $ex An exception (may be null)
	 * @param int $code The error code defined in ErrorManager
	 */
	public function error($msg,\Exception $ex, $code) {
		if ($this->reported) {
			// We only report the first error to avoid clogging the screen.
			return;
		}
		$this->reported = true;
		$text = 'KM\Util\Logging\ErrorManager: ' . $code;
		if (!empty( $msg )) {
			$text .= ': ' . $msg;
		}
		trigger_error( $text );
		if ($ex != null) {
			echo $ex->getTraceAsString();
		}
	}
}
?>