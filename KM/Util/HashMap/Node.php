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
namespace KM\Util\HashMap;

use KM\Lang\Object;
use KM\Util\Map\Entry;
use KM\Lang\ClassCastException;

/**
 * Basic hash bin node, used for most entries.
 *
 * @package KM\Util\HashMap
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Node extends Object implements Entry {
	
	protected $hash;
	
	/**
	 * The node key.
	 * @var mixed
	 */
	protected $key;
	
	/**
	 * The node value.
	 * @var mixed
	 */
	protected $value;
	
	/**
	 * The next node in the map.
	 * @var Node
	 */
	protected $next;

	public function __construct($hash, $key, $value, Node $next = null) {
		$this->hash = $hash;
		$this->key = $key;
		$this->value = $value;
		$this->next = $next;
	}
	
	public function getHash() {
		return $this->hash;
	}

	public final function getKey() {
		return $this->key;
	}

	public final function getValue() {
		return $this->value;
	}

	public function setValue($newValue = null) {
		$oldValue = $this->value;
		$this->value = $newValue;
		return $oldValue;
	}
	
	public function getNext() {
		return $this->next;
	}
	
	public function setNext(Node $newNext = null) {
		$this->next = $newNext;
	}

	public function equals(Object $o = null) {
		/* @var $e Entry */
		if ($o === $this) {
			return true;
		}
		if ($o instanceof Entry) {
			$e = $o;
			if ($this->key === $e->getKey() && $this->value === $e->getValue()) {
				return true;
			}
		}
		return false;
	}

	public function __toString() {
		return $this->key . '=' . $this->value;
	}
}
?>