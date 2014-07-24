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
namespace KM\Util\AbstractMap;

use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\Util\Map\Entry;

/**
 * An Entry maintaining an immutable key and value.
 * This class does not support method <tt>setValue</tt>. This class may be convenient in methods
 * that return thread-safe snapshots of key-value mappings.
 *
 * @package KM\Util\AbstractMap
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class SimpleImmutableEntry extends Object implements Entry {
	private $key;
	private $value;

	/**
	 * Creates an entry representing a mapping from the specified key to the specified value.
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function __construct($key, $value) {
		$this->key;
		$this->value;
	}

	public function getKey() {
		return $this->key;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value = null) {
		throw new UnsupportedOperationException();
	}

	/**
	 * Compares the specified object with this entry for equality.
	 * Returns true if the given object is also a map entry and the two entries represent the same
	 * mapping.
	 * @param Object $o
	 * @return boolean
	 * @see \KM\Lang\Object::equals()
	 */
	public function equals(Object $o = null) {
		/* @var $e Entry */
		if (!$o instanceof Entry) {
			return false;
		}
		return ($this->key == null ? $e->getKey() == null : $this->key == $e->getKey()) &&
			 ($this->value == null ? $e->getValue() == null : $this->value == $e->getValue());
	}

	/**
	 * Returns a string representation of this map entry.
	 * @return string
	 * @see \KM\Lang\Object::__toString()
	 */
	public function __toString() {
		return $this->key . '=' . $this->value;
	}
}
?>