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
 * @category Kaaterskil
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
namespace KM\Util;

use KM\IO\ObjectOutputStream;
use KM\IO\ObjectInputStream;
use KM\IO\Serializable;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\NullPointerException;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Util\AbstractSequentialList;
use KM\Util\Collection;
use KM\Util\Deque;
use KM\Util\Iterator;
use KM\Util\LinkedList\DescendingIterator;
use KM\Util\LinkedList\ListItr;
use KM\Util\LinkedList\Node;
use KM\Util\ListIterator;
use KM\Util\ListInterface;
use KM\Util\NoSuchElementException;

/**
 * Doubly-linked list implementation of the List and Deque interfaces.
 * Implements all optional list operations, and permits all elements (including null).
 *
 * All of the operations perform as could be expected for a doubly-linked list. Operations
 * that index into the list will traverse the list from the beginning or the end,
 * whichever is closer to the specified index.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class LinkedList extends AbstractSequentialList implements Deque, Serializable {
	
	/**
	 * The number of elements in the list
	 * @var int
	 */
	protected $size = 0;
	
	/**
	 * Pointer to the first node.
	 * Invariant: (first == null && last == null) ||
	 * (first.prev == null && first.item != null)
	 * @var \KM\Util\LinkedList\Node
	 */
	protected $first;
	
	/**
	 * Pointer to the last node,
	 * Invariant: (first == null && last == null) ||
	 * (last.next == null && last.item != null)
	 * @var \KM\Util\LinkedList\Node
	 */
	protected $last;

	/**
	 * Creates a new linked list with the given type parameter and collection.
	 * @param string $typeParameter A value denoting the type parameter declared by this
	 *        GenericDeclaration object.
	 * @param Collection $c
	 */
	public function __construct($typeParameter = null, Collection $c = null) {
		parent::__construct( $typeParameter );
		if ($c != null) {
			$this->addAll( $c );
		}
	}

	public function last() {
		return ($last = $this->last) == null ? null : $last;
	}

	/**
	 * Links e as first element.
	 * @param mixed $e
	 */
	private function linkFirst($e) {
		/* @var $f Node */
		$this->testTypeParameters( $e );
		$f = $this->first;
		$newNode = new Node( null, $e, $f );
		$this->first = $newNode;
		if ($f == null) {
			$this->last = $newNode;
		} else {
			$f->prev = $newNode;
		}
		$this->size++;
		$this->modCount++;
	}

	/**
	 * Links e as last element.
	 * @param mixed $e
	 */
	public function linkLast($e) {
		/* @var $l Node */
		$this->testTypeParameters( $e );
		$l = $this->last;
		$newNode = new Node( $l, $e, null );
		$this->last = $newNode;
		if ($l == null) {
			$this->first = $newNode;
		} else {
			$l->next = $newNode;
		}
		$this->size++;
		$this->modCount++;
	}

	/**
	 * Inserts element e before non-null Node succ.
	 * @param mixed $e
	 * @param Node $succ
	 */
	public function linkBefore($e, Node $succ) {
		/* @var $pred Node */
		$this->testTypeParameters( $e );
		$pred = $succ->prev;
		$newNode = new Node( $pred, $e, $succ );
		$succ->prev = $newNode;
		if ($pred == null) {
			$this->first = $newNode;
		} else {
			$pred->next = $newNode;
		}
		$this->size++;
		$this->modCount++;
	}

	/**
	 * Unlinks non-null first node f.
	 * @param Node $f
	 * @return mixed
	 */
	private function unlinkFirst(Node $f) {
		/* @var $next Node */
		$element = $f->item;
		$next = $f->next;
		$f->item = null;
		$f->next = null;
		$this->first = $next;
		if ($next == null) {
			$this->last = null;
		} else {
			$next->prev = null;
		}
		$this->size--;
		$this->modCount++;
		return $element;
	}

	private function unlinkLast(Node $l) {
		/* @var $prev Node */
		$element = $l->item;
		$prev = $l->prev;
		$l->item = null;
		$l->prev = null;
		$this->last = $prev;
		if ($prev == null) {
			$this->first = null;
		} else {
			$prev->next = null;
		}
		$this->size--;
		$this->modCount++;
		return $element;
	}

	public function unlink(Node $x) {
		/* @var $next Node */
		/* @var $prev Node */
		$element = $x->item;
		$next = $x->next;
		$prev = $x->prev;
		
		if ($prev == null) {
			$this->first = $next;
		} else {
			$prev->next = $next;
			$x->prev = null;
		}
		
		if ($next == null) {
			$this->last = $prev;
		} else {
			$next->prev = $prev;
			$x->next = null;
		}
		
		$x->item = null;
		$this->size--;
		$this->modCount++;
		return $element;
	}

	/**
	 * Returns the first element in this list.
	 * @return mixed
	 * @throws NoSuchElementException if this list is empty.
	 * @see \KM\Util\Deque::getFirst()
	 */
	public function getFirst() {
		/* @var $f Node */
		$f = $this->first;
		if ($f == null) {
			throw new NoSuchElementException();
		}
		return $f->item;
	}

	/**
	 * Returns the last element in this list.
	 * @return mixed
	 * @throws NoSuchElementException if this list is empty.
	 * @see \KM\Util\Deque::getLast()
	 */
	public function getLast() {
		/* @var $l Node */
		$l = $this->last;
		if ($l == null) {
			throw new NoSuchElementException();
		}
		return $l->item;
	}

	/**
	 * Removes and returns the first element from this list.
	 * @return mixed
	 * @throws NoSuchElementException if this list is empty.
	 * @see \KM\Util\Deque::removeFirst()
	 */
	public function removeFirst() {
		/* @var $f Node */
		$f = $this->first;
		if ($f == null) {
			throw new NoSuchElementException();
		}
		return $this->unlinkFirst( $f );
	}

	/**
	 * Removes and returns the last element from this list.
	 * @throws NoSuchElementException if this list is empty.
	 * @return mixed
	 * @see \KM\Util\Deque::removeLast()
	 */
	public function removeLast() {
		/* @var $l Node */
		$l = $this->last;
		if ($l == null) {
			throw new NoSuchElementException();
		}
		return $this->unlinkLast( $l );
	}

	/**
	 * Inserts the specified element at the beginning of this list.
	 * @param mixed $e
	 * @see \KM\Util\Deque::addFirst()
	 */
	public function addFirst($e) {
		$this->linkFirst( $e );
	}

	/**
	 * Appends the specified element to the end of this list.
	 * @param mixed $e
	 * @see \KM\Util\Deque::addLast()
	 */
	public function addLast($e) {
		$this->linkLast( $e );
	}

	/**
	 * Returns true if this list contains the specified element.
	 * More formally, returns true if and only if this list contains at least one element
	 * e such that (o == null ? e == null : o->equals(e))
	 * @param mixed $o
	 * @return boolean true if this list contains the specified element.
	 * @see \KM\Util\AbstractCollection::contains()
	 */
	public function contains($o = null) {
		return $this->indexOf( $o ) != -1;
	}

	/**
	 * Returns the number of elements in this list.
	 * @return int
	 * @see \KM\Util\AbstractCollection::size()
	 */
	public function size() {
		return $this->size;
	}

	/**
	 * Appends the specified element to the end of this list.
	 * @param mixed $e
	 * @return boolean
	 * @see \KM\Util\AbstractSequentialList::add()
	 */
	public function add($e) {
		$this->linkLast( $e );
		return true;
	}

	/**
	 * Removes the first occurrence of the specified element from this list if it is
	 * present.
	 * IF this list does not contain the element, it is unchanged. More formally, remove
	 * the element with the lowest index i such that (o == null ? get(i) == null :
	 * o->equals(get(i))). Returns true if this list contained the specified element (or
	 * equivalently if this list changed as a result of the call).
	 * @param mixed $o
	 * @return boolean
	 * @see \KM\Util\AbstractSequentialList::remove()
	 */
	public function remove($o = null) {
		/* @var $x Node */
		if ($o == null) {
			for($x = $this->first; $x != null; $x = $x->next) {
				if ($x->item == null) {
					$this->unlink( $x );
					return true;
				}
			}
		} else {
			for($x = $this->first; $x != null; $x = $x->next) {
				if ($o == $x->item) {
					$this->unlink( $x );
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Inserts all of the elements in the specified collection into this list, starting at
	 * the specified position.
	 * Shifts the element currently at that position (if any) and any subsequent elements
	 * to the right (increases their indices). The new elements will appear in the list in
	 * the order that they are returned by the specified collection;s iterator.
	 * @param Collection $c
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::addAll()
	 */
	public function addAll(Collection $c) {
		return $this->addAllAtIndex( $this->size, $c );
	}

	/**
	 * Inserts all of the elements in the specified collection into this list starting at
	 * the specified position.
	 * Shifts the element currently at that position (if any) and any subsequent elements
	 * to the right (increases their indices). The new elements will appear in the list in
	 * the order that they are returned by the specified collection's iterator.
	 * @param int $index index at which to insert the first element from the specified
	 *        collection.
	 * @param Collection $c
	 * @throws IndexOutOfBoundsException
	 * @throws NullPointerException if the specified collection is null.
	 * @return boolean true if this list changed as a result of the call.
	 * @see \KM\Util\AbstractSequentialList::addAllByIndex()
	 */
	public function addAllAtIndex($index, Collection $c) {
		/* @var $pred Node */
		/* @var $succ Node */
		$this->testTypeParameters( $c );
		$this->checkPositionIndex( $index );
		if ($c == null) {
			throw new NullPointerException();
		}
		
		$a = $c->toArray();
		$numNew = count( $a );
		if ($numNew == 0) {
			return false;
		}
		
		$pred = null;
		$succ = null;
		if ($index == $this->size) {
			$succ = null;
			$pred = $this->last;
		} else {
			$succ = $this->node( $index );
			$pred = $succ->prev;
		}
		
		foreach ( $a as $e ) {
			$newNode = new Node( $pred, $e, null );
			if ($pred == null) {
				$this->first = $newNode;
			} else {
				$pred->next = $newNode;
			}
			$pred = $newNode;
		}
		
		if ($succ == null) {
			$this->last = $pred;
		} else {
			$pred->next = $succ;
			$succ->prev = $pred;
		}
		
		$this->size += $numNew;
		$this->modCount++;
		return true;
	}

	/**
	 * Removes all of the elements from this list.
	 * The list will be empty after the call returns.
	 * @see \KM\Util\AbstractList::clear()
	 */
	public function clear() {
		/* @var $x Node */
		/* @var $next Node */
		for($x = $this->first; $x != null;) {
			$next = $x->next;
			$x->item = null;
			$x->next = null;
			$x->prev = null;
			$x = $next;
		}
		$this->first = $this->last = null;
		$this->size = 0;
		$this->modCount++;
	}
	
	/* ---------- Positional Access Operations ---------- */
	
	/**
	 * Returns the element at the specified position in this list.
	 * @param int $index
	 * @return mixed
	 * @see \KM\Util\AbstractSequentialList::get()
	 */
	public function get($index) {
		$this->checkElementIndex( $index );
		return $this->node( $index )->item;
	}

	/**
	 * Replaces the element at the specified position in this list with the specified
	 * element.
	 * @param int $index index of the element to replace.
	 * @param mixed $element the element to be stored at the specified position
	 * @return mixed the element previously at the specified position.
	 * @see \KM\Util\AbstractSequentialList::set()
	 */
	public function set($index, $element) {
		/* @var $x Node */
		$this->testTypeParameters( $element );
		$this->checkElementIndex( $index );
		
		$x = $this->node( $index );
		$oldVal = $x->item;
		$x->item = $element;
		return $oldVal;
	}

	/**
	 * Inserts the specified element at the specified position in this list.
	 * Shifts the element currently at that position (if any) and any subsequent element
	 * to the right (increases their indices).
	 * @param int $index index at which the specified element is to be inserted.
	 * @param mixed $e element to be inserted.
	 * @see \KM\Util\AbstractSequentialList::addAt()
	 */
	public function addAt($index, $e) {
		$this->testTypeParameters( $e );
		$this->checkPositionIndex( $index );
		
		if ($index == $this->size) {
			$this->linkLast( $e );
		} else {
			$this->linkBefore( $e, $this->node( $index ) );
		}
	}

	/**
	 * Removes the element at the specified position in this list.
	 * Shifts any subsequent elements to the left (subtracts one from their indices).
	 * Returns the element that was removed from the list.
	 *
	 * If the specified position is null, this method retrieves and removes the head of
	 * this list. This is included here since Java overloads the same method name.
	 * @param int $index
	 * @return mixed
	 * @see \KM\Util\AbstractSequentialList::removePosition(index)
	 */
	public function removeAt($index) {
		if ($index == null) {
			return $this->removeFirst();
		}
		$index = (int) $index;
		$this->checkElementIndex( $index );
		return $this->unlink( $this->node( $index ) );
	}

	/**
	 * Tells if the argument is the index of an existing element.
	 * @param int $index
	 * @return boolean
	 */
	private function isElementIndex($index) {
		return (($index >= 0) && ($index <= $this->size));
	}

	/**
	 * Tells if the argument is the index of a valid position for an iterator or an add
	 * operation.
	 * @param int $index
	 * @return boolean
	 */
	private function isPositionIndex($index) {
		return (($index >= 0) && ($index <= $this->size));
	}

	/**
	 * Contains an IndexOutOfBoundsException detail message.
	 * @param int $index
	 * @return string
	 */
	private function outOfBoundsMsg($index) {
		return 'Index: ' . $index . ', Size: ' . $this->size;
	}

	private function checkElementIndex($index) {
		if (!$this->isElementIndex( $index )) {
			throw new IndexOutOfBoundsException( $this->outOfBoundsMsg( $index ) );
		}
	}

	private function checkPositionIndex($index) {
		if (!$this->isPositionIndex( $index )) {
			throw new IndexOutOfBoundsException( $this->outOfBoundsMsg( $index ) );
		}
	}

	/**
	 * Returns the non-null node at the specified element index.
	 * @param int $index
	 * @return \KM\Util\Node
	 */
	public function node($index) {
		/* @var $x Node */
		$index = (int) $index;
		if ($index < ($this->size >> 1)) {
			$x = $this->first;
			for($i = 0; $i < $index; $i++) {
				$x = $x->next;
			}
			return $x;
		} else {
			$x = $this->last;
			for($i = $this->size - 1; $i > $index; $i--) {
				$x = $x->prev;
			}
			return $x;
		}
	}
	
	/* ---------- Search Operations ---------- */
	
	/**
	 * Returns the index of the first occurrence of the specified element in this list, or
	 * -1 if this list does not contain the element.
	 * @param mixed $o the element to search for.
	 * @return int
	 * @see \KM\Util\AbstractList::indexOf()
	 */
	public function indexOf($o = null) {
		/* @var $x Node */
		$index = 0;
		if ($o == null) {
			for($x = $this->first; $x != null; $x = $x->next) {
				if ($x->item == null) {
					return $index;
				}
				$index++;
			}
		} else {
			for($x = $this->first; $x != null; $x = $x->next) {
				if ($o == $x->item) {
					return $index;
				}
				$index++;
			}
		}
		return -1;
	}

	/**
	 * Returns the index of the last occurrence of the specified element in this list, or
	 * -1 if this list does not contain the element.
	 * @param mixed $o the element to search for.
	 * @return int
	 * @see \KM\Util\AbstractList::lastIndexOf()
	 */
	public function lastIndexOf($o = null) {
		/* @var $x Node */
		$index = $this->size;
		if ($o == null) {
			for($x = $this->last; $x != null; $x = $x->prev) {
				$index--;
				if ($x->item == null) {
					return $index;
				}
			}
		} else {
			for($x = $this->last; $x != null; $x = $x->prev) {
				$index--;
				if ($o == $x->item) {
					return $index;
				}
			}
		}
		return -1;
	}
	
	/* ---------- Queue Operations ---------- */
	
	/**
	 * Retrieves but does not remove, the head (first element) of this list.
	 * @return mixed The head of this list, or <code>null</code> if this list is empty.
	 * @see \KM\Util\Queue::peek()
	 */
	public function peek() {
		/* @var $f Node */
		$f = $this->first;
		return ($f == null) ? null : $f->item;
	}

	/**
	 * Retrieves, but does not remove, the head (first element) of this list.
	 * @return mixed The head of this list.
	 * @see \KM\Util\Queue::element()
	 */
	public function element() {
		return $this->getFirst();
	}

	/**
	 * Retrieves and removes the head (first element) of this list.
	 * @return mixed The head of this list, or <code>null</code> if this list is empty.
	 * @see \KM\Util\Queue::poll()
	 */
	public function poll() {
		/* @var $f Node */
		$f = $this->first;
		return ($f == null) ? null : $this->unlinkFirst( $f );
	}

	/**
	 * Adds the specified element as the tail of this list.
	 * @param mixed $e The element to add.
	 * @return boolean
	 * @see \KM\Util\Queue::offer()
	 */
	public function offer($e) {
		return $this->add( $e );
	}
	
	/* ---------- Deque Operations ---------- */
	
	/**
	 * Inserts the specified element at the front of this list.
	 * @param mixed $e The element to insert.
	 * @return boolean
	 * @see \KM\Util\Deque::offerFirst()
	 */
	public function offerFirst($e) {
		$this->addFirst( $e );
		return true;
	}

	/**
	 * Inserts the specified element at the end of this list.
	 * @param mixed $e The element to insert.
	 * @return boolean
	 * @see \KM\Util\Deque::offerLast()
	 */
	public function offerLast($e) {
		$this->addLast( $e );
		return true;
	}

	/**
	 * Retrieves, but does not remove, the first element of this list, or returns null if
	 * this list is empty.
	 * @return mixed The first element of this list, or <code>null</code> if this list is empty.
	 * @see \KM\Util\Deque::peekFirst()
	 */
	public function peekFirst() {
		/* @var $f Node */
		$f = $this->first;
		return ($f == null) ? null : $f->item;
	}

	/**
	 * Retrieves, but odes not remove, the last element of this list, or null if this list
	 * is empty.
	 * @return mixed The last element of this list, or <code>null</code> if this list is empty.
	 * @see \KM\Util\Deque::peekLast()
	 */
	public function peekLast() {
		/* @var $l Node */
		$l = $this->last;
		return ($l == null) ? null : $l->item;
	}

	/**
	 * Retrieves and removes the first element of this list, or returns null if this list
	 * is empty.
	 * @return mixed The first element of this list, or <code>null</code> if this list is empty.
	 * @see \KM\Util\Deque::pollFirst()
	 */
	public function pollFirst() {
		/* @var $f Node */
		$f = $this->first;
		return ($f == null) ? null : $this->unlinkFirst( $f );
	}

	/**
	 * Retrieves and removes the last element of this list, or returns null if this list
	 * is empty.
	 * @return mixed The last element of this list, or <code>null</code> if this list is empty.
	 * @see \KM\Util\Deque::pollLast()
	 */
	public function pollLast() {
		/* @var $l Node */
		$l = $this->last;
		return ($l == null) ? null : $this->unlinkLast( $l );
	}

	/**
	 * Pushes the element onto the stack represented by this list.
	 * In other words, inserts the element at the front of this list.
	 * @param mixed $e
	 * @see \KM\Util\Deque::push()
	 */
	public function push($e) {
		$this->addFirst( $e );
	}

	/**
	 * Pops an element from the stack represented by this list.
	 * In other words, removes and returns the first element of this list.
	 * @return mixed
	 * @see \KM\Util\Deque::pop()
	 */
	public function pop() {
		return $this->removeFirst();
	}

	/**
	 * Removes the first occurrence of the specified element in this list (when traversing
	 * the list from head to tail).
	 * If the list does not contain the element, it is unchanged.
	 * @param mixed $o
	 * @return boolean
	 * @see \KM\Util\Deque::removeFirstOccurrence()
	 */
	public function removeFirstOccurrence($o) {
		return $this->remove( $o );
	}

	/**
	 * Removes the last occurrence of the specified element in this list (when traversing
	 * this list from tail to head).
	 * If the list does not contain the element, it is unchanged.
	 * @param mixed $o
	 * @return true if the list contained the specified element.
	 * @see \KM\Util\Deque::removeLastOccurrence()
	 */
	public function removeLastOccurrence($o) {
		/* @var $x Node */
		if ($o == null) {
			for($x = $this->last; $x != null; $x = $x->prev) {
				if ($x->item == null) {
					$this->unlink( $x );
					return true;
				}
			}
		} else {
			for($x = $this->last; $x != null; $c = $x->prev) {
				if ($o == $x->item) {
					$this->unlink( $x );
					return true;
				}
			}
		}
		return false;
	}
	
	/* ---------- Other Methods ---------- */
	
	/**
	 * Returns a list iterator of the elements in this list (in proper sequence), starting
	 * at the specified position in the list.mObeys the general contract of
	 * List->listIterator(int).
	 * @param int $index index of the first element to be returned from the list iterator
	 *        (by a call to next()).
	 * @return \KM\Util\ListInterator
	 * @see \KM\Util\AbstractSequentialList::listIterator()
	 */
	public function listIterator($index = 0) {
		$this->checkPositionIndex( $index );
		return new ListItr( $index, $this );
	}

	/**
	 * Returns a descending iterator starting at the tail of this list.
	 * @return \KM\Util\Iterator
	 * @see \KM\Util\Deque::descendingIterator()
	 */
	public function descendingIterator() {
		return new DescendingIterator( $this );
	}

	/**
	 * Returns an array containing all of the elements of this list in proper sequence
	 * (from first to last element).
	 *
	 * If an array is specified and the list fits within the specified array, it is
	 * returned therein. If no array is specified, or the list does not fit within a
	 * specified array, a new array is allocated with the size of this list.
	 *
	 * If the array is specified and the list fits within it with room to spare (that is,
	 * the array has more elements than the list), the element in the array immediately
	 * following the end of the list is set to null. This is useful in determining the
	 * length of the list only if the caller knows that the list does not contain any null
	 * elements.
	 * @param mixed[] $a the array into which the elements of the list are to be stored.
	 *        Otherwise, a new array is allocated for this purpose.
	 * @return mixed[]
	 * @see \KM\Util\AbstractCollection::toArray()
	 */
	public function toArray(array $a = null) {
		/* @var $x Node */
		if ($a == null) {
			$result = array();
			$i = 0;
			for($x = $this->first; $x != null; $x = $x->next) {
				$result[$i++] = $x->item;
			}
			return $result;
		} else {
			if (count( $a ) < $this->size) {
				$a = array_fill( 0, $this->size, null );
			}
			
			$result = $a;
			$i = 0;
			for($x = $this->first; $x != null; $x = $x->next) {
				$result[$i++] = $x->item;
			}
			if (count( $a ) > $this->size) {
				$a[$this->size] = null;
			}
			return $a;
		}
	}

	private function writeObject(ObjectOutputStream $s) {
		// Write out any serialization
		$s->defaultWriteObject();
		
		// Write out size
		$s->writeInt( $this->size );
		
		// Write out all elements in the proper order
		$type = ReflectionUtility::typeFor($this->typeParameters[0]);
		for($x = $this->first; $x != null; $x = $x->next) {
			$s->writeObject( $x->item, $type );
		}
	}

	private function readObject(ObjectInputStream $s) {
		// Read in any serialization
		$s->defaultReadObject();
		
		// Read in size
		$size = $s->readInt();
		
		// Read in all elements in the proper order
		for($i = 0; $i < $size; $i++) {
			if ($i == 0) {
				$e = $s->readMixed();
				$this->typeParameters = [
					ReflectionUtility::typeForValue( $e )->getTypeName()
				];
				$this->linkLast( $e );
			} else {
				$this->linkLast( $s->readMixed() );
			}
		}
	}
}
?>