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
namespace KM\Util\Logging\LogManager;

use KM\Util\Logging\Logger;

/**
 * SystemLoggerContext Class
 *
 * @package KM\Util\Logging\LogManager
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class SystemLoggerContext extends LoggerContext {

	/**
	 * Add a system logger in the system context's namespace as well as in the LogManager's
	 * namespace if not exist so that there is only one single logger of the given name.
	 * System loggers are visible to applications unless a logger of the same name has been added.
	 * @param string $name
	 * @return \KM\Util\Logging\Logger
	 * @see \KM\Util\Logging\LogManager\LoggerContext::demandLogger()
	 */
	public function demandLogger($name) {
		$result = $this->findLogger( $name );
		if ($result == null) {
			// Only allocate the new system logger once.
			$newLogger = new Logger( $name, $this->getOwner() );
			do {
				if ($this->addLocalLogger( $newLogger )) {
					// We successfully added the new Logger that we created above so return it
					// without re-fetching.
					$result = $newLogger;
				} else {
					// We didn't add the new Logger that we created above because another thread
					// added a Logger with the same name after our null check above and before our
					// call to addLogger(). We have to re-fetch the Logger because addLogger()
					// returns a boolean instead of the Logger reference itself. However, if the
					// thread that created the other Logger is not holding a strong reference to the
					// other Logger, then it is possible for the other Logger to be GC'ed after we
					// saw it in addLogger() and before we can re-fetch it. If it has been GC'ed
					// then we'll just loop around and try again.
					$result = $this->findLogger( $name );
				}
			} while ( $result == null );
		}
		return $result;
	}
}
?>