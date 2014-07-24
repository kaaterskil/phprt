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

use KM\Lang\IllegalStateException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Util\HashMap;
use KM\Util\Logging\Logger;
use KM\Util\Logging\LogManager;
use KM\Util\Logging\LogManager\LogNode;

/**
 * LoggerContext maintains the logger namespace per context.
 * The default LogManager implementation has one system context and user context. The System context
 * is used to maintain the namespace for all system loggers and is queried by the system code. If a
 * system logger doesn't exist in the user context, it will also be added to the user context. The
 * user context is queried by the user code and all other loggers are added in the user context.
 *
 * @package KM\Util\Logging\LogManager
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class LoggerContext extends Object {
	
	/**
	 * Table of named Loggers that maps names to Loggers.
	 * @var HashMap
	 */
	private $namedLoggers;
	
	/**
	 * Tree of named Loggers
	 * @var LogNode
	 */
	private $root;
	
	/**
	 * The log manager.
	 * @var LogManager
	 */
	private $manager;
	
	/**
	 * The log manager owner of this context.
	 * @var LogManager
	 */
	private $owner;

	/**
	 * Constructs a new context with the given log manager.
	 * @param LogManager $manager
	 */
	public function __construct(LogManager $manager = null, LogManager $owner) {
		$this->namedLoggers = new HashMap( '<string, \KM\Util\Logging\Logger>' );
		$this->root = new LogNode( null, $this );
		$this->manager = $manager;
		$this->owner = $owner;
	}

	/**
	 * Tells whether default loggers are required for this context.
	 * If true, the default loggers will be lazily added.
	 * @return boolean
	 */
	public final function requiresDefaultLoggers() {
		$requiresDefaultLoggers = ($this->getOwner() === $this->manager);
		if ($requiresDefaultLoggers) {
			$this->getOwner()->ensureLogManagerInitialized();
		}
		return $requiresDefaultLoggers;
	}

	/**
	 * Return this context's log manager.
	 * @return \KM\Util\Logging\LogManager
	 */
	public final function getOwner() {
		return $this->owner;
	}

	/**
	 * Returns this context's root logger which, if not null and if the context requires default
	 * loggers, will be added to the context logger's tree.
	 * @return \KM\Util\Logging\Logger
	 */
	public final function getRootLogger() {
		return $this->getOwner()->getRootLogger();
	}

	/**
	 * Returns the named logger or null of none exists.
	 * @param string $name
	 * @return \KM\Util\Logging\Logger
	 */
	public function demandLogger($name) {
		$owner = $this->getOwner();
		return $owner->demandLogger( $name );
	}

	/**
	 * Ensures that the root logger is set.
	 */
	private function ensureInitialized() {
		if ($this->requiresDefaultLoggers()) {
			$this->ensureDefaultLogger( $this->getRootLogger() );
		}
	}

	/**
	 * Returns the named logger from this context's tree.
	 * @param string $name
	 * @return NULL \KM\Util\Logging\LogManager\Logger
	 */
	public function findLogger($name) {
		/* @var $logger Logger */
		$this->ensureInitialized();
		$ref = $this->namedLoggers->get( $name );
		if ($ref == null) {
			return null;
		}
		$logger = $ref;
		return $logger;
	}

	/**
	 * This method is called before adding Logger to the context.
	 * This method will ensure that the default loggers are added before the given logger.
	 * @param Logger $logger
	 */
	private function ensureAllDefaultLoggers(Logger $logger) {
		if ($this->requiresDefaultLoggers()) {
			$name = $logger->getName();
			if (!empty($name)) {
				$this->ensureDefaultLogger( $this->getRootLogger() );
				if($name != Logger::GLOBAL_LOGGER_NAME) {
					// Deprecated
				}
			}
		}
	}

	/**
	 * Used for lazy addition of root logger to a LoggerContext.
	 * @param Logger $logger
	 * @throws IllegalStateException
	 */
	private function ensureDefaultLogger(Logger $logger = null) {
		// This check is simple sanity. We do not want that this method be called for anything else
		// than owner.rootLogger().
		if (!$this->requiresDefaultLoggers() || $logger == null ||
			 $logger != $this->getOwner()->getRootLogger()) {
			// The case where we have a non-null logger which is not manager.rootLogger indicates a
			// serious issue - as ensureDefaultLogger should never be called with any other loggers
			// than this one (or null if manager.rootLogger is not yet initialized).
			assert( $logger == null );
			return;
		}
		// Adds the logger if it is not already there.
		if (!$this->namedLoggers->containsKey( $logger->getName() )) {
			// It is important to prevent addLocalLogger() to call ensureDefaultLoggers() when we're
			// in the process of adding one of those default loggers as this would immediately cause
			// a stack overflow. Therefore we must pass addDefaultLoggersIfNeeded - false even if
			// requiresDefaultLoggers() is true.
			$this->addLocalLogger( $logger, false );
		}
	}

	public function addLocalLogger(Logger $logger, $addDefaultLoggersIfNeeded = null) {
		/* @var $nodep LogNode */
		if ($addDefaultLoggersIfNeeded === null) {
			// No need to add default loggers if it is not required.
			$addDefaultLoggersIfNeeded = $this->requiresDefaultLoggers();
		}
		
		// $addDefaultLoggersIfNeeded serves to break recursion when adding default Loggers. If
		// we're adding one of the default loggers (we're being called from ensureDefaultLogger())
		// than $addDefaultLoggersIfNeeded will be false/ We don't want to call
		// ensureDefaultLoggers() again.
		if ($addDefaultLoggersIfNeeded) {
			$this->ensureAllDefaultLoggers( $logger );
		}
		
		$name = $logger->getName();
		if ($name === null) {
			throw new NullPointerException();
		}
		$ref = $this->namedLoggers->get( $name );
		if ($ref != null) {
			// We already have a registered logger with the given name.
			return false;
		}
		
		// We're adding a new logger.
		$owner = $this->getOwner();
		$logger->setLogManager( $owner );
		$ref = $logger;  // Java sets a weak reference here
		$this->namedLoggers->put( $name, $logger ); // Java sets a weak reference here
		
		// Apply any initial level defined for the new logger, unless the logger's level is already
		// initialized.
		$level = $owner->getLevelProperty( $name . '.level', null );
		if ($level != null && !$logger->isLevelInitialized()) {
			LogManager::doSetLevel( $logger, $level );
		}
		
		// Instantiation of the handler is done in the LogManager.addLogger() implementation as a
		// handler class may only be visible to LogManager.
		$this->processParentHandlers( $logger, $name );
		
		// Find the new node and its parent.
		$node = $this->getNode( $name );
		$node->setLoggerRef( $logger );	// Set the weak reference
		$parent = null;
		$nodep = $node->getParent();
		while ( $nodep != null ) {
			$nodeRef = $nodep->getLoggerRef();	// In Java, this is a weak reference
			if ($nodeRef != null) {
				$parent = $nodeRef;	// In Java, this is the logger that the weak reference references.
				break;
			}
			$nodep = $nodep->getParent();
		}
		
		if ($parent != null) {
			LogManager::doSetParent( $logger, $parent );
		}
		// Walk over the children and tell them we are their new parent.
		$node->walkAndSetParent( $logger );
		return true;
	}

	public function removeLoggerRef($name, Logger $ref) {
		$this->namedLoggers->remove( $name );
	}

	/**
	 * Returns the logger names
	 * @return \KM\Util\Set
	 */
	public function getLoggerNames() {
		// Ensure that the context is properly initialized before returning logger names.
		$this->ensureInitialized();
		return $this->namedLoggers->keySet();
	}

	/**
	 * If logger.getUseParentHandlers() returns true and any of the logger's parents have levels or
	 * handlers defined, make sure they are instantiated.
	 * @param Logger $logger
	 * @param string $name
	 */
	private function processParentHandlers(Logger $logger, $name) {
		$owner = $this->getOwner();
		if ($logger != $owner->getRootLogger()) {
			$useParent = $owner->getBooleanProperty( $name . '.useParentHandlers', true );
			if (!$useParent) {
				$logger->setUseParentHandlers( false );
			}
		}
		
		$ix = 1;
		for(;;) {
			$ix2 = ($ix < strlen($name)) ? strpos( $name, "\\", $ix ) : false;
			if ($ix2 === false) {
				break;
			}
			$pname = substr( $name, 0, $ix2 );
			if ($owner->getProperty( $pname . '.level' ) != null ||
				 $owner->getProperty( $pname . '.handlers' ) != null) {
				// This $pname has a level/handlers definition. Make sure it exists.
				$this->demandLogger( $pname );
			}
			$ix = $ix2 + 1;
		}
	}

	/**
	 * Returns a node in our tree of logger nodes.
	 * If necessary, create it.
	 * @param string $name
	 * @return \KM\Util\Logging\LogManager\LogNode
	 */
	public function getNode($name) {
		/* @var $child LogNode */
		if ($name == null || $name == '') {
			return $this->root;
		}
		$node = $this->root;
		$strlen = strlen($name);
		while ( $strlen > 0 ) {
			$ix = strpos( $name, "\\" );
			$head = '';
			if ($ix !== false && $ix > 0) {
				$head = substr( $name, 0, $ix );
				$name = substr( $name, $ix + 1 );
				$strlen = strlen($name);
			} else {
				$head = $name;
				$name = '';
				$strlen = 0;
			}
			if ($node->getChildren() == null) {
				$node->setChildren( new HashMap('<string, \KM\Util\Logging\LogManager\LogNode>') );
			}
			$child = $node->getChildren()->get( $head );
			if ($child == null) {
				$child = new LogNode( $node, $this );
				$node->getChildren()->put( $head, $child );
			}
			$node = $child;
		}
		return $node;
	}
}
?>