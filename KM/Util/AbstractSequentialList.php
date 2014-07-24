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

use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\Object;
use KM\Util\Iterator;
use KM\Lang\NullPointerException;
use KM\Lang\UnsupportedOperationException;

/**
 * This class provides a skeletal implementation of the List interface to minimize the
 * effort required to implement this interface backed by a "sequential access" data store
 * (such as a linked list).
 * For random access data (such as an array), AbstractList should be used in preference to
 * this class.
 *
 * This class is the opposite of the AbstractList class in the sense that it implements
 * the "random access" methods (get(int index), set(int index, E element), add(int index,
 * E element) and remove(int index)) on top of the list's list iterator, instead of the
 * other way around.
 *
 * To implement a list the programmer needs only to extend this class and provide
 * implementations for the listIterator and size methods. For an unmodifiable list, the
 * programmer need only implement the list iterator's hasNext, next, hasPrevious, previous
 * and index methods.
 *
 * For a modifiable list the programmer should additionally implement the list iterator's
 * set method. For a variable-size list the programmer should additionally implement the
 * list iterator's remove and add methods.
 *
 * The programmer should generally provide a void (no argument) and collection
 * constructor, as per the recommendation in the Collection interface specification.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class AbstractSequentialList extends AbstractList {

	/**
	 * Sole constructor, with the given type parameter.
	 * @param string $typeParameter A value denoting the type parameter declared by this
	 *        GenericDeclaration object.
	 */
	protected function __construct($typeParameter = null) {
		parent::__construct( $typeParameter );
	}

	/**
	 * Needed for PHP since nested classes are not permitted.
	 * @return int
	 */
	public function getModCount() {
		return $this->modCount;
	}

	/**
	 * Returns the element at the specified position in this list.
	 * This implementation first gets a list iterator pointing to the indexed element
	 * (with $this->listIterator($index)). Then it gets the element using
	 * ListIterator->next() and returns it.
	 * @param int $index
	 * @throws IndexOutOfBoundsException
	 * @return mixed
	 * @see \KM\Util\AbstractList::get()
	 */
	public function get($index) {
		$index = (int) $index;
		try {
			return $this->listIterator( $index )->next();
		} catch ( NoSuchElementException $nsee ) {
			throw new IndexOutOfBoundsException( 'Index: ' . $index );
		}
	}

	/**
	 * Replaces the element at the specified position in this list with the specified
	 * element.
	 * This implementation first gets a list iterator pointing to the indexed element.
	 * Then it gets the current element using ListIterator->next() and replaces it with
	 * ListIterator->set().
	 * @param int $index
	 * @param mixed $element
	 * @throws IndexOutOfBoundsException
	 * @return mixed
	 * @see \KM\Util\AbstractList::set()
	 */
	public function set($index, $element) {
		$this->testTypeParameters($element);
		try {
			$e = $this->listIterator( $index );
			$oldVal = $e->next();
			$e->set( $element );
			return $oldVal;
		} catch ( NoSuchElementException $nsee ) {
			throw new IndexOutOfBoundsException( 'Index: ' . $index );
		}
	}

	/**
	 * Inserts the specified element at the specified position in this list.
	 * Shifts the element currently at that position (if any) and any subsequent element
	 * to the right (adds one to their indices). This implementation first gets a list
	 * iterator pointing to the indexed element. Then it inserts the specified element
	 * with ListIterator->add().
	 * @param int $index
	 * @param mixed $element
	 * @throws IndexOutOfBoundsException
	 * @see \KM\Util\AbstractList::addAt()
	 */
	public function addAt($index, $element) {
		$this->testTypeParameters($element);
		try {
			$this->listIterator( $index )->add( $element );
		} catch ( NoSuchElementException $nsee ) {
			throw new IndexOutOfBoundsException( 'Index: ' . $index );
		}
	}

	/**
	 * Removes the element at the specified position in this list.
	 * Shifts any subsequent element to the left (subtracts one from their indices).
	 * Returns the element that was removed from the list.
	 * @param int $index
	 * @throws NullPointerException if the given index is null.
	 * @throws IndexOutOfBoundsException
	 * @return mixed
	 * @see \KM\Util\AbstractList::remove()
	 */
	public function removeAt($index) {
		if($index == null) {
			throw new NullPointerException();
		}
		$index = (int) $index;
		try {
			$e = $this->listIterator( $index );
			$outCast = $e->next();
			$e->remove();
			return $outCast;
		} catch ( NoSuchElementException $nsee ) {
			throw new IndexOutOfBoundsException( 'Index: ' . $index );
		}
	}

	/**
	 * Inserts all of the elements in the specified collection into this list at the
	 * specified position.
	 * Shifts the element currently at that position (if any) and any subsequent elements
	 * to the right (increases their indices). The new elements will appear in this list
	 * in the order that they are returned by the specified collection's iterator. The
	 * behavior of this operation is undefined if the specified collection is modified
	 * while the operation is in progress. (Note that this will occur is the specified
	 * collection is this list, and it is nonempty.)
	 * @param int $index
	 * @param Collection $c
	 * @throws IndexOutOfBoundsException
	 * @return boolean
	 * @see \KM\Util\AbstractList::addAllAt()
	 */
	public function addAllAt($index, Collection $c) {
		/* @var $e1 \KM\Util\ListHelpers\ListItr */
		/* @var $e2 Iterator */
		try {
			$isModified = false;
			$e1 = $this->listIterator( $index );
			$e2 = $c->iterator();
			while ( $e2->hasNext() ) {
				$e1->add( $e2->next() );
				$isModified = true;
			}
			return $isModified;
		} catch ( NoSuchElementException $nsee ) {
			throw new IndexOutOfBoundsException( 'Index: ' . $index );
		}
	}
	
	/* ---------- Iterators ---------- */
	
	/**
	 * Returns an iterator over the element in this list (in proper sequence).
	 * This implementation merely returns a list iterator over the list.
	 * @return \KM\Util\Iterator
	 * @see \KM\Util\AbstractList::getIterator()
	 */
	public function getIterator() {
		return $this->listIterator();
	}

	/**
	 * Returns a list iterator over the element in this list (in proper sequence).
	 * @param int $index
	 * @see \KM\Util\AbstractList::listIterator()
	 */
	public function listIterator($index = 0) {
		throw new UnsupportedOperationException();
	}
}
?>