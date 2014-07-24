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
namespace KM\Lang;

/**
 * Error handler that transforms PHP errors into exceptions.
 *
 * @package KM\Lang
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Error extends \ErrorException {

	/**
	 * Registers the handler.
	 */
	public static function registerErrorHandler() {
		set_error_handler('\KM\Lang\Error::errorHandler');
	}

	public static function errorHandler($errNo, $errStr, $errFile, $errLine) {
		throw new self($errStr, 0, $errNo, $errFile, $errLine);
	}

	/**
	 * Utility constructor
	 */
	public function __construct($message, $code, $severity, $filename, $lineNumber) {
		$this->message = $message;
		$this->code = $code;
		$this->file = $filename;
		$this->line = $lineNumber;
		$this->severity = $severity;
	}
}
?>