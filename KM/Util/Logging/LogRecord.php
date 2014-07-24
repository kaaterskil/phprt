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
 * LogRecord objects are used to pass logging requests between the logging framework and individual
 * log Handlers.
 * <p>
 * When a LogRecord is passed into the logging framework it logically belongs to the framework and
 * should no longer be used or updated by the client application.
 * <p>
 * Note that if the client application has not specified an explicit source method name and source
 * class name, then the LogRecord class will infer them automatically when they are first accessed
 * (due to a call on getSourceMethodName or getSourceClassName) by analyzing the call stack.
 * Therefore, if a logging Handler wants to pass off a LogRecord to another thread, or to transmit
 * it over RMI, and if it wishes to subsequently obtain method name or class name information it
 * should call one of getSourceClassName or getSourceMethodName to force the values to be filled in.
 * <p>
 * <b> Serialization notes:</b>
 * <ul>
 * <li>The LogRecord class is serializable.
 *
 * <li> Because objects in the parameters array may not be serializable,
 * during serialization all objects in the parameters array are
 * written as the corresponding Strings (using Object.toString).
 *
 * <li> The ResourceBundle is not transmitted as part of the serialized
 * form, but the resource bundle name is, and the recipient object's
 * readObject method will attempt to locate a suitable resource bundle.
 *
 * </ul>
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class LogRecord extends Object {
	private static $MIN_SEQUENTIAL_THREAD_ID;
	private static $globalSequenceNumber = 0;
	private static $nextThreadId;

	public static function clinit() {
		self::$MIN_SEQUENTIAL_THREAD_ID = (int) PHP_INT_MAX / 2;
		self::$nextThreadId = self::$MIN_SEQUENTIAL_THREAD_ID;
	}
	
	/**
	 * The logging message level.
	 * @var Level
	 */
	private $level;
	
	/**
	 * The sequence number of this message.
	 * @var int
	 */
	private $sequenceNumber;
	
	/**
	 * The name of the class that issued the logging call.
	 * @var string
	 */
	private $sourceClassName;
	
	/**
	 * The name of the method that issued the logging call.
	 * @var string
	 */
	private $sourceMethodName;
	
	/**
	 * The raw message to log.
	 * @var string
	 */
	private $message;
	
	/**
	 * The process ID for the process that issued the logging call.
	 * @var int
	 */
	private $threadId;
	
	/**
	 * The event UNIX time
	 * @var int
	 */
	private $millis;
	
	/**
	 * The exception associated with the log message, if any.
	 * @var \Exception
	 */
	private $thrown;
	
	/**
	 * The current logger name.
	 * @var string
	 */
	private $loggerName;
	
	/**
	 * Do we have to guess the logger class name?
	 * @var boolean
	 */
	private $needToInferCaller;
	
	/**
	 * The message parameters, if any.
	 * @var array
	 */
	private $parameters = array();

	/**
	 * Returns the default value for a new LogRecord's thread id.
	 * @return int
	 */
	private function defaultThreadId() {
		$tid = getmypid();
		if ($tid < self::$MIN_SEQUENTIAL_THREAD_ID) {
			return $tid;
		} else {
			return self::$nextThreadId++;
		}
	}

	/**
	 * Constructs a LogRecord with the given level and message values.
	 * The $sequence property will be initialized with a new unique value. These sequence values are
	 * allocated with increasing order.
	 * The $millis property will be initialized with the current time.
	 * The $threadId property will be initialized with a unique Id for the current thread.
	 * All other properties will be initialized to null;
	 * @param Level $level
	 * @param string $msg
	 */
	public function __construct(Level $level, $msg) {
		$this->level = $level;
		$this->message = (string) $msg;
		$this->sequenceNumber = self::$globalSequenceNumber++;
		$this->threadId = $this->defaultThreadId();
		$this->millis = microtime();
		$this->needToInferCaller = true;
	}

	/**
	 * Returns the source logger's name.
	 * @return string
	 */
	public function getLoggerName() {
		return $this->loggerName;
	}

	/**
	 * Sets the source logger's name.
	 * @param string $name
	 */
	public function setLoggerName($name) {
		$this->loggerName = (string) $name;
	}

	/**
	 * Returns the logging message level.
	 * @return Level
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * Sets the logging message level.
	 * @param Level $level
	 */
	public function setLevel(Level $level) {
		$this->level = $level;
	}

	/**
	 * Returns the sequence number.
	 * Sequence numbers are normally assigned in the LogRecord constructor, which assigns unique
	 * sequence numbers to each new LogRecord in increasing order.
	 * @return int
	 */
	public function getSequenceNumber() {
		return $this->sequenceNumber;
	}

	/**
	 * Sets the sequence number.
	 * Sequence numbers are normally assigned in the LogRecord constructor, so it should not
	 * normally be necessary to use this method.
	 * @param int $seq
	 */
	public function setSequenceNumber($seq) {
		$this->sequenceNumber = (int) $seq;
	}

	/**
	 * Returns the name of the class that (allegedly) issued the logging request.
	 * Note that this $sourceClassName is not verified and may be spoofed. This information may
	 * either have been provided as part of the logging call or it may have been inferred
	 * automatically by the logging framework. In the latter case, the information may only be
	 * approximate and may in fact describe an earlier call on the stack frame.
	 * May be null if no information could be obtained.
	 * @return string
	 */
	public function getSourceClassName() {
		if ($this->needToInferCaller) {
			$this->inferCaller();
		}
		return $this->sourceClassName;
	}

	/**
	 * Sets the name of the class that (allegedly) issued the logging request.
	 * @param string $sourceClassName
	 */
	public function setSourceClassName($sourceClassName) {
		$this->sourceClassName = (string) $sourceClassName;
		$this->needToInferCaller = false;
	}

	/**
	 * Returns the name of the method that (allegedly) issued the logging request.
	 * Note that this $sourceMethodName is not verified and may be spoofed. This information may
	 * either have been provide as part of the logging call or it may have been inferred
	 * automatically by the logging framework. In the latter case, the information may only be
	 * approximate and may in fact describe an earlier call on the stack frame.
	 * May be null if no information could be obtained.
	 * @return string
	 */
	public function getSourceMethodName() {
		if ($this->needToInferCaller) {
			$this->inferCaller();
		}
		return $this->sourceMethodName;
	}

	/**
	 * Sets the name of the method that (allegedly) issued the logging request.
	 * @param string $sourceMethodName
	 */
	public function setSourceMethodName($sourceMethodName) {
		$this->sourceMethodName = (string) $sourceMethodName;
		$this->needToInferCaller = false;
	}

	/**
	 * Returns the raw log message.
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Sets the raw log message.
	 * @param string $message
	 */
	public function setMessage($message) {
		$this->message = (string) $message;
	}

	/**
	 * Returns the parameters to the log message.
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Sets the parameters to the log message.
	 * @param array $parameters
	 */
	public function setParameters(array $parameters = array()) {
		$this->parameters = $parameters;
	}

	/**
	 * Returns an identifier for the thread where the message originated.
	 * @return int
	 */
	public function getThreadId() {
		return $this->threadId;
	}

	/**
	 * Sets an identifier for the thread where the message originated.
	 * @param int $threadId
	 */
	public function setThreadId($threadId) {
		$this->threadId = (int) $threadId;
	}

	/**
	 * Returns the event time in milliseconds.
	 * @return int
	 */
	public function getMillis() {
		return $this->millis;
	}

	/**
	 * Sets the event time.
	 * @param int $millis
	 */
	public function setMillis($millis) {
		$this->millis = (int) $millis;
	}

	/**
	 * Returns any exception associated with the log record.
	 * If the event involved an exception, this will be the exception object, otherwise null.
	 * @return \Exception
	 */
	public function getThrown() {
		return $this->thrown;
	}

	/**
	 * Sets an exception associated with the log event.
	 * @param \Exception $thrown
	 */
	public function setThrown(\Exception $thrown = null) {
		$this->thrown = $thrown;
	}

	/**
	 * Private method to infer the caller's class and method names.
	 */
	private function inferCaller() {
		$this->needToInferCaller = false;
		$trace = debug_backtrace();
		
		$looking = true;
		$depth = count( $trace );
		for($i = 0; $i < $depth; $i++) {
			$frame = $trace[$i];
			$cname = $frame['class'];
			$isLoggerImpl = $this->isLoggerImplFrame( $cname );
			if ($looking) {
				// Skip all frames until we have found the first logger frame
				if ($isLoggerImpl) {
					$looking = false;
				}
			} else {
				if (!$isLoggerImpl) {
					$this->setSourceClassName( $cname );
					$this->setSourceMethodName( $frame['function'] );
					return;
				}
			}
		}
		// We haven't found a suitable frame, so just punt. This is OK as we are only committed to
		// making a best effort here.
	}

	private function isLoggerImplFrame($cname) {
		return ($cname == 'KM\Util\Logging\Logger' ||
			 strpos( $cname, 'KM\Util\Logging\LoggingProxyImpl' ) === 0);
	}
}
?>