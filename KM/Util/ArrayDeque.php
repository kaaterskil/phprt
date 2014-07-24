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

use KM\IO\IOException;
use KM\IO\ObjectInputStream;
use KM\IO\ObjectOutputStream;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\ClassNotFoundException;
use KM\Lang\IllegalStateException;
use KM\Lang\NullPointerException;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Lang\System;
use KM\Util\ArrayDeque\DeqIterator;
use KM\Util\ArrayDeque\DescendingIterator;

/**
 * Resizable-array implementation of the <code>Deque</code> interface.
 * Array deques have no capacity restrictions; they grow as necessary to support usage. Null
 * elements are prohibited. This class is likely to be faster than <code>Stack</code> when used as a
 * stack, and faster than <code>LinkedList</code> when used as a queue.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ArrayDeque extends AbstractCollection implements Deque, Serializable {
	
	/**
	 * The array in which the elements of the deque are stored.
	 * @Transient
	 * @var mixed[]
	 */
	public $elements;
	
	/**
	 * The index of the element at the head of the deque (which is the element that would be removed
	 * by remove() or pop()); or an arbitrary number equal to tail if the deque is empty.
	 * @Transient
	 * @var int
	 */
	public $head;
	
	/**
	 * The index at which the next element would be added to the tail of the deque (via addLast(E),
	 * add(E), or push(E)).
	 * @Transient
	 * @var int
	 */
	public $tail;
	
	/**
	 * The minimum capacity that we'll use for a newly created deque.
	 * @var int
	 */
	private static $MIN_INITIAL_CAPACITY = 8;
	
	/* ---------- Array Allocation and Resizing Utilities ---------- */
	
	/**
	 * Allocate empty array to hold the given number of elements.
	 * @param int $numElements The number of elements to hold.
	 */
	private function allocateElements($numElements) {
		$initialCapacity = self::$MIN_INITIAL_CAPACITY;
		if ($numElements >= $initialCapacity) {
			$initialCapacity |= ($initialCapacity >> 1);
			$initialCapacity |= ($initialCapacity >> 2);
			$initialCapacity |= ($initialCapacity >> 4);
			$initialCapacity |= ($initialCapacity >> 8);
			$initialCapacity |= ($initialCapacity >> 16);
			$initialCapacity++;
			
			if ($initialCapacity > PHP_INT_MAX) {
				$initialCapacity = PHP_INT_MAX - 1;
			}
		}
		$this->elements = array_fill( 0, $initialCapacity, null );
	}

	/**
	 * Doubles the capacity of this deque.
	 * Call only when full, i.e., when head and tail have wrapped around to become equal.
	 * @throws IllegalStateException
	 */
	private function doubleCapacity() {
		assert( $this->head == $this->tail );
		$p = $this->head;
		$n - count( $this->elements );
		$r = $n - $p; // THe number of elements to the right of p.
		$newCapacity = $n << 1;
		if ($newCapacity > PHP_INT_MAX || is_float( $newCapacity )) {
			throw new IllegalStateException( 'Sorry, deque too big' );
		}
		$a = array_fill( 0, $newCapacity, null );
		System::arraycopy( $this->elements, $p, $a, 0, $r );
		System::arraycopy( $this->elements, 0, $a, $r, $p );
		$this->elements = $a;
		$this->head = 0;
		$this->tail = $n;
	}

	/**
	 * Copies the elements from our element array into the specified array in order (from first to
	 * last element in the deque).
	 * It is assumed that the array is large enough to hold all the elements in the deque.
	 * @param mixed[] $a
	 * @return mixed[] ITs argument.
	 */
	private function copyElements(array &$a) {
		if ($this->head < $this->tail) {
			System::arraycopy( $this->elements, $this->head, $a, 0, $this->size() );
		} elseif ($this->head > $this->tail) {
			$headProtionLen = count( $this->elements ) - $this->head;
			System::arraycopy( $this->elements, $this->head, $a, 0, $headProtionLen );
			System::arraycopy( $this->elements, 0, $a, $headProtionLen, $this->tail );
		}
		return $a;
	}

	/**
	 * Constructs a deque containing the elements of the specified collection, in the order they are
	 * returned by the collection's iterator.
	 * (The first element returned by the collection's iterator becomes the first element, or
	 * <i>front</i> of the deque.)
	 * @param string $typeParameters
	 * @param Collection $c
	 */
	public function __construct($typeParameters = null, Collection $c = null) {
		parent::__construct( $typeParameters );
		if ($c != null) {
			$this->elements = $this->allocateElements( $c->size() );
			$this->addAll( $c );
		} else {
			$this->elements = array_fill( 0, 16, null );
		}
	}
	
	/* ---------- Main Insertion and Extraction Methods ---------- */
	
	/**
	 * Inserts the specified element at the front of this deque.
	 * @param mixed $e The element to add.
	 * @throws NullPointerException if the specified element is null.
	 * @see \KM\Util\Deque::addFirst()
	 */
	public function addFirst($e) {
		if ($e == null) {
			throw new NullPointerException();
		}
		$this->testTypeParameters( $e );
		$this->head = ($this->head - 1) & (count( $this->elements ) - 1);
		$this->elements[$this->head] = $e;
		if ($this->head == $this->tail) {
			$this->doubleCapacity();
		}
	}

	/**
	 * Inserts the specified element at the end of this deque.
	 * @param mixed $e The element to add.
	 * @throws NullPointerException if the specified element is null.
	 * @see \KM\Util\Deque::addLast()
	 */
	public function addLast($e) {
		if ($e == null) {
			throw new NullPointerException();
		}
		$this->testTypeParameters( $e );
		$this->elements[$this->tail] = $e;
		if (($this->tail = ($this->tail + 1) & (count( $this->elements ) - 1)) == $this->head) {
			$this->doubleCapacity();
		}
	}

	/**
	 * Inserts the specified element at the front of this deque.
	 * @param mixed $e The element to add.
	 * @return boolean
	 * @throws NullPointerException if the specified element is null.
	 * @see \KM\Util\Deque::offerFirst()
	 */
	public function offerFirst($e) {
		$this->addFirst( $e );
		return true;
	}

	/**
	 * Inserts the specified element at the end of this deque.
	 * @param mixed $e The element to add.
	 * @return boolean
	 * @throws NullPointerException if the specified element is null.
	 * @see \KM\Util\Deque::offerLast()
	 */
	public function offerLast($e) {
		$this->addLast( $e );
		return true;
	}

	/**
	 *
	 * @throws NoSuchElementException
	 * @return mixed
	 * @see \KM\Util\Deque::removeFirst()
	 */
	public function removeFirst() {
		$x = $this->pollFirst();
		if ($x == null) {
			throw new NoSuchElementException();
		}
		return $x;
	}

	/**
	 *
	 * @throws NoSuchElementException
	 * @return \KM\Util\mixed
	 * @see \KM\Util\Deque::removeLast()
	 */
	public function removeLast() {
		$x = $this->pollLast();
		if ($x == null) {
			throw new NoSuchElementException();
		}
		return $x;
	}

	public function pollFirst() {
		$h = $this->head;
		$result - $this->elements[$h];
		// Element is null if deque is empty.
		if ($result == null) {
			return null;
		}
		// Must null out the slot.
		$this->elements[$h] = null;
		$this->head = ($h + 1) & (count( $this->elements ) - 1);
		return $result;
	}

	public function pollLast() {
		$t = ($this->tail - 1) & (count( $this->elements ) - 1);
		$result = $this->elements[$t];
		if ($result == null) {
			return null;
		}
		$this->elements[$t] = null;
		$this->tail = $t;
		return $result;
	}

	public function getFirst() {
		$result = $this->elements[$this->head];
		if ($result == null) {
			throw new NoSuchElementException();
		}
		return $result;
	}

	public function getLast() {
		$result = $this->elements[($this->tail - 1) & (count( $this->elements ) - 1)];
		if ($result == null) {
			throw new NoSuchElementException();
		}
		return $result;
	}

	public function peekFirst() {
		return $this->elements[$this->head];
	}

	public function peekLast() {
		return $this->elements[($this->tail - 1) & (count( $this->elements ) - 1)];
	}

	/**
	 * Removes the first occurrence of the specified element in this deque (when traversing the
	 * deque from head to tail).
	 * If the deque does not contain the element, it is unchanged.
	 * @param mixed $o The element to be removed from this deque, if present.
	 * @return boolean <code>true</code> if the deque contains the specified element.
	 * @see \KM\Util\Deque::removeFirstOccurrence()
	 */
	public function removeFirstOccurrence($o) {
		if ($o == null) {
			return false;
		}
		$mask = count( $this->elements ) - 1;
		$i = $this->head;
		$x = null;
		while ( ($x = $this->elements[$i]) != null ) {
			if ($o == $x) {
				$this->delete( $i );
				return true;
			}
			$i = ($i + 1) & $mask;
		}
		return false;
	}

	/**
	 * Removes the last occurrence of the specified element in this deque (when traversing the deque
	 * from head to tail).
	 * If the deque does not contain the element, it is unchanged.
	 * @param mixed $o The element to be removed from this deque, if present.
	 * @return boolean <code>true</code> if the deque contains the specified element.
	 * @see \KM\Util\Deque::removeLastOccurrence()
	 */
	public function removeLastOccurrence($o) {
		if ($o == null) {
			return false;
		}
		$mask = count( $this->elements ) - 1;
		$i = ($this->tail - 1) & $mask;
		$x = null;
		while ( ($x = $this->elements[$i]) != null ) {
			if ($o == $x) {
				$this->delete( $i );
				return true;
			}
			$i = ($i - 1) & $mask;
		}
		return false;
	}
	
	/* ---------- Queue Methods ---------- */
	
	/**
	 * Inserts the specified element at the end of this deque.
	 * @param mixed $e The element to add.
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::add()
	 */
	public function add($e) {
		$this->addLast( $e );
		return true;
	}

	/**
	 * Inserts the specified element at the end of this deque.
	 * @param mixed $e The element to add.
	 * @return boolean
	 * @see \KM\Util\Queue::offer()
	 */
	public function offer($e) {
		return $this->offerLast( $e );
	}

	/**
	 *
	 * @param string $o
	 * @return mixed
	 * @see \KM\Util\AbstractCollection::remove()
	 */
	public function remove($o = null) {
		if ($o == null) {
			return $this->removeFirst();
		} else {
			return $this->removeFirstOccurrence( $o );
		}
	}

	/**
	 * Retrieves and removes the head of the queue represented by this deque (in other words, the
	 * first element of this deque), or returns <code>null</code> if this deque is empty.
	 * @return mixed
	 * @see \KM\Util\Queue::poll()
	 */
	public function poll() {
		return $this->pollFirst();
	}

	/**
	 * Retrieves, but does not remove, the head of the queue represented by this deque.
	 * This method differs from <code>peek</code> only in that it throws an exception if this deque
	 * is empty.
	 * @return mixed
	 * @see \KM\Util\Queue::element()
	 */
	public function element() {
		return $this->getFirst();
	}

	/**
	 * Retrieves, but does not remove, the head of the queue represented by this deque, or returns
	 * <code>null</code> if this deque is empty.
	 * @return mixed
	 * @see \KM\Util\Queue::peek()
	 */
	public function peek() {
		return $this->peekFirst();
	}
	
	/* ---------- Stack Methods ---------- */
	
	/**
	 * Pushes an element onto the stack represented by this deque.
	 * In other words, inserts the element at the front of this deque.
	 * @param mixed $e
	 * @see \KM\Util\Deque::push()
	 */
	public function push($e) {
		$this->addFirst( $e );
	}

	/**
	 * Pops an element from the stack represented by this deque.
	 * In other words, removes and returns the first element of this deque.
	 * @return mixed
	 * @see \KM\Util\Deque::pop()
	 */
	public function pop() {
		return $this->removeFirst();
	}

	private function checkInvariants() {
		assert( $this->elements[$this->tail] == null );
		assert(
			$this->head == $this->tail ? $this->elements[$this->head] == null : ($this->elements[$this->head] !=
				 null && $this->elements[($this->tail - 1) & (count( $this->elements ) - 1)] != null) );
		assert( $this->elements[($this->head - 1) & (count( $this->elements ) - 1)] == null );
	}

	/**
	 * Removes the element at the specified position in the elements array, adjusting head and tail
	 * as necessary.
	 * This can result in motion of elements backwards or forwards in the array.
	 * @param int $i
	 * @return boolean <code>true</code> if elements moved backwards.
	 */
	public function delete($i) {
		$this->checkInvariants();
		$elements = $this->elements;
		$mask = count( $this->elements ) - 1;
		$h = $this->head;
		$t = $this->tail;
		$front = ($i - $h) & $mask;
		$back = ($t - $i) & $mask;
		
		// Optimize for least element motion
		if ($front < $back) {
			if ($h <= $i) {
				System::arraycopy( $elements, $h, $elements, $h + 1, $front );
			} else {
				// Wrap around
				System::arraycopy( $elements, 0, $elements, 1, $i );
				$elements[0] = $elements[$mask];
				System::arraycopy( $elements, $h, $elements, $h + 1, $mask - $h );
			}
			$elements[$h] = null;
			$this->head = ($h + 1) & $mask;
			$this->elements = $elements;
			return false;
		} else {
			if ($i < $t) {
				// Copy the null tail as well
				System::arraycopy( $elements, $i + 1, $elements, $i, $back );
				$this->tail = $t - 1;
			} else {
				// Wrap around
				System::arraycopy( $elements, $i + 1, $elements, $i, $mask - 1 );
				$elements[$mask] = $elements[0];
				System::arraycopy( $elements, 1, $elements, 0, $t );
				$this->tail = ($t - 1) & $mask;
			}
			$this->elements = $elements;
			return true;
		}
	}
	
	/* ---------- Collection methods ---------- */
	
	/**
	 * Returns the number of elements in this deque.
	 * @return int The number of elements in this deque.
	 * @see \KM\Util\AbstractCollection::size()
	 */
	public function size() {
		return ($this->tail - $this->head) & (count( $this->elements ) - 1);
	}

	/**
	 * Returns <code>true</code> if this deque contains no elements.
	 * @return boolean <code>true</code> if this deque contains no elements.
	 * @see \KM\Util\AbstractCollection::isEmpty()
	 */
	public function isEmpty() {
		return $this->head == $this->tail;
	}

	/**
	 * Returns an iterator over the elements in this deque.
	 * The elements will be ordered from first (head) to last (tail). This is the same order that
	 * elements would be dequeued (via successive calls to <code>remove</code> or popped (via
	 * successive calls to <code>pop</code>).
	 * @return \KM\Util\Iterator
	 * @see \KM\Util\AbstractCollection::getIterator()
	 */
	public function getIterator() {
		return new DeqIterator( $this );
	}

	/**
	 *
	 * @return \KM\Util\Iterator
	 * @see \KM\Util\Deque::descendingIterator()
	 */
	public function descendingIterator() {
		return new DescendingIterator( $this );
	}

	/**
	 * Returns <code>true</code> if this deque contains the specified element.
	 * @param mized $o The object to be checked for contained in ths deque.
	 * @return boolean <code>true</code> if this deque contains the specified element.
	 * @see \KM\Util\AbstractCollection::contains()
	 */
	public function contains($o = null) {
		if ($o == null) {
			return false;
		}
		$mask = count( $this->elements ) - 1;
		$i = $this->head;
		$x = null;
		while ( ($x = $this->elements[$i]) != null ) {
			if ($o == $x) {
				return true;
			}
			$i = ($i + 1) & $mask;
		}
		return false;
	}

	/**
	 * Removes all of the elements from this deque.
	 * The deque will be empty after this call returns.
	 * @see \KM\Util\AbstractCollection::clear()
	 */
	public function clear() {
		$h = $this->head;
		$t = $this->tail;
		if ($h != $t) {
			$this->head = $this->tail = 0;
			$i = $h;
			$mask = count( $this->elements ) - 1;
			do {
				$this->elements[$i] = null;
				$i = ($i + 1) & $mask;
			} while ( $i != $t );
		}
	}

	public function toArray(array $a = null) {
		if ($a == null) {
			$a = array_fill( 0, $this->size(), null );
			return $this->copyElements( $a );
		} else {
			$size = $this->size();
			if (count( $a ) < $size) {
				$a = array_pad( $a, $size, null );
			}
			$this->copyElements( $a );
			if (count( $a ) > $size) {
				$a[$size] = null;
			}
			return $a;
		}
	}

	/**
	 * Saves the deque to a stream (i.e.
	 * serializes it).
	 * @param ObjectOutputStream $s
	 * @throws IOException if an I/O error occurs
	 */
	private function writeObject(ObjectOutputStream $s) {
		// Write out any serialization
		$s->defaultWriteObject();
		
		// Write out size
		$s->writeInt( $this->size() );
		
		// Write out elements in proper order
		$mask = count( $this->elements ) - 1;
		$type = ReflectionUtility::typeFor($this->typeParameters[0]);
		for($i = $this->head; $i != null; $i = ($i + 1) & $mask) {
			$s->writeMixed( $this->elements[$i], $type );
		}
	}

	/**
	 * Reconstitutes the deque from a stream (i.e.
	 * deserializes it).
	 * @param ObjectInputStream $s
	 * @throws IOException if an I/O error occurs
	 * @throws ClassNotFoundException
	 */
	private function readObject(ObjectInputStream $s) {
		// Read in any serialization
		$s->defaultReadObject();
		
		// Read in size
		$size = $s->readInt();
		$this->head = 0;
		$this->tail = $size;
		
		// Read in all elements in proper order
		for($i = 0; $i < $size; $i++) {
			$this->elements[$i] = $s->readMixed();
			if ($i == 0) {
				// Set type parameter from first element
				$this->typeParameters = [
					ReflectionUtility::typeForValue( $this->elements[$i] )->getTypeName()
				];
			}
		}
	}
}
?>