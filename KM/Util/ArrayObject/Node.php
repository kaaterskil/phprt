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
namespace KM\Util\ArrayObject;

use KM\IO\Serializable;
use KM\Lang\Comparable;
use KM\Lang\Object;
use KM\Lang\ClassCastException;

/**
 * Node Class
 *
 * @package KM\Util\ArrayObject
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Node extends Object implements Comparable {
	
	/**
	 * The node key
	 * @var mixed
	 */
	private $key;
	
	/**
	 * The node value
	 * @var mixed
	 */
	private $value;
	
	/**
	 * Pointer to the previous node.
	 * @var \KM\Util\ArrayObject\Node
	 */
	public $prev;
	
	/**
	 * Pointer to the next node.
	 * @var \KM\Util\ArrayObject\Node
	 */
	public $next;

	/**
	 * Constructs a new Node with the given data.
	 * @param Node $prev The previous linked node, if any.
	 * @param mixed $key The current node key.
	 * @param mixed $value The current node value.
	 * @param Node $next The next linked node, if any.
	 */
	public function __construct(Node $prev = null, $key, $value = null, Node $next = null) {
		$this->prev = $prev;
		$this->key = $key;
		$this->value = $value;
		$this->next = $next;
	}

	/**
	 * Returns the node key.
	 * @return mixed
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Returns the value of this node.
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Sets the value of this node.
	 * @param mixed $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Compares the given Node object with this instance for ordering.
	 * Key values are compared numerically or, if keys are represented by strings,
	 * lexicographically.
	 * @param Object $o The Node object to compare.
	 * @throws ClassCastException
	 * @return int
	 */
	public function compareTo(Object $o = null) {
		/* @var $that Node */
		if ($o == null || !$o instanceof Node) {
			throw new ClassCastException();
		}
		$that = $o;
		if ($this->key > $that->key) {
			return 1;
		}
		return $this->key < $that->key ? -1 : 0;
	}
}
?>