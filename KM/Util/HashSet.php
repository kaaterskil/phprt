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
use KM\IO\IOException;
use KM\IO\ObjectInputStream;
use KM\IO\ObjectOutputStream;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\ClassNotFoundException;
use KM\Lang\Object;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Util\HashMap;

/**
 * This class implements the Set interface, backed by a hash table (actually a HashMap
 * instance).
 * It makes no guarantees as to the iteration order of the set; in particular, it does not
 * guarantee that the order will remain constant over time. This class permits the null
 * element.
 *
 * This class offers constant time performance for the basic operations (add, remove,
 * contains and size), assuming the hash function disperses the elements properly among
 * the buckets. Iterating over this set requires time proportional to the sum of the
 * HashSet instance's size (the number of elements) plus the "capacity" of the backing
 * HashMap instance (the number of buckets). Thus, it's very important not to set the
 * initial capacity too high (or the load factor too low) if iteration performance is
 * important.
 *
 * Note that this implementation is not synchronized. If multiple threads access a hash
 * set concurrently, and at least one of the threads modifies the set, it must be
 * synchronized externally. This is typically accomplished by synchronizing on some object
 * that naturally encapsulates the set. If no such object exists, the set should be
 * "wrapped" using the Collections.synchronizedSet method. This is best done at creation
 * time, to prevent accidental unsynchronized access to the set:
 *
 * Set s = Collections.synchronizedSet(new HashSet(...));
 *
 * Note that the fail-fast behavior of an iterator cannot be guaranteed as it is,
 * generally speaking, impossible to make any hard guarantees in the presence of
 * unsynchronized concurrent modification.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class HashSet extends AbstractSet implements Set, Serializable {
	
	/**
	 * A dummy object to associate with a value in the backing map.
	 * @var Object
	 */
	private static $PRESENT;

	/**
	 * Static constructor.
	 */
	public static function clinit() {
		self::$PRESENT = new Object();
	}
	
	/**
	 * The backing map.
	 * @Transient
	 * @var HashMap
	 */
	private $map;

	/**
	 * Creates a new hash set with the given collection.
	 * @param string $typeParameter A value denoting the type parameter declared by this
	 *        GenericDeclaration object.
	 * @param Collection $c
	 */
	public function __construct($typeParameter = null, Collection $c = null) {
		parent::__construct( $typeParameter );
		$this->map = new HashMap( '<' . strval( $typeParameter ) . ', \KM\Lang\Object>' );
		if ($c != null) {
			$this->addAll( $c );
		}
	}

	/**
	 * Returns an iterator over the elements in this set.
	 * The elements are returned in no particular order.
	 * @return \KM\Util\Iterator
	 * @see \KM\Util\AbstractCollection::getIterator()
	 */
	public function getIterator() {
		return $this->map->keySet()->getIterator();
	}

	/**
	 * Returns the number of element in this set (its cardinality).
	 * @return int
	 * @see \KM\Util\AbstractCollection::size()
	 */
	public function size() {
		return $this->map->size();
	}

	/**
	 * Returns true if this set contains no elements.
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::isEmpty()
	 */
	public function isEmpty() {
		return $this->map->isEmpty();
	}

	/**
	 * Returns true if this set contains the specified element.
	 * @param mixed $o Element whose presence in this set is to be tested.
	 * @return boolean True if this set contains the specified element.
	 * @see \KM\Util\AbstractCollection::contains()
	 */
	public function contains($o = null) {
		return $this->map->containsKey( $o );
	}

	/**
	 * Adds the specified element to this set if it is not already present.
	 * If this set already contains the element, the call leaves the set unchanged and
	 * returns false.
	 * @param mixed $e The element to be added to this set.
	 * @return boolean True if this set did not already contain the specified element.
	 * @see \KM\Util\AbstractCollection::add()
	 */
	public function add($e) {
		return ($this->map->put( $e, self::$PRESENT ) == null);
	}

	/**
	 * Removes the specified element from this set it it is present.
	 * Returns true if this set contained the element (or equivalently, if this set
	 * changed as a result of the call). This set will not contain the element once the
	 * call returns.
	 * @param mixed $o Object to be removed from this set, if present.
	 * @return boolean True if the set contained the specified element.
	 * @see \KM\Util\AbstractCollection::remove()
	 */
	public function remove($o = null) {
		return ($this->map->remove( $o ) == self::$PRESENT);
	}

	/**
	 * Removes all of the elements from this set.
	 * The set will be empty after the call returns.
	 * @see \KM\Util\AbstractCollection::clear()
	 */
	public function clear() {
		$this->map->clear();
	}
	
	/* ---------- Serialization Methods ---------- */
	
	/**
	 * Saves the HashMap instance to a stream (i.e.
	 * serializes it).
	 * @param ObjectOutputStream $s
	 * @throws IOException if an I/O error occurs
	 */
	private function writeObject(ObjectOutputStream $s) {
		// Write out any serialization
		$s->defaultWriteObject();
		
		// Write out size
		$s->writeInt( $this->map->size() );
		
		// Write out all elements in proper order
		$keyType = ReflectionUtility::typeFor( $this->typeParameters[0] );
		foreach ( $this->map->keySet() as $e ) {
			$s->writeMixed( $e, $keyType );
		}
	}

	/**
	 * Reconstitute the HashSet instance from a stream (i.e.
	 * deserialize it).
	 * @param ObjectInputStream $s
	 * @throws InvalidObjectException
	 * @throws IOException if an I/O error occurs
	 * @throws ClassNotFoundException
	 */
	private function readObject(ObjectInputStream $s) {
		// Read in any serialization
		$s->defaultReadObject();
		
		// Read in size
		$size = $s->readInt();
		if ($size < 0) {
			throw new InvalidObjectException( 'Illegal size: ' . $size );
		}
		
		if ($size == 0) {
			// Create the backing HashMap
			$this->map = new HashMap( '<string, \KM\Lang\Object>' );
		} else {
			for($i = 0; $i < $size; $i++) {
				$e = $s->readMixed();
				if ($i == 0) {
					// Create the backing map with the proper type parameter
					$this->map = new HashMap( [
						ReflectionUtility::typeForValue( $e )->getTypeName(),
						'\KM\Lang\Object'
					] );
				}
				$this->map->put( $e, self::$PRESENT );
			}
		}
	}
}
?>