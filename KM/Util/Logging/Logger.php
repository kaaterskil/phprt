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
use KM\Util\ArrayList;
use KM\Util\Iterator;
use KM\Util\Logging\Filter;
use KM\Util\Logging\Logger;
use KM\Lang\NullPointerException;

/**
 * A Logger object is used to log messages for a specific
 * system or application component.  Loggers are normally named,
 * using a hierarchical dot-separated namespace.  Logger names
 * can be arbitrary strings, but they should normally be based on
 * the package name or class name of the logged component, such
 * as java.net or javax.swing.  In addition it is possible to create
 * "anonymous" Loggers that are not stored in the Logger namespace.
 * <p>
 * Logger objects may be obtained by calls on one of the getLogger
 * factory methods.  These will either create a new Logger or
 * return a suitable existing Logger. It is important to note that
 * the Logger returned by one of the {@code getLogger} factory methods
 * may be garbage collected at any time if a strong reference to the
 * Logger is not kept.
 * <p>
 * Logging messages will be forwarded to registered Handler
 * objects, which can forward the messages to a variety of
 * destinations, including consoles, files, OS logs, etc.
 * <p>
 * Each Logger keeps track of a "parent" Logger, which is its
 * nearest existing ancestor in the Logger namespace.
 * <p>
 * Each Logger has a "Level" associated with it.  This reflects
 * a minimum Level that this logger cares about.  If a Logger's
 * level is set to <tt>null</tt>, then its effective level is inherited
 * from its parent, which may in turn obtain it recursively from its
 * parent, and so on up the tree.
 * <p>
 * The log level can be configured based on the properties from the
 * logging configuration file, as described in the description
 * of the LogManager class.  However it may also be dynamically changed
 * by calls on the Logger.setLevel method.  If a logger's level is
 * changed the change may also affect child loggers, since any child
 * logger that has <tt>null</tt> as its level will inherit its
 * effective level from its parent.
 * <p>
 * On each logging call the Logger initially performs a cheap
 * check of the request level (e.g., SEVERE or FINE) against the
 * effective log level of the logger.  If the request level is
 * lower than the log level, the logging call returns immediately.
 * <p>
 * After passing this initial (cheap) test, the Logger will allocate
 * a LogRecord to describe the logging message.  It will then call a
 * Filter (if present) to do a more detailed check on whether the
 * record should be published.  If that passes it will then publish
 * the LogRecord to its output Handlers.  By default, loggers also
 * publish to their parent's Handlers, recursively up the tree.
 * <p>
 * Each Logger may have a {@code ResourceBundle} associated with it.
 * The {@code ResourceBundle} may be specified by name, using the
 * {@link #getLogger(java.lang.String, java.lang.String)} factory
 * method, or by value - using the {@link
 * #setResourceBundle(java.util.ResourceBundle) setResourceBundle} method.
 * This bundle will be used for localizing logging messages.
 * If a Logger does not have its own {@code ResourceBundle} or resource bundle
 * name, then it will inherit the {@code ResourceBundle} or resource bundle name
 * from its parent, recursively up the tree.
 * <p>
 * Most of the logger output methods take a "msg" argument.  This
 * msg argument may be either a raw value or a localization key.
 * During formatting, if the logger has (or inherits) a localization
 * {@code ResourceBundle} and if the {@code ResourceBundle} has a mapping for
 * the msg string, then the msg string is replaced by the localized value.
 * Otherwise the original msg string is used.  Typically, formatters use
 * java.text.MessageFormat style formatting to format parameters, so
 * for example a format string "{0} {1}" would format two parameters
 * as strings.
 * <p>
 * A set of methods alternatively take a "msgSupplier" instead of a "msg"
 * argument.  These methods take a {@link Supplier}{@code <String>} function
 * which is invoked to construct the desired log message only when the message
 * actually is to be logged based on the effective log level thus eliminating
 * unnecessary message construction. For example, if the developer wants to
 * log system health status for diagnosis, with the String-accepting version,
 * the code would look like:
 <pre><code>

   class DiagnosisMessages {
     static String systemHealthStatus() {
       // collect system health information
       ...
     }
   }
   ...
   logger.log(Level.FINER, DiagnosisMessages.systemHealthStatus());
</code></pre>
 * With the above code, the health status is collected unnecessarily even when
 * the log level FINER is disabled. With the Supplier-accepting version as
 * below, the status will only be collected when the log level FINER is
 * enabled.
 <pre><code>

   logger.log(Level.FINER, DiagnosisMessages::systemHealthStatus);
</code></pre>
 * <p>
 * When looking for a {@code ResourceBundle}, the logger will first look at
 * whether a bundle was specified using {@link
 * #setResourceBundle(java.util.ResourceBundle) setResourceBundle}, and then
 * only whether a resource bundle name was specified through the {@link
 * #getLogger(java.lang.String, java.lang.String) getLogger} factory method.
 * If no {@code ResourceBundle} or no resource bundle name is found,
 * then it will use the nearest {@code ResourceBundle} or resource bundle
 * name inherited from its parent tree.<br>
 * When a {@code ResourceBundle} was inherited or specified through the
 * {@link
 * #setResourceBundle(java.util.ResourceBundle) setResourceBundle} method, then
 * that {@code ResourceBundle} will be used. Otherwise if the logger only
 * has or inherited a resource bundle name, then that resource bundle name
 * will be mapped to a {@code ResourceBundle} object, using the default Locale
 * at the time of logging.
 * <br id="ResourceBundleMapping">When mapping resource bundle names to
 * {@code ResourceBundle} objects, the logger will first try to use the
 * Thread's {@linkplain java.lang.Thread#getContextClassLoader() context class
 * loader} to map the given resource bundle name to a {@code ResourceBundle}.
 * If the thread context class loader is {@code null}, it will try the
 * {@linkplain java.lang.ClassLoader#getSystemClassLoader() system class loader}
 * instead.  If the {@code ResourceBundle} is still not found, it will use the
 * class loader of the first caller of the {@link
 * #getLogger(java.lang.String, java.lang.String) getLogger} factory method.
 * <p>
 * Formatting (including localization) is the responsibility of
 * the output Handler, which will typically call a Formatter.
 * <p>
 * Note that formatting need not occur synchronously.  It may be delayed
 * until a LogRecord is actually written to an external sink.
 * <p>
 * The logging methods are grouped in five main categories:
 * <ul>
 * <li><p>
 *     There are a set of "log" methods that take a log level, a message
 *     string, and optionally some parameters to the message string.
 * <li><p>
 *     There are a set of "logp" methods (for "log precise") that are
 *     like the "log" methods, but also take an explicit source class name
 *     and method name.
 * <li><p>
 *     There are a set of "logrb" method (for "log with resource bundle")
 *     that are like the "logp" method, but also take an explicit resource
 *     bundle object for use in localizing the log message.
 * <li><p>
 *     There are convenience methods for tracing method entries (the
 *     "entering" methods), method returns (the "exiting" methods) and
 *     throwing exceptions (the "throwing" methods).
 * <li><p>
 *     Finally, there are a set of convenience methods for use in the
 *     very simplest cases, when a developer simply wants to log a
 *     simple string at a given log level.  These methods are named
 *     after the standard Level names ("severe", "warning", "info", etc.)
 *     and take a single argument, a message string.
 * </ul>
 * <p>
 * For the methods that do not take an explicit source name and
 * method name, the Logging framework will make a "best effort"
 * to determine which class and method called into the logging method.
 * However, it is important to realize that this automatically inferred
 * information may only be approximate (or may even be quite wrong!).
 * Virtual machines are allowed to do extensive optimizations when
 * JITing and may entirely remove stack frames, making it impossible
 * to reliably locate the calling class and method.
 * <P>
 * All methods on Logger are multi-thread safe.
 * <p>
 * <b>Subclassing Information:</b> Note that a LogManager class may
 * provide its own implementation of named Loggers for any point in
 * the namespace.  Therefore, any subclasses of Logger (unless they
 * are implemented in conjunction with a new LogManager class) should
 * take care to obtain a Logger instance from the LogManager class and
 * should delegate operations such as "isLoggable" and "log(LogRecord)"
 * to that instance.  Note that in order to intercept all logging
 * output, subclasses need only override the log(LogRecord) method.
 * All the other logging methods are implemented as calls on this
 * log(LogRecord) method.
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Logger extends Object {
	const GLOBAL_LOGGER_NAME = 'global';
	private static $emptyHandlers = array();
	private static $offValue;

	public static function clinit() {
		self::$offValue = Level::$OFF->intValue();
	}
	
	/**
	 * The current LogManager.
	 * @var LogManager
	 */
	protected $manager;
	
	/**
	 * This Logger's name.
	 * @var string
	 */
	private $name;
	
	/**
	 * The list of Handlers.
	 * @var ArrayList
	 */
	private $handlers;
	
	/**
	 * Flag to indicate if this logger should delegate to its parent's handlers.
	 * @var boolean
	 */
	private $useParentHandlers = true;
	
	/**
	 * The logger Filter, is any,
	 * @var Filter
	 */
	private $filter;
	
	/**
	 * Flag to indicate if the Logger is anonymous.
	 * @var boolean
	 */
	private $anonymous = false;
	
	/**
	 * This Logger's nearest parent.
	 * @var Logger
	 */
	private $parent;
	
	/**
	 * Children Loggers of this Logger.
	 * @var ArrayList
	 */
	private $kids;
	
	/**
	 * This Logger's message level.
	 * @var Level
	 */
	private $levelObject;
	
	/**
	 * This Logger's current effective message level value.
	 * @var int
	 */
	private $levelValue;

	/**
	 * Constructs a new Logger with the given name and LogManager.
	 * The LogManager should not be specified when constructing the global or system Loggers.
	 * @param string $name A name for the logger. This should be a FQCN and should normally be based
	 * 		on the package name or class name of the subsystem. It may be null for anonymous loggers.
	 * @param LogManager $manager
	 */
	public function __construct($name, LogManager $manager = null) {
		$this->handlers = new ArrayList('KM\Util\Logging\Handler');
		
		if ($manager == null) {
			$manager = LogManager::getLogManager();
		}
		$this->manager = $manager;
		$this->name = $name;
		$this->levelValue = Level::$INFO->intValue();
	}

	/**
	 * This method is called from LoggerContext.addLocalLogger() when the Logger is actually added
	 * to a LogManager.
	 * @param LogManager $manager
	 */
	public function setLogManager(LogManager $manager) {
		$this->manager = $manager;
	}

	/**
	 *
	 * @param string $name
	 * @return \KM\Util\Logging\Logger
	 */
	private static function demandLogger($name) {
		$manager = LogManager::getLogManager();
		return $manager->demandLogger($name);
	}

	/**
	 * Find or create a logger for a named subsystem.
	 * If a logger has already been created with the given name it is returned. Otherwise a new
	 * logger is created.
	 *
	 * If a new logger is created its log level will be configured based on the LogManager
	 * configuration and it will be configured to also send logging output to its parent handlers.
	 * It will be registered in the LogManager global namespace.
	 * @param string $name
	 * @return \KM\Util\Logging\Logger
	 */
	public static function getLogger($name) {
		return self::demandLogger( $name );
	}

	/**
	 * Adds a platform logger to the system context.
	 * @param string $name
	 * @return \KM\Util\Logging\Logger
	 */
	public static function getPlatformLogger($name) {
		$manager = LogManager::getLogManager();
		$result = $manager->demandSystemLogger( $name );
		return $result;
	}

	/**
	 * Create an anonymous Logger.
	 * The newly created Logger is not registered in the LogManager namespace. There will be no
	 * access checks on updates to the logger.
	 * <p>
	 * This factory method is primarily intended for use from applets. Because the resulting Logger
	 * is anonymous it can be kept private by the creating class. This removes the need for normal
	 * security checks, which in turn allows untrusted applet code to update the control state of
	 * the Logger. For example an applet can do a setLevel or an addHandler on an anonymous Logger.
	 * <p>
	 * Even although the new logger is anonymous, it is configured to have the root logger ("") as
	 * its parent. This means that by default it inherits its effective level and handlers from the
	 * root logger. Changing its parent via the {@link #setParent(java.util.logging.Logger)
	 * setParent} method will still require the security permission specified by that method.
	 *
	 * @return \KM\Util\Logging\Logger
	 */
	public static function getAnonymousLogger() {
		$manager = LogManager::getLogManager();
		$result = new Logger( null, $manager );
		$result->anonymous = true;
		$root = $manager->getLogger( '' );
		$result->doSetParent( $root );
		return $result;
	}

	/**
	 * Returns the current filter for this Logger.
	 * @return Filter
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * Set a filter to control output on this Logger.
	 * After passing the initial level check, the Logger will call this Filter to check if a log
	 * record should really be published.
	 * @param Filter $newFilter
	 */
	public function setFilter(Filter $newFilter) {
		$this->filter = $newFilter;
	}

	/**
	 * Log a LogRecord.
	 * All the other logging methods in this class call through this method to actually perform any
	 * logging. Subclasses can override this single method to capture all the log activity.
	 * @param LogRecord $record The LogRecord to be published.
	 */
	public function logRecord(LogRecord $record) {
		/* @var $handler Handler */
		if (!$this->isLoggable( $record->getLevel() )) {
			return;
		}
		$theFilter = $this->filter;
		if ($theFilter != null && !$theFilter->isLoggable( $record )) {
			return;
		}
		
		// Post the LogRecord to all our Handlers and then to our parent's handlers all the way up
		// the tree.
		$logger = $this;
		while ( $logger != null ) {
			foreach ( $logger->getHandlers() as $handler ) {
				$handler->publish( $record );
			}
			if (!$logger->getUseParentHandlers()) {
				break;
			}
			$logger = $logger->getParent();
		}
	}

	/**
	 * Private support method for logging.
	 * @param LogRecord $lr
	 */
	private function doLog(LogRecord $lr) {
		$lr->setLoggerName( $this->name );
		$this->logRecord( $lr );
	}

	/**
	 * Log a message, specifying the message level and an optional $arg of either an array of
	 * parameters or associated Throwable information.
	 *
	 * If the logger is currently enabled for the given message level then a corresponding LogRecord
	 * is created and forwarded to all registered output handler objects.
	 * @param Level $level
	 * @param string $msg
	 * @param string $arg
	 */
	public function log(Level $level, $msg, $arg = null) {
		if (!$this->isLoggable( $level )) {
			return;
		}
		$lr = new LogRecord( $level, $msg );
		if (is_array( $arg )) {
			$lr->setParameters( $arg );
		} elseif ($arg instanceof \Exception) {
			$lr->setThrown( $arg );
		}
		$this->doLog( $lr );
	}

	/**
	 * Log a message, specifying the message level, source class and method, and an optional $arg of
	 * either an array of parameters or associated Throwable information.
	 *
	 * If the logger is currently enabled for the given message level then a corresponding LogRecord
	 * is created and forwarded to all registered output handler objects.
	 * @param Level $level
	 * @param string $sourceClass
	 * @param string $sourceMethod
	 * @param string $msg
	 * @param array|\Exception|null $arg
	 */
	public function logp(Level $level, $sourceClass, $sourceMethod, $msg, $arg = null) {
		if (!$this->isLoggable( $level )) {
			return;
		}
		$lr = new LogRecord( $level, $msg );
		$lr->setSourceClassName( $sourceClass );
		$lr->setSourceMethodName( $sourceMethod );
		if (is_array( $arg )) {
			$lr->setParameters( $arg );
		} elseif ($arg instanceof \Exception) {
			$lr->setThrown( $arg );
		}
		$this->doLog( $lr );
	}

	/**
	 * Log a method entry.
	 * %his is a convenience method that can be used to log entry to a method. A LogRecord with
	 * message 'ENTRY', log level FINER and the given source class and method is logged.
	 * @param string $sourceClass
	 * @param string $sourceMethod
	 * @param array $params
	 */
	public function entering($sourceClass, $sourceMethod, array $params = null) {
		$msg = 'ENTRY';
		if ($params == null) {
			$this->logp( Level::$FINER, $sourceClass, $sourceMethod, $msg );
			return;
		}
		if (!$this->isLoggable( Level::$FINER )) {
			return;
		}
		for($i = 0; $i < count( $params ); $i++) {
			$msg = $msg . ' {' . $i . '}';
		}
		$this->logp( Level::$FINER, $sourceClass, $sourceMethod, $msg, $params );
	}

	/**
	 * Log a method return with an optional result.
	 * @param string $sourceClass
	 * @param string $sourceMethod
	 * @param mixed $result
	 */
	public function exiting($sourceClass, $sourceMethod, $result = null) {
		if ($result == null) {
			$this->logp( Level::$FINER, $sourceClass, $sourceMethod, 'RETURN' );
			return;
		}
		$result = array(
			$result
		);
		$this->logp( Level::$FINER, $sourceClass, $sourceMethod, 'RETURN {0}', $result );
	}

	/**
	 * Log throwing an exception.
	 * @param string $sourceClass
	 * @param string $sourceMethod
	 * @param \Exception $thrown
	 */
	public function throwing($sourceClass, $sourceMethod,\Exception $thrown) {
		if (!$this->isLoggable( Level::$FINER )) {
			return;
		}
		$lr = new LogRecord( Level::$FINER, 'THROW' );
		$lr->setSourceClassName( $sourceClass );
		$lr->setSourceMethodName( $sourceMethod );
		$lr->setThrown( $thrown );
		$this->doLog( $lr );
	}

	/**
	 * Log a SEVERE message.
	 *
	 * If the logger is currently enabled for the SEVERE message level then the
	 * given message is forwarded to all the registered output handler objects.
	 * @param string $msg
	 */
	public function severe($msg) {
		$this->log( Level::$SEVERE, $msg );
	}

	/**
	 * Log a WARNING message.
	 *
	 * If the logger is currently enabled for the WARNING message level then the given message is
	 * forwarded to all the registered output handler objects.
	 * @param string $msg
	 */
	public function warning($msg) {
		$this->log( Level::$WARNING, $msg );
	}

	/**
	 * Log an INFO message.
	 *
	 * If the logger is currently enabled for the INFO message level then the given message is
	 * forwarded to all the registered output handler objects.
	 * @param unknown $msg
	 */
	public function info($msg) {
		$this->log( Level::$INFO, $msg );
	}

	/**
	 * Log a CONFIG message.
	 *
	 * If the logger is currently enabled for the CONFIG message level then the given message is
	 * forwarded to all the registered output handler objects.
	 * @param string $msg
	 */
	public function config($msg) {
		$this->log( Level::$CONFIG, $msg );
	}

	/**
	 * Log a FINE message.
	 *
	 * If the logger is currently enabled for the FINE message level then the given message is
	 * forwarded to all the registered output handler objects.
	 * @param string $msg
	 */
	public function fine($msg) {
		$this->log( Level::$FINE, $msg );
	}

	/**
	 * Log a FINER message.
	 *
	 * If the logger is currently enabled for the FINER message level then the given message is
	 * forwarded to all the registered output handler objects.
	 * @param string $msg
	 */
	public function finer($msg) {
		$this->log( Level::$FINER, $msg );
	}

	/**
	 * Log a FINEST message.
	 *
	 * If the logger is currently enabled for the FINEST message level then the given message is
	 * forwarded to all the registered output handler objects.
	 * @param string $msg
	 */
	public function finest($msg) {
		$this->log( Level::$FINEST, $msg );
	}

	/**
	 * Sets the log level specifying which message levels will be logged by this logger.
	 * Message levels lower than this value will be discarded. The level value Level.OFF can be used
	 * to turn off logging.
	 *
	 * If the new level is null, it means that this node should inherit its level from its nearest
	 * ancestor with a specific non-null level value.
	 * @param Level $newLevel
	 */
	public function setLevel(Level $newLevel = null) {
		$this->levelObject = $newLevel;
		$this->updateEffectiveLevel();
	}

	/**
	 * Returns true if the message level has been initialized.
	 * @return boolean
	 */
	final public function isLevelInitialized() {
		return $this->levelObject != null;
	}

	/**
	 * Get the log Level that has been specified for this Logger.
	 * The result may be null, which means that this logger's effective level will be inherited from
	 * its parent.
	 * @return \KM\Util\Logging\Level
	 */
	public function getLevel() {
		return $this->levelObject;
	}

	/**
	 * Check if a message of the given level would actually be logged by this logger.
	 * This check is based on the Logger's effective level, which my be inherited from its parent.
	 * @param Level $level
	 * @return boolean True if the given message level is currently being logged.
	 */
	public function isLoggable(Level $level) {
		if ($level->intValue() < $this->levelValue || $this->levelValue == self::$offValue) {
			return false;
		}
		return true;
	}

	/**
	 * Returns the name for this logger.
	 * @return string Will be null for anonymous loggers.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Add a log handler to receive logging messages.
	 *
	 * By default, loggers also send their output to their parent logger. Typically the root Logger
	 * is configured with a set of Handlers that essentially act as default handlers for all
	 * loggers.
	 * @param Handler $handler
	 */
	public function addHandler(Handler $handler) {
		$this->handlers->add( $handler );
	}
	
	/**
	 * Removes a log handler.
	 * Returns silently if the given Handler is not found.
	 * @param Handler $handler
	 */
	public function removeHandler(Handler $handler) {
		$this->handlers->remove($handler);
	}
	
	/**
	 * Returns the Handlers associated with this Logger.
	 * @return Handler[] An array of all registered handlers.
	 */
	public function getHandlers() {
		return $this->handlers->toArray(self::$emptyHandlers);
	}

	/**
	 * Returns true if this logger is sending its output to its parent logger.
	 * @return boolean
	 */
	public function getUseParentHandlers() {
		return $this->useParentHandlers;
	}

	/**
	 * Specify whether or not this logger should send its output to its parent logger.
	 * This means that any LogRecords will also be written to the parent's handlers and potentially
	 * to its parent recursively up the namespace.
	 * @param boolean $useParentHandlers True if output is to be sent to the logger's parent.
	 */
	public function setUseParentHandlers($useParentHandlers) {
		$this->useParentHandlers = (bool) $useParentHandlers;
	}

	/**
	 * Return the parent for this Logger.
	 * <p>
	 * This method returns the nearest extant parent in the namespace. Thus if a Logger is called
	 * "a.b.c.d", and a Logger called "a.b" has been created but no logger "a.b.c" exists, then a
	 * call of getParent on the Logger "a.b.c.d" will return the Logger "a.b".
	 * <p>
	 * The result will be null if it is called on the root Logger in the namespace.
	 * @return \KM\Util\Logging\Logger
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Sets the parent for this Logger.
	 * This method is used by the LogManager to update a Logger when the namespace changes. It
	 * should not be called from application code.
	 * @param Logger $parent
	 */
	public function setParent(Logger $parent = null) {
		if($parent == null) {
			throw new NullPointerException();
		}
		if($this->manager == null) {
			$this->manager = LogManager::getLogManager();
		}
		$this->doSetParent( $parent );
	}

	/**
	 * Private method to do the work for parenting a child Logger onto a parent Logger.
	 * @param Logger $newParent
	 */
	private function doSetParent(Logger $newParent) {
		/* @var $iter Iterator */
		/* @var $ref Logger */
		
		// Remove ourselves from any previous parent.
		$ref = null;
		if($this->parent != null) {
			$iter = $this->parent->kids->getIterator();
			while($iter->hasNext()) {
				$ref = $iter->next();
				if($ref === $this) {
					// $ref is used down below to complete the re-parenting.
					$iter->remove();
					break;
				} else {
					$ref = null;
				}
			}
			// We have now removed ourselves from our parent's kids.
		}
		
		// Set our new parent.
		$this->parent = $newParent;
		if($this->parent->kids == null) {
			$this->parent->kids = new ArrayList('KM\Util\Logging\Logger');
		}
		if($ref == null) {
			// We didn't have a previous parent.
			$ref = $this;
		}
		$this->parent->kids->add($ref);
		
		// As a result of the re-parenting, the effective level may have changed for us and our children.
		$this->updateEffectiveLevel();
	}
	
	/**
	 * Removes the specified child Logger from the $kid list.
	 * @param Logger $child
	 */
	public final function removeChildLogger(Logger $child) {
		/* @var $iter Iterator */
		/* @var $ref Logger */
		$iter = $this->kids->getIterator();
		while($iter->hasNext()) {
			$ref = $iter->next();
			if($ref === $child) {
				$iter->remove();
				return ;
			}
		}
	}

	/**
	 * Recalculate the effective level for this node and recursively for our children.
	 */
	private function updateEffectiveLevel() {
		/* @var $kid Logger */
		// Figure out our current effective level.
		$newLevelValue = null;
		if ($this->levelObject != null) {
			$newLevelValue = $this->levelObject->intValue();
		} else {
			if ($this->parent != null) {
				$newLevelValue = $this->parent->levelValue;
			} else {
				// This may happen during initialization.
				$newLevelValue = Level::$INFO->intValue();
			}
		}
		
		// If our effective level hasn't changed, we're done.
		if ($this->levelValue == $newLevelValue) {
			return;
		}
		
		$this->levelValue = $newLevelValue;
		
		// Recursively update the level on each of our kids.
		if ($this->kids != null) {
			for($i = 0; $i < $this->kids->size(); $i++) {
				$kid = $this->kids->get( $i );
				if ($kid != null) {
					$kid->updateEffectiveLevel();
				}
			}
		}
	}
}
?>