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

use KM\Lang\Object;
use KM\Util\Logging\Logger;
use KM\Util\HashMap;
use KM\Util\Iterator;
use KM\Util\Logging\LogManager;
use KM\Util\Map;

/**
 * Represents a node in our tree of nested loggers.
 *
 * @package KM\Util\Logging\LogManager
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class LogNode extends Object {
	
	/**
	 * The LogNode's children nodes.
	 * @var HashMap
	 */
	protected $children;
	
	/**
	 * The Logger reference
	 * @var Logger
	 */
	protected $loggerRef;
	
	/**
	 * This LogNode's parent
	 * @var LogNode
	 */
	protected $parent;
	
	/**
	 *
	 * @var LoggerContext
	 */
	protected $context;

	/**
	 * Constructs a LogNode with the given parent, context.
	 * @param LogNode $parent
	 * @param LoggerContext $context
	 */
	public function __construct(LogNode $parent = null, LoggerContext $context) {
		$this->parent = $parent;
		$this->context = $context;
	}
	
	/**
	 * Returns the map of children.
	 * @return \KM\Util\Map
	 */
	public function getChildren() {
		return $this->children;
	}
	
	/**
	 * Sets the nodes children.
	 * @param Map $children
	 */
	public function setChildren(Map $children) {
		$this->children = $children;
	}
	
	/**
	 * Returns the logger reference.
	 * @return \KM\Util\Logging\Logger
	 */
	public function getLoggerRef() {
		return $this->loggerRef;
	}
	
	/**
	 * Sets the logger reference.
	 * @param Logger $ref
	 */
	public function setLoggerRef(Logger $ref) {
		$this->loggerRef = $ref;
	}
	
	/**
	 * Returns this node's parent.
	 * @return \KM\Util\Logging\LogManager\LogNode
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * Sets this node's parent.
	 * @param LogNode $parent
	 */
	public function setParent(LogNode $parent) {
		$this->parent = $parent;
	}

	/**
	 * Recursive method to walk the tree below a node and set a new parent logger.
	 * @param Logger $parent
	 */
	public function walkAndSetParent(Logger $parent) {
		/* @var $iter Iterator */
		/* @var $node LogNode */
		/* @var $logger Logger */
		if ($this->children == null) {
			return;
		}
		$iter = $this->children->values()->getIterator();
		while ( $iter->hasNext() ) {
			$node = $iter->next();
			$logger = $node->loggerRef;
			// $logger = ($ref == null) ? null : $ref;
			if ($logger == null) {
				// $node->walkAndSetParent( $parent );
			} else {
				LogManager::doSetParent( $logger, $parent );
			}
		}
	}
}
?>