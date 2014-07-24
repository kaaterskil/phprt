<?php

/**
 * Kaaterskil Library
 *
 * PHP version 5.4
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
 * <COPYRIGHT HOLDER> BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Kaaterskil
 * @package     KM\Util
 * @copyright   Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version     SVN $Id$
 */
namespace KM\Util;

use KM\IO\IOException;
use KM\IO\ObjectInputStream;
use KM\IO\ObjectOutputStream;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\ArrayIndexOutOfBoundsException;
use KM\Lang\ClassCastException;
use KM\Lang\ClassNotFoundException;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\Object;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Lang\System;
use KM\Util\Collection;
use KM\Util\ArrayList\Itr;
use KM\Util\ArrayList\ListItr;

/**
 * Array implementation of the List interface.
 * Implements all optional list operations and permits all elements including null.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ArrayList extends AbstractList implements ListInterface, \IteratorAggregate, Serializable {
	private static $DEFAULT_CAPACITY = 10;
	
	/**
	 * The array buffer into which the elements of the list are stored.
	 * @Transient
	 * @var array
	 */
	protected $elementData = [];
	
	/**
	 * The number of elements the list contains.
	 * @var int
	 */
	protected $size;

	/**
	 * Constructs a list containing the elements of the specified collection, if any, in
	 * the order they are returned by the collection's iterator.
	 * @param string $typeParameter A value denoting the type parameter declared by this
	 *        GenericDeclaration object.
	 * @param Collection|array $c The collection whose elements are to be places into this list.
	 */
	public function __construct($typeParameter = '<string>', $c = null) {
		parent::__construct( $typeParameter );
		if ($c != null) {
			if ($c instanceof Collection) {
				if ($c->getTypeParameters() != $this->typeParameters) {
					throw new ClassCastException();
				}
				$this->elementData = $c->toArray();
			} elseif (is_array( $c )) {
				foreach ( $c as $value ) {
					$this->testTypeParameters( $value );
					$this->elementData[] = $value;
				}
			}
		}
		$this->size = count( $this->elementData );
	}

	/**
	 * Trims the capacity of this ArrayList instance to be the list's current size.
	 * An application can use this operation to minimize the storage of an ArrayList instance.
	 */
	public function trimToSize() {
		if ($this->size < count( $this->elementData )) {
			$this->elementData = Arrays::copyOf( $this->elementData, $this->size );
		}
	}

	/**
	 * Increases the capacity of this ArrayList instance, if necessary, to ensure that it can hold
	 * at least the number of elements specified by the minimum capacity argument.
	 * @param int $minCapacity The desired minimum capacity.
	 */
	public function ensureCapacity($minCapacity) {
		$minExpand = (count( $this->elementData ) != 0 ? 0 : self::$DEFAULT_CAPACITY);
		if ($minCapacity > $minExpand) {
			$this->ensureExplicitCapacity( $minCapacity );
		}
	}

	private function ensureCapacityInternal($minCapacity) {
		if (count( $this->elementData ) == 0) {
			$minCapacity = max( array(
				self::$DEFAULT_CAPACITY,
				$minCapacity
			) );
		}
		$this->ensureExplicitCapacity( $minCapacity );
	}

	private function ensureExplicitCapacity($minCapacity) {
		if ($minCapacity - count( $this->elementData ) > 0) {
			$this->grow( $minCapacity );
		}
	}

	/**
	 * Increases the capacity to ensure that it can hold at least the number of elements specified
	 * by the minimum capacity argument.
	 * @param int $minCapacity The desired minimum capacity.
	 */
	private function grow($minCapacity) {
		$oldCapacity = count( $this->elementData );
		$newCapacity = $oldCapacity + ($oldCapacity >> 1);
		if ($newCapacity - $minCapacity < 0) {
			$newCapacity = $minCapacity;
		}
		if ($newCapacity - PHP_INT_MAX - 8 > 0) {
			$newCapacity = static::hugeCapacity( $minCapacity );
		}
		
		// $minCapacity is usually close to size, so this is a win.
		$this->elementData = Arrays::copyOf( $this->elementData, $newCapacity );
	}

	private static function hugeCapacity($minCapacity) {
		if ($minCapacity < 0) {
			throw new \OverflowException();
		}
		return $minCapacity > (PHP_INT_MAX - 8) ? PHP_INT_MAX : PHP_INT_MAX - 8;
	}

	/**
	 * Returns the number of elements in this collection.
	 * @return int
	 * @see \KM\Util\AbstractCollection::size()
	 */
	public function size() {
		return $this->size;
	}

	/**
	 * Returns true if this collection contains no elements.
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::isEmpty()
	 */
	public function isEmpty() {
		return $this->size == 0;
	}

	/**
	 * Returns true if this collection contains the specified element.
	 * @param mixed $o
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::contains()
	 */
	public function contains($o = null) {
		return $this->indexOf( $o ) >= 0;
	}

	/**
	 * Returns the index of the first occurrence of the specified element in this list, or
	 * -1 if this list does not contain the element.
	 * @param mixed $o
	 * @return int
	 * @see \KM\Util\AbstractList::indexOf()
	 */
	public function indexOf($o = null) {
		if ($o == null) {
			for($i = 0; $i < $this->size; $i++) {
				if ($this->elementData[$i] == null) {
					return $i;
				}
			}
		} else {
			for($i = 0; $i < $this->size; $i++) {
				if ($o === $this->elementData[$i]) {
					return $i;
				}
			}
		}
		return -1;
	}

	/**
	 * Returns the index of the last occurrence of the specified element in this list, or
	 * -1 if this list does not contain the element.
	 * @param mixed $o
	 * @return int
	 * @see \KM\Util\AbstractList::lastIndexOf()
	 */
	public function lastIndexOf($o = null) {
		if ($o == null) {
			for($i = $this->size - 1; $i >= 0; $i--) {
				if ($this->elementData[$i] == null) {
					return $i;
				}
			}
		} else {
			for($i = $this->size - 1; $i >= 0; $i--) {
				if ($o === $this->elementData[$i]) {
					return $i;
				}
			}
		}
	}

	/**
	 * Returns an array containing all of the elements in this list in proper sequence (from first
	 * to last element); the runtime type of the returned array is that of the specified array.
	 * If the list fits in the specified array, it is returned therein. Otherwise, a new array is
	 * allocated with the runtime type of the specified array and the size of this list.
	 * @param array $a
	 * @return array
	 * @see \KM\Util\AbstractCollection::toArray()
	 */
	public function toArray(array $a = null) {
		if ($a == null) {
			return $this->toArray0();
		}
		return $this->toArray1( $a );
	}

	/**
	 * Returns an array containing all of the elements in this list
	 * in proper sequence (from first to last element).
	 *
	 * <p>The returned array will be "safe" in that no references to it are
	 * maintained by this list. (In other words, this method must allocate
	 * a new array). The caller is thus free to modify the returned array.
	 *
	 * <p>This method acts as bridge between array-based and collection-based
	 * APIs.
	 * @return array
	 * @see \KM\Util\AbstractCollection::toArray0()
	 */
	protected function toArray0() {
		return Arrays::copyOf( $this->elementData, $this->size );
	}

	/**
	 * Returns an array containing all of the elements in this list in proper
	 * sequence (from first to last element); the runtime type of the returned
	 * array is that of the specified array.
	 * If the list fits in the
	 * specified array, it is returned therein. Otherwise, a new array is
	 * allocated with the runtime type of the specified array and the size of
	 * this list.
	 *
	 * <p>If the list fits in the specified array with room to spare
	 * (i.e., the array has more elements than the list), the element in
	 * the array immediately following the end of the collection is set to
	 * <tt>null</tt>. (This is useful in determining the length of the
	 * list <i>only</i> if the caller knows that the list does not contain
	 * any null elements.)
	 * @param array $a
	 * @return array
	 * @see \KM\Util\AbstractCollection::toArray1()
	 */
	protected function toArray1(array $a) {
		if (count( $a ) < $this->size) {
			return Arrays::copyOf( $this->elementData, $this->size );
		}
		System::arraycopy( $this->elementData, 0, $a, 0, $this->size );
		if (count( $a ) > $this->size) {
			$a[$this->size] = null;
		}
		return $a;
	}
	
	/* ---------- Positional Access Operations ---------- */
	
	/**
	 * Returns the element at the specified position in this list.
	 * @param int $index
	 * @return mixed
	 */
	public function elementData($index) {
		$index = (int) $index;
		$this->rangeCheckForAdd( $index );
		return $this->elementData[$index];
	}

	/**
	 * Returns the element at the specified position in this list.
	 * @param int $index The index of the element to return.
	 * @return mixed The element at the specified position in this list.
	 * @throws IndexOutOfBoundsException
	 * @see \KM\Util\AbstractList::get()
	 */
	public function get($index) {
		$index = (int) $index;
		$this->rangeCheck( $index );
		return $this->elementData( $index );
	}

	/**
	 * Replaces the element at the specified position in this list with the specified
	 * element.
	 * @param int $index Index of the element to replace.
	 * @param mixed $element Element to be stored at the specified position.
	 * @return mixed The element previously at the specified position.
	 * @see \KM\Util\AbstractList::set()
	 */
	public function set($index, $element) {
		$this->testTypeParameters( $element );
		$index = (int) $index;
		$this->rangeCheck( $index );
		
		$oldValue = $this->elementData( $index );
		$this->elementData[$index] = $element;
		return $oldValue;
	}

	/**
	 * Appends the specified element to the end of this list.
	 * @param mixed $e
	 * @return boolean
	 * @see \KM\Util\AbstractList::add()
	 */
	public function add($e) {
		$this->testTypeParameters( $e );
		
		$this->ensureCapacityInternal( $this->size + 1 );
		$this->elementData[$this->size++] = $e;
		return true;
	}

	/**
	 * Inserts the specified element at the specified position in this list.
	 * Shifts the element currently at that position (if any) and any subsequent elements to the
	 * right (adds one to their indices).
	 * @param int $index
	 * @param mixed $element
	 * @see \KM\Util\AbstractList::addAt()
	 */
	public function addAt($index, $element) {
		$this->testTypeParameters( $element );
		$index = (int) $index;
		$this->rangeCheckForAdd( $index );
		
		$this->ensureCapacityInternal( $this->size + 1 );
		System::arraycopy( $this->elementData, $index, $this->elementData, $index + 1, $this->size - $index );
		$this->elementData[$index] = $element;
		$this->size++;
	}

	/**
	 * Removes the element at the specified position in this list.
	 * Shifts any subsequent elements to the left (subtracts one from their indices).
	 * @param int $index
	 * @return mixed
	 * @see \KM\Util\ListInterface::removeAt()
	 */
	public function removeAt($index) {
		$index = (int) $index;
		$this->rangeCheck( $index );
		
		$oldValue = $this->elementData( $index );
		
		$numMoved = $this->size - $index - 1;
		if ($numMoved > 0) {
			System::arraycopy( $this->elementData, $index + 1, $this->elementData, $index, $numMoved );
		}
		$this->elementData[--$this->size] = null;
		return $oldValue;
	}

	/**
	 * Removes a single instance of the specified element from this collection, if it is
	 * present.
	 * @param mixed $o
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::remove()
	 */
	public function remove($o = null) {
		if ($o == null) {
			for($i = 0; $i < $this->size; $i++) {
				if ($this->elementData[$i] == null) {
					$this->fastRemove( $i );
					return true;
				}
			}
		} else {
			for($i = 0; $i < $this->size; $i++) {
				if ($o === $this->elementData[$i]) {
					$this->fastRemove( $i );
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Private remove method that skips bounds checking and does not return the value removed.
	 * @param int $index
	 */
	private function fastRemove($index) {
		$index = (int) $index;
		$numMoved = $this->size - $index - 1;
		if ($numMoved > 0) {
			System::arraycopy( $this->elementData, $index + 1, $this->elementData, $index, $numMoved );
		}
		$this->elementData[--$this->size] = null;
	}

	/**
	 * Removes all of the elements from this list.
	 * The list will be empty after this call returns.
	 * @see \KM\Util\AbstractList::clear()
	 */
	public function clear() {
		$this->elementData = array();
		$this->size = 0;
	}

	/**
	 * Appends all of the elements in the specified collection to the end of
	 * this list, in the order that they are returned by the
	 * specified collection's Iterator.
	 * The behavior of this operation is
	 * undefined if the specified collection is modified while the operation
	 * is in progress. (This implies that the behavior of this call is
	 * undefined if the specified collection is this list, and this
	 * list is nonempty.)
	 * @param Collection $c The collection containing elements to be added to this list.
	 * @return boolean True if this list changed as a result of the call.
	 * @see \KM\Util\AbstractCollection::addAll()
	 */
	public function addAll(Collection $c) {
		$this->testTypeParameters( $c );
		$a = $c->toArray();
		$numNew = count( $a );
		$this->ensureCapacityInternal( $this->size + $numNew );
		System::arraycopy( $a, 0, $this->elementData, $this->size, $numNew );
		$this->size += $numNew;
		return $numNew != 0;
	}

	/**
	 * Inserts all of the elements in the specified collection into this list, starting at the
	 * specified position.
	 * Shifts the element currently at that position (if any) and any subsequent elements to the
	 * right (increases their indices). The new elements will appear in the list in the order that
	 * they are returned by the specified collection's iterator.
	 * @param int $index Index at which to insert the first element from the specified collection.
	 * @param Collection $c The collection containing elements to be added to this list.
	 * @return boolean True if this list changed as a result of the call.
	 * @see \KM\Util\AbstractList::addAllAt()
	 */
	public function addAllAt($index, Collection $c) {
		$this->testTypeParameters( $c );
		$index = (int) $index;
		$this->rangeCheckForAdd( $index );
		
		$a = $c->toArray();
		$numNew = count( $a );
		$this->ensureCapacityInternal( $this->size + $numNew );
		
		$numMoved = $this->size - $index;
		if ($numMoved > 0) {
			System::arraycopy( $this->elementData, $index, $this->elementData, $index + $numNew, $numMoved );
		}
		System::arraycopy( $a, 0, $this->elementData, $index, $numNew );
		$this->size += $numNew;
		return $numNew != 0;
	}

	/**
	 * Checks if the given index is in range.
	 * If not, throws an appropriate runtime exception. This method does *not* check if
	 * the index is negative: It is always used immediately prior to an array access,
	 * which throws an ArrayIndexOutOfBoundsException if index is negative.
	 * @param int $index
	 * @throws IndexOutOfBoundsException
	 */
	private function rangeCheck($index) {
		$index = (int) $index;
		if ($index >= $this->size) {
			throw new IndexOutOfBoundsException( $this->outOfBoundsMsg( $index ) );
		}
	}

	/**
	 * Version of rangeCheck() for add() and addAll(),
	 * @param int $index
	 * @throws IndexOutOfBoundsException
	 */
	private function rangeCheckForAdd($index) {
		$index = (int) $index;
		if ($index > $this->size || $index < 0) {
			throw new IndexOutOfBoundsException( $this->outOfBoundsMsg( $index ) );
		}
	}

	/**
	 * Constructs an IndexOutOfBoundsException detail message.
	 * Of the many possible refactorings of the error handling code, this "outlining" performs best
	 * with both server and client VMs.
	 * @param int $index
	 * @return string
	 */
	private function outOfBoundsMsg($index) {
		return 'Index: ' . $index . ', Size: ' . $this->size;
	}

	/**
	 * Removes all of this collection's elements that are also contained in the specified
	 * collection.
	 * @param Collection $c
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::removeAll()
	 */
	public function removeAll(Collection $c) {
		return $this->batchRemove( $c, false );
	}

	/**
	 * Retains only the elements in this collection that are contained in the specified
	 * collection.
	 * @param Collection $c
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::retainAll()
	 */
	public function retainAll(Collection $c) {
		$this->batchRemove( $c, true );
	}

	private function batchRemove(Collection $c, $complement) {
		$elementData = $this->elementData;
		$w = 0;
		$modified = false;
		$thrown = null;
		
		try {
			for($r = 0; $r < $this->size; $r++) {
				if ($c->contains( $elementData[$r] ) == $complement) {
					$elementData[$w++] = $elementData[$r];
				}
			}
		} catch ( \Exception $e ) {
			$thrown = $e;
			// Drop through
		}
		
		if ($r != $this->size) {
			System::arraycopy( $elementData, $r, $elementData, $w, $this->size - $r );
			$w += $this->size - $r;
		}
		if ($w != $this->size) {
			for($i = $w; $i < $this->size; $i++) {
				$elementData[$i] = null;
			}
			$this->size = $w;
			$modified = true;
		}
		if ($thrown != null) {
			throw $thrown;
		}
		return $modified;
	}

	/**
	 * Returns an iterator over the elements in this list in proper sequence.
	 * @return \KM\Util\ArrayList\Itr
	 * @see \KM\Util\AbstractList::getIterator()
	 */
	public function getIterator() {
		return new Itr( $this );
	}

	/**
	 * Returns a list iterator over the elements in this list (in proper sequence).
	 * @param int $index
	 * @throws IndexOutOfBoundsException
	 * @return \KM\Util\ArrayList\ListItr
	 * @see \KM\Util\AbstractList::listIterator()
	 */
	public function listIterator($index = 0) {
		$index = (int) $index;
		if ($index < 0 || $index > $this->size) {
			throw new IndexOutOfBoundsException();
		}
		return new ListItr( $index, $this );
	}

	/**
	 * Saves the ArrayList instance to a stream (i.e.
	 * serializes it).
	 * @param ObjectOutputStream $s
	 * @throws IOException if an I/O error occurs
	 */
	private function writeObject(ObjectOutputStream $s) {
		// Write out any serialization
		$s->defaultWriteObject();
		
		// Write out size
		$s->writeInt( $this->size );
		
		// Write out elements in proper order
		$type = ReflectionUtility::typeFor($this->typeParameters[0]);
		for($i = 0; $i < $this->size; $i++) {
			$s->writeMixed( $this->elementData[$i], $type );
		}
	}

	/**
	 * Reconstitutes the ArayList instance from a stream (i.e.
	 * deserializes it).
	 * @param ObjectInputStream $s
	 * @throws IOException if an I/O error occurs
	 * @throws ClassNotFoundException
	 */
	private function readObject(ObjectInputStream $s) {
		// Read in any serialization
		$s->defaultReadObject();
		
		$size = $s->readInt(); // Ignored
		
		if ($this->size > 0) {
			// Read in all elements in proper order
			for($i = 0; $i < $this->size; $i++) {
				if ($i == 0) {
					$e = $s->readMixed();
					$this->typeParameters = [
						ReflectionUtility::typeForValue( $e )->getTypeName()
					];
					$this->elementData[$i] = $e;
				} else {
					$this->elementData[$i] = $s->readMixed();
				}
			}
		}
	}
}
?>