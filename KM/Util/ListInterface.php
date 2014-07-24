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

use KM\Lang\Object;
use KM\Util\ListIterator;

/**
 * An ordered collection (also known as a sequence).
 * The user of this interface has precise control over where in the list each element is
 * inserted. The user can access elements by their integer index (position in the list),
 * and search for elements in the list.
 *
 * Unlike sets, lists typically allow duplicate elements. More formally, lists typically
 * allow pairs of elements e1 and e2 such that e1.equals(e2), and they typically allow
 * multiple null elements if they allow null elements at all. It is not inconceivable that
 * someone might wish to implement a list that prohibits duplicates, by throwing runtime
 * exceptions when the user attempts to insert them, but we expect this usage to be rare.
 *
 * The List interface places additional stipulations, beyond those specified in the
 * Collection interface, on the contracts of the iterator, add, remove, equals, and
 * hashCode methods. Declarations for other inherited methods are also included here for
 * convenience.
 *
 * The List interface provides four methods for positional (indexed) access to list
 * elements. Lists (like Java arrays) are zero based. Note that these operations may
 * execute in time proportional to the index value for some implementations (the
 * LinkedList class, for example). Thus, iterating over the elements in a list is
 * typically preferable to indexing through it if the caller does not know the
 * implementation.
 *
 * The List interface provides a special iterator, called a ListIterator, that allows
 * element insertion and replacement, and bidirectional access in addition to the normal
 * operations that the Iterator interface provides. A method is provided to obtain a list
 * iterator that starts at a specified position in the list.
 *
 * The List interface provides two methods to search for a specified object. From a
 * performance standpoint, these methods should be used with caution. In many
 * implementations they will perform costly linear searches.
 *
 * The List interface provides two methods to efficiently insert and remove multiple
 * elements at an arbitrary point in the list.
 *
 * Note: While it is permissible for lists to contain themselves as elements, extreme
 * caution is advised: the equals and hashCode methods are no longer well defined on such
 * a list.
 *
 * Some list implementations have restrictions on the elements that they may contain. For
 * example, some implementations prohibit null elements, and some have restrictions on the
 * types of their elements. Attempting to add an ineligible element throws an unchecked
 * exception, typically NullPointerException or ClassCastException. Attempting to query
 * the presence of an ineligible element may throw an exception, or it may simply return
 * false; some implementations will exhibit the former behavior and some will exhibit the
 * latter. More generally, attempting an operation on an ineligible element whose
 * completion would not result in the insertion of an ineligible element into the list may
 * throw an exception or it may succeed, at the option of the implementation. Such
 * exceptions are marked as "optional" in the specification for this interface.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface ListInterface extends Collection {
	
	/* ---------- Query Operations ---------- */
	
	/**
	 * Returns the number of elements in this list.
	 * @return int
	 * @see \KM\Util\Collection::size()
	 */
	public function size();

	/**
	 * Returns true of this list contains no elements.
	 * @return boolean
	 * @see \KM\Util\Collection::isEmpty()
	 */
	public function isEmpty();

	/**
	 * Returns true if this list contains the specified element.
	 * @param mixed $o
	 * @see \KM\Util\Collection::contains()
	 */
	public function contains($o = null);

	/**
	 * Returns an iterator over the elements in this list in proper sequence.
	 * @return \KM\Util\Iterator
	 * @see \KM\Util\Collection::getIterator()
	 */
	public function getIterator();

	/**
	 * Returns an array containing all of the elements in this list in proper sequence.
	 * If the specified array is not null and if the list fits into the specified array, it is
	 * returned therein.
	 * @param array $a
	 * @return array
	 * @see \KM\Util\Collection::toArray()
	 */
	public function toArray(array $a = null);
	
	/* ---------- Modification Operations ---------- */
	
	/**
	 * Appends the specified element to the end of this list.
	 * @param mixed $e
	 * @return boolean
	 * @see \KM\Util\Collection::add()
	 */
	public function add($e);

	/**
	 * Removes the first occurrence of the specified element from this list.
	 * @param mixed $o
	 * @return boolean
	 * @see \KM\Util\Collection::remove()
	 */
	public function remove($o = null);
	
	/* ---------- Bulk Modification Operations ---------- */
	
	/**
	 * Returns true if this list contains all of the elements of the specified collection.
	 * @param Collection $c
	 * @return boolean
	 * @see \KM\Util\Collection::containsAll()
	 */
	public function containsAll(Collection $c);

	/**
	 * Appends all of the elements of the specified collection to the end of this list.
	 * @param Collection $c
	 * @return boolean
	 * @see \KM\Util\Collection::addAll()
	 */
	public function addAll(Collection $c);

	/**
	 * Inserts all of the elements of the specified collection into this list at the specified
	 * position.
	 * @param int $index
	 * @param Collection $c
	 */
	public function addAllAt($index, Collection $c);

	/**
	 * Removes from this list all of its elements that are contained in the specified collection.
	 * @param Collection $c
	 * @return boolean
	 * @see \KM\Util\Collection::removeAll()
	 */
	public function removeAll(Collection $c);

	/**
	 * Retains only the elements in this list that are contained in the specified collection.
	 * @param Collection $c
	 * @return boolean
	 * @see \KM\Util\Collection::retainAll()
	 */
	public function retainAll(Collection $c);

	/**
	 * Removes all of the elements from this list.
	 * @see \KM\Util\Collection::clear()
	 */
	public function clear();
	
	/* ---------- Positional Access Operations ---------- */
	
	/**
	 * Returns the element at the specified position in this list.
	 * @param int $index
	 * @return mixed
	 */
	public function get($index);

	/**
	 * Replaces the element at the specified position in this list with the specified
	 * element (optional operation).
	 * @param int $index
	 * @param mixed $element
	 * @return mixed The element previously at the specified position
	 */
	public function set($index, $element);

	/**
	 * Inserts the specified element at the specified position in this list.
	 * @param int $index
	 * @param mixed $element
	 */
	public function addAt($index, $element);

	/**
	 * Removes the element at the specified position in this list (optional operation).
	 * Shifts any subsequent elements to the left (subtracts one from their indices). Returns the
	 * element that was removes from the list.
	 * @param int $index The index of the element being removed.
	 * @return mixed The element that was removed.
	 */
	public function removeAt($index);
	
	/* ---------- Search Operations ---------- */

	/**
	 * Returns the index of the first occurrence of the specified element in this list, or
	 * -1 if this list does not contain the element.
	 * More formally, returns the lowest index i such that (o==null ? get(i)==null :
	 * o.equals(get(i))), or -1 if there is no such index.
	 * @param mixed $o
	 * @return int
	 */
	public function indexOf($o = null);

	/**
	 * Returns the index of the last occurrence of the specified element in this list, or
	 * -1 if this list does not contain the element.
	 * More formally, returns the highest index i such that (o==null ? get(i)==null :
	 * o.equals(get(i))), or -1 if there is no such index.
	 * @param mixed $o
	 * @return int
	 */
	public function lastIndexOf($o = null);

	/**
	 * Returns a list iterator over the elements in this list (in proper sequence),
	 * starting at the specified position in the list.
	 * The specified index indicates the first element that would be returned by an
	 * initial call to next. An initial call to previous would return the element with the
	 * specified index minus one.
	 * @return ListIterator
	 */
	public function listIterator($index = 0);
}
?>