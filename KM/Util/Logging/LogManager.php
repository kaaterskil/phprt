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
 * @category Kaaterskil
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
namespace KM\Util\Logging;

use KM\IO\File;
use KM\Lang\IllegalStateException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\System;
use KM\Util\ArrayList;
use KM\Util\HashMap;
use KM\Util\Iterator;
use KM\Util\Logging\LogManager\Beans;
use KM\Util\Logging\LogManager\LoggerContext;
use KM\Util\Logging\LogManager\RootLogger;
use KM\Util\Logging\LogManager\SystemLoggerContext;
use KM\Util\Map;
use KM\Util\Properties;
use KM\Util\Set;

/**
 * There is a single global LogManager object that is used to
 * maintain a set of shared state about Loggers and log services.
 * <p>
 * This LogManager object:
 * <ul>
 * <li> Manages a hierarchical namespace of Logger objects. All
 * named Loggers are stored in this namespace.
 * <li> Manages a set of logging control properties. These are
 * simple key-value pairs that can be used by Handlers and
 * other logging objects to configure themselves.
 * </ul>
 * <p>
 * The global LogManager object can be retrieved using LogManager.getLogManager().
 * The LogManager object is created during class initialization and
 * cannot subsequently be changed.
 * <p>
 * At startup the LogManager class is located using the
 * java.util.logging.manager system property.
 * <p>
 * The LogManager defines two optional system properties that allow control over
 * the initial configuration:
 * <ul>
 * <li>"java.util.logging.config.class"
 * <li>"java.util.logging.config.file"
 * </ul>
 * These two properties may be specified on the command line to the "java"
 * command, or as system property definitions passed to JNI_CreateJavaVM.
 * <p>
 * If the "java.util.logging.config.class" property is set, then the
 * property value is treated as a class name. The given class will be
 * loaded, an object will be instantiated, and that object's constructor
 * is responsible for reading in the initial configuration. (That object
 * may use other system properties to control its configuration.) The
 * alternate configuration class can use <tt>readConfiguration(InputStream)</tt>
 * to define properties in the LogManager.
 * <p>
 * If "java.util.logging.config.class" property is <b>not</b> set,
 * then the "java.util.logging.config.file" system property can be used
 * to specify a properties file (in java.util.Properties format). The
 * initial logging configuration will be read from this file.
 * <p>
 * If neither of these properties is defined then the LogManager uses its
 * default configuration. The default configuration is typically loaded from the
 * properties file "{@code lib/logging.properties}" in the Java installation
 * directory.
 * <p>
 * The properties for loggers and Handlers will have names starting
 * with the dot-separated name for the handler or logger.
 * <p>
 * The global logging properties may include:
 * <ul>
 * <li>A property "handlers". This defines a whitespace or comma separated
 * list of class names for handler classes to load and register as
 * handlers on the root Logger (the Logger named ""). Each class
 * name must be for a Handler class which has a default constructor.
 * Note that these Handlers may be created lazily, when they are
 * first used.
 *
 * <li>A property "&lt;logger&gt;.handlers". This defines a whitespace or
 * comma separated list of class names for handlers classes to
 * load and register as handlers to the specified logger. Each class
 * name must be for a Handler class which has a default constructor.
 * Note that these Handlers may be created lazily, when they are
 * first used.
 *
 * <li>A property "&lt;logger&gt;.useParentHandlers". This defines a boolean
 * value. By default every logger calls its parent in addition to
 * handling the logging message itself, this often result in messages
 * being handled by the root logger as well. When setting this property
 * to false a Handler needs to be configured for this logger otherwise
 * no logging messages are delivered.
 *
 * <li>A property "config". This property is intended to allow
 * arbitrary configuration code to be run. The property defines a
 * whitespace or comma separated list of class names. A new instance will be
 * created for each named class. The default constructor of each class
 * may execute arbitrary code to update the logging configuration, such as
 * setting logger levels, adding handlers, adding filters, etc.
 * </ul>
 * <p>
 * Note that all classes loaded during LogManager configuration are
 * first searched on the system class path before any user class path.
 * That includes the LogManager class, any config classes, and any
 * handler classes.
 * <p>
 * Loggers are organized into a naming hierarchy based on their
 * dot separated names. Thus "a.b.c" is a child of "a.b", but
 * "a.b1" and a.b2" are peers.
 * <p>
 * All properties whose names end with ".level" are assumed to define
 * log levels for Loggers. Thus "foo.level" defines a log level for
 * the logger called "foo" and (recursively) for any of its children
 * in the naming hierarchy. Log Levels are applied in the order they
 * are defined in the properties file. Thus level settings for child
 * nodes in the tree should come after settings for their parents.
 * The property name ".level" can be used to set the level for the
 * root of the tree.
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class LogManager extends Object {
	
	/**
	 * The global LogManager.
	 * @var LogManager
	 */
	private static $manager;
	
	/**
	 * The configuration properties.
	 * @var Properties
	 */
	private $props;
	
	/**
	 * The default Level for this manager.
	 * @var Level
	 */
	public static $defaultLevel;
	
	/**
	 * The map of registered listeners.
	 * The map value is the registration count to allow for cases where the same listener is
	 * registered many times.
	 * @var Map
	 */
	private $listenerMap;
	
	/**
	 * Context for system loggers.
	 * @var LoggerContext
	 */
	private $systemContext;
	
	/**
	 * Context for user loggers.
	 * @var LoggerContext
	 */
	private $userContext;
	
	/**
	 * The root Logger
	 * @var Logger
	 */
	private $rootLogger;
	
	/**
	 * Flag to determine if the original configuration file has been read.
	 * @var boolean
	 */
	private $readPrimordialConfiguration;
	
	/**
	 * Flag to determine if root handlers have been initialized.
	 * @var boolean
	 */
	private $initializedGlobalHandlers = true;
	
	/**
	 * Initialization flag.
	 * @var boolean
	 */
	private $initializedCalled = false;
	
	/**
	 * Initialization flag.
	 * @var boolean
	 */
	private $initializationDone = false;

	/**
	 * Static initializer
	 */
	public static function clinit() {
		self::$defaultLevel = Level::$INFO;
		
		$mgr = null;
		try {
			$cname = System::getProperty( 'php.util.logging.manager' );
			if ($cname != null) {
				$clazz = new \ReflectionClass( $cname );
				$mgr = $clazz->newInstance();
			}
		} catch ( \Exception $e ) {
			$msg = "Could not load log manager'" . $cname . "' " . $e->getTraceAsString();
			trigger_error( $msg );
		}
		if ($mgr == null) {
			$mgr = new LogManager();
		}
		self::$manager = $mgr;
	}

	/**
	 * Protected constructor.
	 */
	protected function __construct() {
		$this->props = new Properties();
		$this->listenerMap = new HashMap( '<\KM\Lang\Object, integer>' );
		$this->systemContext = new SystemLoggerContext( self::$manager, $this );
		$this->userContext = new LoggerContext( self::$manager, $this );
	}
	
	public function __destruct() {
		$this->reset();
	}

	/**
	 * Lazy initialization: if this instance of manager is the global
	 * manager then this method will read the initial configuration and
	 * add the root logger and global logger by calling addLogger().
	 *
	 * Note that it is subtly different from what we do in LoggerContext.
	 * In LoggerContext we're patching up the logger context tree in order to add
	 * the root and global logger *to the context tree*.
	 *
	 * For this to work, addLogger() must have already have been called
	 * once on the LogManager instance for the default logger being
	 * added.
	 *
	 * This is why ensureLogManagerInitialized() needs to be called before
	 * any logger is added to any logger context.
	 */
	public function ensureLogManagerInitialized() {
		/* @var $owner LogManager */
		$owner = $this;
		if ($this->initializationDone || $owner != self::$manager) {
			// We don't want to do this twice and we don't want to do this on private manager
			// instances.
			return;
		}
		
		// If initializationCalled is true it means that we're already in the process of
		// initializing the LogManager in this thread. There has been a recursive call to
		// ensureLogManagerInitialized().
		$isRecursiveInitialization = ($this->initializedCalled === true);
		if ($isRecursiveInitialization || $this->initializationDone) {
			// If isRecursiveInitialization is true it means that we're already in the process of
			// initializing the LogManager in this thread. There has been a recursive call to
			// ensureLogManagerInitialized(). We should not proceed as it would lead to infinite
			// recursion. If initializationDone is true then it means the manager has finished
			// initializing - just return as we're done.
			return;
		}
		
		// Calling addLogger() below will in turn call requiresDefaultLogger() which will call
		// ensureLogManagerInitialized(). We use initializedCalled to break the recursion.
		$this->initializedCalled = true;
		
		assert( $this->rootLogger == null );
		assert( $this->initializedCalled && !$this->initializationDone );
		
		// Read the configuration.
		$owner->readPrimordialConfiguration();
		
		// Create and retain logger for the root of the namespace.
		$owner->rootLogger = new RootLogger( $this );
		$owner->addLogger( $owner->rootLogger );
		
		$this->initializationDone = true;
	}

	/**
	 * Returns the singleton instance.
	 * @return \KM\Util\Logging\LogManager
	 */
	public static function getLogManager() {
		if (self::$manager != null) {
			self::$manager->ensureLogManagerInitialized();
		}
		return self::$manager;
	}

	private function readPrimordialConfiguration() {
		if (!$this->readPrimordialConfiguration) {
			$this->readPrimordialConfiguration = true;
			$this->readConfiguration();
		}
	}

	/**
	 * Returns the user context.
	 * @return \KM\Util\Logging\LogManager\LoggerContext
	 */
	private function getUserContext() {
		return $this->userContext;
	}

	/**
	 * Returns the system context.
	 * @return \KM\Util\Logging\LogManager\LoggerContext
	 */
	public function getSystemContext() {
		return $this->systemContext;
	}

	/**
	 * Returns a list of contexts.
	 * @return \KM\Util\ArrayList
	 */
	private function contexts() {
		$cxs = new ArrayList( '\KM\Util\Logging\LogManager\LoggerContext' );
		$cxs->add( $this->getSystemContext() );
		$cxs->add( $this->getUserContext() );
		return $cxs;
	}

	/**
	 * Returns this root logger.
	 * @return \KM\Util\Logging\Logger
	 */
	public function getRootLogger() {
		return $this->rootLogger;
	}

	/**
	 * Find or create a specified logger instance.
	 * If a logger has already been created with the given name it is returned. Otherwise a new
	 * logger instance is creates and registered in the LogManager global namespace. This method
	 * will always create a non-null object.
	 *
	 * This method must delegate to the LogManager implementation to add a new Logger or return the
	 * one that has been added previously as a LogManager subclass may override the addLogger,
	 * getLogger, readConfiguration and other methods.
	 * @param string $name
	 * @return \KM\Util\Logging\Logger
	 */
	public function demandLogger($name) {
		$result = $this->getLogger( $name );
		if ($result == null) {
			// Only allocate the new logger once.
			$newLogger = new Logger( $name, $this );
			do {
				if ($this->addLogger( $newLogger )) {
					// We successfully added the new Logger that we created above so return it
					// without re-fetching.
					return $newLogger;
				}
				// We didn't add the new Logger that we created above because another thread added a
				// Logger with the same name after our null check above and before our call to
				// addLogger(). We have to re-fetch the Logger because addLogger() returns a boolean
				// instead of the Logger reference itself. However, if the thread that created the
				// other Logger is not holding a strong reference to the other Logger, then it is
				// possible for the other Logger to be garbage collected after we saw it in add
				// Logger() and before we can re-fetch it. If it has been garbage collected then
				// we'll just loop around and try again.
				$result = $this->getLogger( $name );
			} while ( $result == null );
		}
		return $result;
	}

	/**
	 * Returns the specified system logger.
	 * @param string $name
	 * @return \KM\Util\Logging\Logger
	 */
	public function demandSystemLogger($name) {
		/* @var $l Logger */
		/* @var $hdl Handler */
		$sysLogger = $this->getSystemContext()->demandLogger( $name );
		
		// Add the system logger to the LogManager's namespace if not exist so that there is only
		// one single logger of the given name. System loggers are visible to applications unless a
		// logger of the same name has been added.
		$logger = null;
		do {
			// First attempt to call addLogger() instead of getLogger(). This would avoid potential
			// bug in custom LogManager.getLogger() implementation that adds a logger if does not
			// exist.
			if ($this->addLogger( $sysLogger )) {
				// Successfully added the new system logger.
				$logger = $sysLogger;
			} else {
				$logger = $this->getLogger( $name );
			}
		} while ( $logger == null );
		
		// LogManager will set the $sysLogger's handlers via LogManager.addLogger() method.
		if ($logger != $sysLogger && count( $sysLogger->getHandlers() ) == 0) {
			// If logger already exists but handlers not set.
			$l = $logger;
			foreach ( $l->getHandlers() as $hdl ) {
				$sysLogger->addHandler( $hdl );
			}
		}
		return $sysLogger;
	}

	/**
	 * Add new log handlers
	 * @param Logger $logger
	 * @param string $name
	 * @param string $handlersPropertyName
	 */
	private function loadLoggerHandlers(Logger $logger, $name, $handlersPropertyName) {
		/* @var $hdl Handler */
		$names = $this->parseClassNames( $handlersPropertyName );
		for($i = 0; $i < count( $names ); $i++) {
			$word = $names[$i];
			try {
				$clazz = new \ReflectionClass( $word );
				$hdl = $clazz->newInstance();
				// Check if there is a property defining this handler's level.
				$levs = $this->getProperty( $word . '.level' );
				if ($levs != null) {
					$level = Level::findLevel( $levs );
					if ($level != null) {
						$hdl->setLevel( $level );
					} else {
						trigger_error( "Can't set level for " . $word );
					}
				}
				// Add this handler to the logger.
				$logger->addHandler( $hdl );
			} catch ( \Exception $e ) {
				$msg = "Can't load log handler '" . $word . "'";
				$msg .= "\n" . $e->getTraceAsString();
				trigger_error( $msg );
			}
		}
	}

	/**
	 * Adds a named logger.
	 * This does nothing and returns false if a logger with the same name is already registered.
	 * The logger factory methods call this method to register each newly created Logger.
	 * The application should retain its own references to the Logger object to avoid it being
	 * garbage collected.
	 * @param Logger $logger The new logger.
	 * @return boolean True of the given logger was registered successfully, false if a logger of
	 *         that same name already exists.
	 */
	public function addLogger(Logger $logger) {
		/* @var $cx LoggerContext */
		$name = $logger->getName();
		if ($name === null) {
			throw new NullPointerException();
		}
		$cx = $this->getUserContext();
		if ($cx->addLocalLogger( $logger )) {
			// Do we have a per-logger handler, too?
			$this->loadLoggerHandlers( $logger, $name, $name . '.handlers' );
			return true;
		}
		return false;
	}

	/**
	 * Sets a level on a logger.
	 * @param Logger $logger
	 * @param Level $level
	 */
	public static function doSetLevel(Logger $logger, Level $level) {
		$logger->setLevel( $level );
		return;
	}

	/**
	 * Sets a parent on a logger.
	 * @param Logger $logger
	 * @param Logger $parent
	 */
	public static function doSetParent(Logger $logger, Logger $parent) {
		$logger->setParent( $parent );
	}

	/**
	 * Method to find a named Logger.
	 * Note that since untrusted code may create loggers with arbitrary names, this method should
	 * not be relied on to find Loggers for security sensitive logging.
	 * It is also important to note that the a logger associated with the $name may be garbage
	 * collected at any time if there is no strong reference to the Logger. The caller of this
	 * method must check the return value for null in order to properly handle the case where the
	 * Logger has been garbage collected.
	 * @param string $name
	 * @return Logger The matching Logger or null if none found.
	 */
	public function getLogger($name) {
		return $this->getUserContext()->findLogger( $name );
	}

	/**
	 * Returns a set of known logger names.
	 * Note: Loggers may be added dynamically as new classes are loaded. This method only reports on
	 * the loggers that are currently registered. It is also important to note that this method only
	 * returns the name of a Logger, not a reference to the Logger itself. THe returned string does
	 * nothing to prevent the Logger from being garbage collected. In particular, if the returned
	 * name is passed to LogManager.getLogger(), then the caller must check the return value from
	 * LogManager.getLogger() for null to properly handle the case where the Logger has been
	 * garbage collected in the time since its name was returned by this method.
	 * @return \KM\Util\Set
	 */
	public function getLoggerNames() {
		return $this->getUserContext()->getLoggerNames();
	}

	/**
	 * Reinitialize the logging properties and re-read the logging configuration, which should be
	 * in array notation.
	 *
	 * And log-level definitions in the new configuration file will be applied using
	 * Logger.setLEvel() if the target logger exists.
	 */
	public function readConfiguration($inputStream = null) {
		if ($inputStream == null) {
			$this->readConfiguration0();
		} else {
			$this->readConfiguration1( $inputStream );
		}
	}

	/**
	 * Reinitialize the logging properties and reread the logging configuration.
	 * <p>
	 * The same rules are used for locating the configuration properties as are used at startup. So
	 * normally the logging properties will be re-read from the same file that was used at startup.
	 * <P>
	 * Any log level definitions in the new configuration file will be applied using
	 * Logger.setLevel(), if the target Logger exists.
	 * <p>
	 * A PropertyChangeEvent will be fired after the properties are read.
	 */
	private function readConfiguration0() {
		
		// If a configuration class is specified, load it and use it.
		$cname = System::getProperty( 'php.util.logging.config.class' );
		if ($cname != null) {
			try {
				// Instantiate the named class. It is the constructor's responsibility to initialize
				// the logging configuration by calling readConfiguration(InputStream) with a
				// suitable stream.
				$clazz = new \ReflectionClass( $cname );
				$clazz->newInstance();
				return;
			} catch ( \ReflectionException $e ) {
				$data = "Logging configuration for class '" . $cname . "' failed. \n";
				$data .= $e->getTraceAsString();
				trigger_error( $data );
			}
		}
		
		$fname = System::getProperty( 'php.util.logging.config.file' );
		if ($fname == null) {
			$fname = System::getProperty( 'php.home' );
			if ($fname == null) {
				throw new IllegalStateException( "Can't find php.home" );
			}
		}
		$f = new File( $fname, 'lib' );
		$f = new File( $f, 'logging.properties' );
		if ($f->exists()) {
			$this->readConfiguration1( $f->getPath() );
		}
	}

	/**
	 * Reset the logging configuration.
	 * For all named loggers, the reset operation removes and closes all Handlers and (except for
	 * the root logger) sets the level to null. The root logger's level is set to Level.INFO.
	 */
	public function reset() {
		/* @var $iter Iterator */
		/* @var $it Iterator */
		/* @var $cx LoggerContext */
		
		$this->props = new Properties();
		
		// Since we are doing a reset we no longer want to initialize the global handlers, if they
		// haven't been initialized yet.
		$this->initializedGlobalHandlers = true;
		
		$iter = $this->contexts()->getIterator();
		while ( $iter->hasNext() ) {
			$cx = $iter->next();
			$it = $cx->getLoggerNames()->getIterator();
			while ( $it->hasNext() ) {
				$name = $it->next();
				$logger = $cx->findLogger( $name );
				if ($logger != null) {
					$this->resetLogger( $logger );
				}
			}
		}
	}

	/**
	 * Resets an individual target logger.
	 * @param Logger $logger
	 */
	private function resetLogger(Logger $logger) {
		/* @var $h Handler */
		// Close all the logger's handlers.
		$targets = $logger->getHandlers();
		for($i = 0; $i < count( $targets ); $i++) {
			$h = $targets[$i];
			$logger->removeHandler( $h );
			try {
				$h->close();
			} catch ( \Exception $e ) {
				// Drop through.
			}
		}
		$name = $logger->getName();
		if ($name != null && $name == '') {
			// This is the root logger.
			$logger->setLevel( self::$defaultLevel );
		} else {
			$logger->setLevel( null );
		}
	}

	/**
	 * Returns a list of whitespace separated class names from a property.
	 * @param string $propertyName
	 * @return string[]
	 */
	private function parseClassNames($propertyName) {
		$hands = (string) $this->getProperty( $propertyName );
		if ($hands == null) {
			return array();
		}
		$hands = trim( $hands );
		$ix = 0;
		$result = array();
		while ( $ix < strlen( $hands ) ) {
			$end = $ix;
			while ( $end < strlen( $hands ) ) {
				if (preg_match( '/\s/', $hands[$end] )) {
					break;
				}
				if ($hands[$end] == ',') {
					break;
				}
				$end++;
			}
			$word = substr( $hands, $ix, $end - $ix );
			$ix = $end + 1;
			$word = trim( $word );
			if (strlen( $word ) == 0) {
				continue;
			}
			$result[] = $word;
		}
		return $result;
	}

	private function readConfiguration1($filename) {
		/* @var $listeners Map */
		/* @var $entry Map\Entry */
		$filename = (string) $filename;
		$this->reset();
		
		// Load the properties
		$this->props->load( $filename );
		// Instantiate new configuration objects.
		$names = $this->parseClassNames( 'config' );
		
		for($i = 0; $i < count( $names ); $i++) {
			$word = $names[$i];
			try {
				$clazz = new \ReflectionClass( $word );
				$clazz->newInstance();
			} catch ( \Exception $e ) {
				$msg = "Can't load config class '" . $word . "' \n" . $e->getTraceAsString();
				trigger_error( $msg );
			}
		}
		
		// Set levels on any pre-existing loggers based on the new properties.
		$this->setLevelsOnExistingLoggers();
		
		// Notify any interested parties that our properties have changed. We first take a copy of
		// the listener map so that we aren't holding any locks when calling the listeners.
		$listeners = null;
		if (!$this->listenerMap->isEmpty()) {
			$listeners = new HashMap( '<\KM\Lang\Object, integer>', $this->listenerMap );
		}
		if ($listeners != null) {
			assert( Beans::isBeansPresent() );
			$ev = Beans::newPropertyChangeEvent( LogManager::getClass(), null, null, null );
			foreach ( $listeners->entrySet() as $entry ) {
				$listener = $entry->getKey();
				$count = $entry->getValue();
				for($i = 0; $i < $count; $i++) {
					Beans::invokePropertyChange( $listener, $ev );
				}
			}
		}
		
		// Note that we need to reinitialize global handlers when they are first referenced.
		$this->initializedGlobalHandlers = false;
	}

	/**
	 * Returns the value of a logging property.
	 * The method returns null if the property is not found.
	 * @param string $name
	 * @return string
	 */
	public function getProperty($name) {
		return $this->props->getProperty( $name );
	}

	/**
	 * Returns a logging property as a string.
	 * @param string $name
	 * @param string $defaultValue
	 * @return string
	 */
	public function getStringProperty($name, $defaultValue) {
		$val = (string) $this->getProperty( $name );
		if ($val == null) {
			return (string) $defaultValue;
		}
		return trim( $val );
	}

	/**
	 * Returns a logging property as an integer.
	 * @param string $name
	 * @param string $defaultValue
	 * @return int
	 */
	public function getIntProperty($name, $defaultValue) {
		$val = (string) $this->getProperty( $name );
		if ($val == null) {
			return (int) $defaultValue;
		}
		try {
			return intval( trim( $val ) );
		} catch ( \Exception $e ) {
			return (int) $defaultValue;
		}
	}

	/**
	 * Returns a logging property as a boolean.
	 * @param string $name
	 * @param string $defaultValue
	 * @return boolean
	 */
	public function getBooleanProperty($name, $defaultValue) {
		$val = (string) $this->getProperty( $name );
		if ($val == null) {
			return (boolean) $defaultValue;
		}
		$val = strtolower( $val );
		if ($val == 'true' || $val == '1') {
			return true;
		} elseif ($val == 'false' || $val == '0') {
			return false;
		}
		return (boolean) $defaultValue;
	}

	/**
	 * Returns a Level property.
	 * @param string $name
	 * @param Level $defaultValue
	 * @return Level
	 */
	public function getLevelProperty($name, Level $defaultValue = null) {
		$val = (string) $this->getProperty( $name );
		if ($val == null) {
			return $defaultValue;
		}
		$level = Level::findLevel( trim( $val ) );
		return $level != null ? $level : $defaultValue;
	}

	/**
	 * Returns a Filter property.
	 * We return an instance of the class named by the $name property. If the property is not
	 * defined or has problems we return the default value.
	 * @param string $name
	 * @param Filter $defaultValue
	 * @return Filter
	 */
	public function getFilterProperty($name, Filter $defaultValue = null) {
		$val = (string) $this->getProperty( $name );
		try {
			if ($val != null) {
				$clazz = new \ReflectionClass( $val );
				return $clazz->newInstance();
			}
		} catch ( \Exception $e ) {
			// Drop through.
		}
		return $defaultValue;
	}

	/**
	 * Returns a Formatter property.
	 * We return an instance of the class named by the $name property. If the property is not
	 * defined or has problems, we return the default value.
	 * @param string $name
	 * @param Formatter $defaultValue
	 * @return object Formatter
	 */
	public function getFormatterProperty($name, Formatter $defaultValue = null) {
		$val = (string) $this->getProperty( $name );
		try {
			$clazz = new \ReflectionClass( $val );
			return $clazz->newInstanceWithoutConstructor();
		} catch ( \Exception $e ) {
			// Drop through.
		}
		return $defaultValue;
	}

	/**
	 * Loads the global handlers.
	 * We do the real work lazily when the global handlers are first used.
	 */
	public function initializeGlobalHandlers() {
		if ($this->initializedGlobalHandlers) {
			return;
		}
		$this->initializedGlobalHandlers = true;
		$this->loadLoggerHandlers( $this->rootLogger, null, 'handlers' );
	}

	/**
	 * Called when the configuration has changed to apply any level settings to any pre-existing
	 * loggers.
	 */
	private function setLevelsOnExistingLoggers() {
		/* @var $cx LoggerContext */
		$enum = $this->props->propertyNames();
		while ( $enum->hasNext() ) {
			$key = $enum->next();
			if(strpos($key, '.level') === false) {
				// Not a level definition
				continue;
			}
			$ix = strlen( $key ) - 6;
			$name = $ix == 0 ? "" : substr( $key, 0, $ix );
			$level = $this->getLevelProperty( $key, null );
			if ($level == null) {
				trigger_error( 'Bad level value for property: ' . $key );
				continue;
			}
			foreach ( $this->contexts() as $cx ) {
				$logger = $cx->findLogger( $name );
				if ($logger == null) {
					continue;
				}
				$logger->setLevel( $level );
			}
		}
	}
}
?>