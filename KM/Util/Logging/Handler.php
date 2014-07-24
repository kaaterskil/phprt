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
use KM\Util\Logging\Filter;
use KM\Util\Logging\Formatter;

/**
 * A <tt>Handler</tt> object takes log messages from a <tt>Logger</tt> and exports them.
 * It might for example, write them to a console or write them to a file, or send them to a network
 * logging service, or forward them to an OS log, or whatever.
 * <p>
 * A <tt>Handler</tt> can be disabled by doing a <tt>setLevel(Level.OFF)</tt> and can be re-enabled
 * by doing a <tt>setLevel</tt> with an appropriate level.
 * <p>
 * <tt>Handler</tt> classes typically use <tt>LogManager</tt> properties to set default values for
 * the <tt>Handler</tt>'s <tt>Filter</tt>, <tt>Formatter</tt>, and <tt>Level</tt>. See the specific
 * documentation for each concrete <tt>Handler</tt> class.
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class Handler extends Object {
	/**
	 * The log message level value for OFF.
	 * @var int
	 */
	private static $offValue;

	/**
	 * Static constructor
	 */
	public static function clinit() {
		self::$offValue = Level::$OFF->intValue();
	}
	
	/**
	 * The log manager.
	 * @var LogManager
	 */
	private $manager;
	
	/**
	 * The log filter.
	 * @var Filter
	 */
	private $filter;
	
	/**
	 * The log formatter.
	 * @var Formatter
	 */
	private $formatter;
	
	/**
	 * The log message level.
	 * @var Level
	 */
	private $logLevel;
	
	/**
	 * This Handler's error manager.
	 * @var ErrorManager
	 */
	private $errorManager;
	
	/**
	 * The message encoding.
	 * @var string
	 */
	private $encoding;
	
	/**
	 * Support for security checking.
	 * When $sealed is true, we access check updates to the class.
	 * @var boolean
	 */
	protected $sealed = true;

	/**
	 * Default constructor.
	 */
	protected function __construct() {
		$this->manager = LogManager::getLogManager();
		$this->logLevel = Level::$ALL;
		$this->errorManager = new ErrorManager();
	}

	/**
	 * Publishes a LogRecord.
	 * The logging request was made initially to a Logger object which initialized the LogRecord and
	 * forwarded it here. The Handler is responsible for formatting the message when and if
	 * necessary. The formatting should include localization.
	 * @param LogRecord $record Description of the log event. A null record is silently ignored and
	 *        is not published.
	 */
	public abstract function publish(LogRecord $record);

	/**
	 * Flushes any buffered output.
	 */
	public abstract function flush();

	/**
	 * Closes the Handler and frees any associated resources.
	 * The close method will perform a flush and then close the Handler. After close has been called
	 * this Handler should no longer be used. Method calls may either be silently ignored or may
	 * throw runtime exceptions.
	 */
	public abstract function close();

	/**
	 * Sets a Formatter.
	 * This Formatter will be used to format LogRecords for this Handler. Some Handlers May not use
	 * Formatters, in which case the Formatter will be remembered but not used.
	 * @param Formatter $newFormatter
	 */
	public function setFormatter(Formatter $newFormatter) {
		$this->formatter = $newFormatter;
	}

	/**
	 * Returns the Formatter for this Handler.
	 * @return \KM\Util\Logging\Formatter
	 */
	public function getFormatter() {
		return $this->formatter;
	}

	/**
	 * Sets the character encoding used by this Handler.
	 * @param string $encoding
	 */
	public function setEncoding($encoding) {
		$this->encoding = (string) $encoding;
	}

	/**
	 * Returns the character encoding used by this Handler.
	 * @return string
	 */
	public function getEncoding() {
		return $this->encoding;
	}

	/**
	 * Sets a Filter to control output on this Handler.
	 * For each call of publish() the Handler will call this Filter (if it is non-null) to check if
	 * the LogRecord should be published or discarded.
	 * @param Filter $newFilter
	 */
	public function setFilter(Filter $newFilter = null) {
		$this->filter = $newFilter;
	}

	/**
	 * Returns the current Filter for this Handler.
	 * @return \KM\Util\Logging\Filter
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * Defines an ErrorManager for this Handler.
	 * @param ErrorManager $em
	 */
	public function setErrorManager(ErrorManager $em) {
		$this->errorManager = $em;
	}

	/**
	 * Returns the ErrorManager for this Handler.
	 * @return \KM\Util\Logging\ErrorManager
	 */
	public function getErrorManager() {
		return $this->errorManager;
	}

	/**
	 * Protected convenience method to report an error to this Handler's ErrorManager.
	 * @param string $msg
	 * @param \Exception $e
	 * @param int $code
	 */
	protected function reportError($msg,\Exception $e, $code) {
		try {
			$this->errorManager->error( $msg, $e, $code );
		} catch ( \Exception $ex ) {
			$msg = 'Handler.reportError caught: ' . $ex->getTraceAsString();
			trigger_error($msg);
		}
	}

	/**
	 * Sets the log level specifying which message levels will be logged by this Handler.
	 * Message levels lower than this value will be discarded. The intention is to allow developers
	 * to turn on voluminous logging but to limit the messages that are sent to certain handlers.
	 * @param Level $newLevel
	 */
	public function setLevel(Level $newLevel) {
		$this->logLevel = $newLevel;
	}

	/**
	 * Returns the log level specifying which messages will be logged by this Handler.
	 * Message levels lower than this level will be discarded.
	 * @return \KM\Util\Logging\Level
	 */
	public function getLevel() {
		return $this->logLevel;
	}

	/**
	 * Check if this Handler would actually log a given LogRecord.
	 * This method checks if the LogRecord has an appropriate Level and whether it satisfied any
	 * Filter. It also may make other Handler-specific checks that might prevent a handler from
	 * logging the LogRecord. It will return false if the LogRecord is null.
	 * @param LogRecord $record
	 * @return boolean True if the LogRecord would be logged.
	 */
	public function isLoggable(LogRecord $record) {
		$levelValue = $this->getLevel()->intValue();
		if ($record->getLevel()->intValue() < $levelValue || $levelValue == self::$offValue) {
			return false;
		}
		$filter = $this->getFilter();
		if ($filter == null) {
			return true;
		}
		return $filter->isLoggable( $record );
	}
}
?>