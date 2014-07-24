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
use KM\Util\Map\Entry;

/**
 * An Entry maintaining a key and a value.
 * The value may be changed using the <tt>setValue</tt> method. This class facilitates the process
 * of building custom map implementations. For example, it may be convenient to return arrays of
 * <tt>SimpleEntry</tt> instances in method <tt>Map.entrySet().toArray</tt>.
 *
 * @package KM\Util\AbstractMap
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class SimpleEntry extends Object implements Entry {
	private $key;
	private $value;

	/**
	 * Creates an entry representing a mapping from the specified key to the specified value.
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function __construct($key = null, $value = null) {
		/* @var $e Entry */
		if($key != null && $key instanceof Entry) {
			$e = $key;
			$key = $e->getKey();
			$value = $e->getValue();
		}
		$this->key = $key;
		$this->value = $value;
	}

	/**
	 * Returns the key corresponding to this entry
	 * @return mixed
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Returns the value corresponding to this entry.
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Replaces the value corresponding to this entry with the specified value.
	 * @param mixed $value
	 * @return mixed
	 */
	public function setValue($value) {
		$oldValue = $this->value;
		$this->value = $value;
		return $oldValue;
	}

	/**
	 * Compares the specified object with this entry for equality.
	 * Returns {@code true} if the given object is also a map entry and the two entries represent
	 * the same mapping.
	 * @param Object $o
	 * @return boolean
	 * @see \KM\Lang\Object::equals()
	 */
	public function equals(Object $o = null) {
		/* @var $e Entry */
		if (!$o instanceof Entry) {
			return false;
		}
		$e = $o;
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