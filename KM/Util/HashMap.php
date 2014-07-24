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
namespace KM\Util;

use KM\IO\InvalidObjectException;
use KM\IO\ObjectInputStream;
use KM\IO\ObjectOutputStream;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\ClassNotFoundException;
use KM\Lang\Object;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Util\Collection;
use KM\Util\HashMap\EntrySet;
use KM\Util\HashMap\KeySet;
use KM\Util\HashMap\Node;
use KM\Util\HashMap\Values;
use KM\Util\Map;
use KM\Util\Set;

/**
 * Hash table based implementation of the <tt>Map</tt> interface.
 * This implementation provides all of the optional map operations, and permits
 * <tt>null</tt> values and the <tt>null</tt> key. (The <tt>HashMap</tt>
 * class is roughly equivalent to <tt>Hashtable</tt>, except that it is
 * unsynchronized and permits nulls.) This class makes no guarantees as to
 * the order of the map; in particular, it does not guarantee that the order
 * will remain constant over time.
 *
 * <p>If many mappings are to be stored in a <tt>HashMap</tt>
 * instance, creating it with a sufficiently large capacity will allow
 * the mappings to be stored more efficiently than letting it perform
 * automatic rehashing as needed to grow the table. Note that using
 * many keys with the same {@code hashCode()} is a sure way to slow
 * down performance of any hash table. To ameliorate impact, when keys
 * are {@link Comparable}, this class may use comparison order among
 * keys to help break ties.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class HashMap extends AbstractMap implements Map, Serializable {
	
	/**
	 * The store of keys.
	 * @Transient
	 * @var mixed[]
	 */
	private $keys = [];
	
	/**
	 * The store of values.
	 * @Transient
	 * @var mixed[]
	 */
	private $data = [];
	
	/**
	 * The table, initialized on first use and resized as necessary.
	 * When allocated, length is always a power of two.
	 * @Transient
	 * @var KM\Util\HashMap\Node[]
	 */
	protected $table = [];
	
	/**
	 * Holds cached entrySet().
	 * NOte that AbstractMap fields are used for keySet() and values().
	 * @Transient
	 * @var KM\Util\Set
	 */
	protected $entrySet = null;
	
	/**
	 * The number of key-value mappings contained in this map.
	 * @Transient
	 * @var int
	 */
	protected $size = 0;

	/**
	 * Constructs a new HashMap with the specified type parameters.
	 * If the optional map is specified, its mappings will be placed in this map.
	 * @param mixed $typeParameters An array of string values or a comma-delimited string of values
	 *        denoting the type parameters declared by this GenericDeclaration object.
	 * @param Map $m The optional map whose mappings are to be placed in this map.
	 */
	public function __construct($typeParameters = null, Map $m = null) {
		parent::__construct( $typeParameters );
		if ($m != null) {
			$this->putMapEntries( $m, false );
		}
	}

	/**
	 * Returns the backing table.
	 * @return Node[]
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Returns the index of the given key or false if none found.
	 * @param mixed $obj The key to locate in this mapping.
	 * @return int
	 */
	public function hash($key, $isPut = false) {
		if ($key == null) {
			return 0;
		}
		$index = array_search( $key, $this->keys );
		if ($index === false) {
			$index = ($isPut === true) ? $this->size : 0;
		}
		return $index;
	}

	/**
	 * Implements Map.putAll() and Map constructor
	 * @param Map $m
	 * @param boolean $evict False when initially constructing this map, else true
	 */
	protected function putMapEntries(Map $m, $evict = false) {
		/* @var $e Map\Entry */
		$this->testCollectionTypeParameters( $m );
		$s = $m->size();
		if ($s > 0) {
			foreach ( $m->entrySet() as $e ) {
				$key = $e->getKey();
				$value = $e->getValue();
				$this->putVal( $this->hash( $key, true ), $key, $value, false, $evict );
			}
		}
	}

	/**
	 * Returns the number of key-value mappings in this map.
	 * @return int
	 * @see \KM\Util\AbstractMap::size()
	 */
	public function size() {
		return $this->size;
	}

	/**
	 * Returns true if this map contains no key-value mappings.
	 * @return boolean
	 * @see \KM\Util\AbstractMap::isEmpty()
	 */
	public function isEmpty() {
		return $this->size == 0;
	}

	/**
	 * Returns the value to which the specified key is mapped, or {@code null} if this map contains
	 * no mapping for the key.
	 *
	 * <p>More formally, if this map contains a mapping from a key {@code k} to a value {@code v}
	 * such that {@code (key==null ? k==null : key.equals(k))}, then this method returns {@code v};
	 * otherwise it returns {@code null}. (There can be at most one such mapping.)
	 *
	 * <p>A return value of {@code null} does not <i>necessarily</i> indicate that the map contains
	 * no mapping for the key; it's also possible that the map explicitly maps the key to {@code
	 * null}. The {@link #containsKey containsKey} operation may be used to distinguish these two
	 * cases.
	 * @param mixed $key
	 * @return mixed
	 * @see \KM\Util\AbstractMap::get()
	 */
	public function get($key = null) {
		/* @var $e Node */
		return ($e = $this->getNode( $this->hash( $key ), $key )) == null ? null : $e->getValue();
	}

	/**
	 * Implements Map.get() and related methods.
	 * @param mixed $hash
	 * @param mixed $key
	 * @return \KM\Util\HashMap\Node NULL
	 */
	public function getNode($hash, $key) {
		/* @var $first Node */
		/* @var $e Node */
		$this->testTypeParameters( $key, null );
		if ($this->table != null && count( $this->table ) > 0 && isset( $this->table[$hash] ) &&
			 ($first = $this->table[$hash]) != null) {
			if ($first->getHash() == $hash && $first->getKey() == $key) {
				return $first;
			}
			if (($e = $first->getNext() != null)) {
				do {
					if ($e->getHash() == $hash && $e->getKey() == $key) {
						return $e;
					}
				} while ( ($e = $e->getNext()) != null );
			}
		}
		return null;
	}

	/**
	 * Returns true if this map contains a mapping for the specified key.
	 * @param mixed $key
	 * @return boolean
	 * @see \KM\Util\AbstractMap::containsKey()
	 */
	public function containsKey($key = null) {
		return $this->getNode( $this->hash( $key ), $key ) != null;
	}

	/**
	 * Associates the specified value with the specified key in this map.
	 * If the map previously contained a mapping for the key, the old value is replaced.
	 * @param mixed $key Key with which the specified value is to be associated.
	 * @param mixed $value Value to be associated with the specified key.
	 * @return mixed The previous value associated with $key or null if there is no mapping for
	 *         $key. (A null return can also indicate that the map previously associated null with
	 *         $key.)
	 * @see \KM\Util\AbstractMap::put()
	 */
	public function put($key, $value) {
		$this->testTypeParameters( $key, $value );
		return $this->putVal( $this->hash( $key, true ), $key, $value, false, true );
	}

	/**
	 * Implements Map.put() and related methods.
	 * @param mixed $hash
	 * @param mixed $key
	 * @param mixed $value
	 * @param boolean $onlyIfAbsent
	 * @param Boolean $evict
	 * @return mixed
	 */
	protected function putVal($hash, $key, $value, $onlyIfAbsent, $evict) {
		/* @var $p Node */
		/* @var $e Node */
		$n = count( $this->table );
		if (!isset( $this->table[$hash] )) {
			$this->table[$hash] = $this->newNode( $hash, $key, $value, null );
			$this->keys[$hash] = $key;
			$this->data[$hash] = $value;
		} else {
			$p = $this->table[$hash];
			if ($p->getHash() == $hash && $p->getKey() == $key) {
				$e = $p;
			} else {
				for($binCount = 0;; ++$binCount) {
					if (($e = $p->getNext()) == null) {
						$p->setNext( $this->newNode( $hash, $key, null, $next ) );
						break;
					}
					if ($e->getHash() == $hash && $e->getKey() == $key) {
						break;
					}
					$p = $e;
				}
			}
			if ($e != null) {
				// Existing mapping for key.
				$oldValue = $e->getValue();
				if (!$onlyIfAbsent || $oldValue == null) {
					$e->setValue( $value );
				}
				$this->afterNodeAccess( $e );
				return $oldValue;
			}
		}
		$this->size++;
		$this->afterNodeInsertion( $evict );
		return null;
	}

	/**
	 * Copies all of the mappings from the specified map to this map.
	 * These mappings will replace any mappings that this map had for any of the keys currently in
	 * the specified map.
	 * @param Map $m
	 * @see \KM\Util\AbstractMap::putAll()
	 */
	public function putAll(Map $m) {
		$this->putMapEntries( $m, true );
	}

	/**
	 * Removes the mapping for the specified key from this map if present.
	 * @param mixed $key Key whose mapping is to be removed from the map.
	 * @return \KM\Util\HashMap\Node The previous value associated with $key, or null if there was
	 *         no mapping for $key (A null return can also indicate that the map previously
	 *         associated null with $key.)
	 * @see \KM\Util\AbstractMap::remove()
	 */
	public function remove($key = null) {
		/* @var $e Node */
		return ($e = $this->removeNode( $this->hash( $key ), $key, null, false, true )) ? null : $e->getValue();
	}

	/**
	 * Implements Map.remove() and related methods.
	 * @param mixed $hash
	 * @param mixed $key
	 * @param mixed $value
	 * @param boolean $matchValue If true, only remove is value is equal.
	 * @param boolean $movable If false, do not move other nodes while removing.
	 * @return \KM\Util\HashMap\Node
	 */
	public function removeNode($hash, $key, $value, $matchValue, $movable) {
		/* @var $p Node */
		/* @var $node Node */
		/* @var $e Node */
		if ($this->table != null && count( $this->table ) > 0 && isset( $this->table[$hash] )) {
			$p = $this->table[$hash];
			if ($p->getHash() == $hash && (($k = $p->getKey()) == $key || ($key != null && $key == $k))) {
				$node = $p;
			} elseif (($e = $p->getNext()) != null) {
				do {
					if ($e->getHash() == $hash && (($k = $e->getKey()) == $key || ($key != null && $key == $k))) {
						$node = $e;
						break;
					}
					$p = $e;
				} while ( ($e = $e->getNext()) != null );
			}
			if ($node != null && (!$matchValue || ($v = $node->getValue()) == $value)) {
				if ($node == $p) {
					$this->table[$hash] = $node->getNext();
					$this->keys[$hash] = $node->getNext() != null ? $node->getNext()->getKey() : null;
					$this->data[$hash] = $node->getNext() != null ? $node->getNext()->getValue() : null;
				} else {
					$p->setNext( $node->getNext() );
				}
				$this->size--;
				$this->afterNodeRemoval( $node );
				return $node;
			}
		}
		return null;
	}

	/**
	 * Removes all of the mappings from this map.
	 * The map will be empty after this call returns.
	 * @see \KM\Util\AbstractMap::clear()
	 */
	public function clear() {
		if ($this->table != null && $this->size > 0) {
			$this->size = 0;
			$this->table = [];
			$this->keys = [];
			$this->data = [];
		}
	}

	/**
	 * Returns true if this map maps one or more keys to the specified value.
	 * @param mixed $value Value whose presence in this map is to be tested.
	 * @return boolean True if this map maps one or more keys to the specified value.
	 * @see \KM\Util\AbstractMap::containsValue()
	 */
	public function containsValue($value = null) {
		/* @var $e Node */
		if ($this->table != null && $this->size > 0) {
			return array_search( $value, $this->data ) !== false ? true : false;
		}
		return false;
	}

	/**
	 * Returns a {@link Set} view of the keys contained in this map.
	 * The set is backed by the map, so changes to the map are reflected in the set, and vice-versa.
	 * If the map is modified while an iteration over the set is in progress (except through the
	 * iterator's own <tt>remove</tt> operation), the results of the iteration are undefined. The
	 * set supports element removal, which removes the corresponding mapping from the map, via the
	 * <tt>Iterator.remove</tt>, <tt>Set.remove</tt>, <tt>removeAll</tt>, <tt>retainAll</tt>, and
	 * <tt>clear</tt> operations. It does not support the <tt>add</tt> or <tt>addAll</tt>
	 * operations.
	 * @return \KM\Util\Set A set view of the keys in this map.
	 * @see \KM\Util\AbstractMap::keySet()
	 */
	public function keySet() {
		/* @var $ks Set */
		return ($ks = $this->keySet) == null ? ($this->keySet = new KeySet( $this )) : $ks;
	}

	/**
	 * Returns a {@link Collection} view of the values contained in this map.
	 * The collection is backed by the map, so changes to the map are reflected in the collection,
	 * and vice-versa. If the map is modified while an iteration over the collection is in progress
	 * (except through the iterator's own <tt>remove</tt> operation), the results of the iteration
	 * are undefined. The collection supports element removal, which removes the corresponding
	 * mapping from the map, via the <tt>Iterator.remove</tt>, <tt>Collection.remove</tt>,
	 * <tt>removeAll</tt>, <tt>retainAll</tt> and <tt>clear</tt> operations. It does not support the
	 * <tt>add</tt> or <tt>addAll</tt> operations.
	 * @return \KM\Util\Collection A view of the values contained in this map.
	 * @see \KM\Util\AbstractMap::values()
	 */
	public function values() {
		/* @var $vs Collection */
		return ($vs = $this->values) == null ? ($this->values = new Values( $this )) : $vs;
	}

	/**
	 * Returns a {@link Set} view of the mappings contained in this map.
	 * The set is backed by the map, so changes to the map are reflected in the set, and vice-versa.
	 * If the map is modified while an iteration over the set is in progress (except through the
	 * iterator's own <tt>remove</tt> operation, or through the <tt>setValue</tt> operation on a map
	 * entry returned by the iterator) the results of the iteration are undefined. The set supports
	 * element removal, which removes the corresponding mapping from the map, via the
	 * <tt>Iterator.remove</tt>, <tt>Set.remove</tt>, <tt>removeAll</tt>, <tt>retainAll</tt> and
	 * <tt>clear</tt> operations. It does not support the <tt>add</tt> or <tt>addAll</tt>
	 * operations.
	 * @return \KM\Util\Set A set view of the mappings contained in this map.
	 * @see \KM\Util\AbstractMap::entrySet()
	 */
	public function entrySet() {
		/* @var $es Set */
		return ($es = $this->entrySet) == null ? ($this->entrySet = new EntrySet( $this )) : $es;
	}

	/**
	 * Returns the value to which the specified $key is mapped, or $defaultValue if this map
	 * contains no mapping for the key.
	 * @param mixed $key The key whose associated value is to be returned.
	 * @param mixed $defaultValue The default mapping of the key.
	 * @return mixed The value to which the specified key is mapped, or $defaultValue if this map
	 *         contains no mapping for the key.
	 */
	public function getOrDefault($key, $defaultValue) {
		/* @var $e Node */
		$this->testTypeParameters( $key, $defaultValue );
		return ($e = $this->getNode( $this->hash( $key ), $key )) == null ? $defaultValue : $e->getValue();
	}

	/**
	 * If the specified key is not already associated with a value (or is mapped to null), this
	 * method associates it with the given value and returns null, else returns the current value.
	 * @param mixed $key Key with which the specified value is associated.
	 * @param mixed $value Value to be associated with the specified key.
	 * @return mixed The previous value associated with the specified key, or null if there was no
	 *         mapping for the key. (A null return can also indicate that the map previously
	 *         associated null with the key if the implementation supports null values.)
	 */
	public function putIfAbsent($key, $value) {
		$this->testTypeParameters( $key, $value );
		return $this->putVal( $this->hash( $key, true ), $key, $value, true, true );
	}

	/**
	 * Replaces the entry for the specified key only if it is currently mapped to some value.
	 * @param mixed $key Key with which the specified value is associated.
	 * @param mixed $value Value to be associated with the specified key.
	 * @return mixed The previous value associated with the specified key, or null if there was no
	 *         mapping for the key. (A null return can also indicate that the map previously
	 *         associated null with the key if the implementation supports null values.)
	 */
	public function replace($key, $value) {
		/* @var $e Node */
		$this->testTypeParameters( $key, $value );
		if (($e = $this->getNode( $this->hash( $key ), $key )) != null) {
			$oldValue = $e->getValue();
			$e->setValue( $value );
			$this->afterNodeAccess( $e );
			return $oldValue;
		}
		return null;
	}

	/**
	 * Creates a regular node.
	 * @param int $hash
	 * @param mixed $key
	 * @param mixed $value
	 * @param Node $next
	 * @return \KM\Util\HashMap\Node
	 */
	protected function newNode($hash, $key, $value, Node $next = null) {
		$this->testTypeParameters( $key, $value );
		return new Node( $hash, $key, $value, $next );
	}

	/**
	 * Callback to allow post-action.
	 * @param Node $p
	 */
	protected function afterNodeAccess(Node $p) {
		// Noop
	}

	/**
	 * Callback to allow post-action.
	 * @param boolean $evict
	 */
	protected function afterNodeInsertion($evict) {
		// Noop
	}

	/**
	 * Callback to allow post-action
	 * @param Node $p
	 */
	protected function afterNodeRemoval(Node $p) {
		// Noop
	}
	
	/* ---------- Serialization Methods ---------- */
	
	/**
	 * Save the state of the HasMap instance to a stream (i.e.
	 * serialize it).
	 * @param ObjectOutputStream $s
	 * @throws InvalidObjectException if an I/O error occurs
	 */
	private function writeObject(ObjectOutputStream $s) {
		// Write out any serialization
		$s->defaultWriteObject();
		
		// Write out size
		$s->writeInt( $this->size );
		
		// Write out elements in proper order
		$this->internalWriteEntries( $s );
	}

	/**
	 * Reconstitute the HashMap instance from a stream (i.e.
	 * deserialize it).
	 * @param ObjectInputStream $s
	 * @throws InvalidObjectException if an I/O error occurs
	 * @throws ClassNotFoundException
	 */
	private function readObject(ObjectInputStream $s) {
		// Read in any serialization
		$s->defaultReadObject();
		
		// Read in size
		$mappings = $s->readInt();
		if ($mappings < 0) {
			throw new InvalidObjectException( 'Illegal mappings count: ' . $mappings );
		} elseif ($mappings > 0) {
			// Read the keys and values and put the mappings in the map
			for($i = 0; $i < $mappings; $i++) {
				$key = $s->readMixed();
				$value = $s->readMixed();
				if ($i == 0) {
					// Set the type parameters from the first entry
					$this->typeParameters = [
						ReflectionUtility::typeForValue( $key )->getTypeName(),
						ReflectionUtility::typeForValue( $value )->getTypeName(),
					];
				}
				$this->putVal( self::hash( $key, true ), $key, $value, false, false );
			}
		}
	}

	private function internalWriteEntries(ObjectOutputStream $s) {
		/* @var $e Node */
		$keyType = ReflectionUtility::typeFor( $this->typeParameters[0] );
		$valueType = ReflectionUtility::typeFor( $this->typeParameters[1] );
		if ($this->size > 0 && $this->table != null) {
			for($i = 0; $i < count( $this->table ); $i++) {
				for($e = $this->table[$i]; $e != null; $e = $e->getNext()) {
					$s->writeMixed( $e->getKey(), $keyType );
					$s->writeMixed( $e->getValue(), $valueType );
				}
			}
		}
	}
}
?>