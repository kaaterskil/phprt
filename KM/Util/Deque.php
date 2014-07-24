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

use KM\Lang\IllegalStateException;
use KM\Lang\ClassCastException;
use KM\Lang\NullPointerException;
use KM\Util\Iterator;
/**
 * A linear collection that supports element insertion and removal at both ends.
 * The name deque is short for "double ended queue" and is usually pronounced "deck". Most
 * Deque implementations place no fixed limits on the number of elements they may contain,
 * but this interface supports capacity-restricted deques as well as those with no fixed
 * size limit.
 *
 * This interface defines methods to access the elements at both ends of the deque.
 * Methods are provided to insert, remove, and examine the element. Each of these methods
 * exists in two forms: one throws an exception if the operation fails, the other returns
 * a special value (either null or false, depending on the operation). The latter form of
 * the insert operation is designed specifically for use with capacity-restricted Deque
 * implementations; in most implementations, insert operations cannot fail.
 *
 * Note that the peek method works equally well when a deque is used as a queue or a
 * stack; in either case, elements are drawn from the beginning of the deque.
 *
 * This interface provides two methods to remove interior elements, removeFirstOccurrence
 * and removeLastOccurrence.
 *
 * Unlike the List interface, this interface does not provide support for indexed access
 * to elements.
 *
 * While Deque implementations are not strictly required to prohibit the insertion of null
 * elements, they are strongly encouraged to do so. Users of any Deque implementations
 * that do allow null elements are strongly encouraged not to take advantage of the
 * ability to insert nulls. This is so because null is used as a special return value by
 * various methods to indicated that the deque is empty.
 *
 * Deque implementations generally do not define element-based versions of the equals and
 * hashCode methods, but instead inherit the identity-based versions from class Object.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface Deque extends Queue {

	/**
	 * Inserts the specified element at the front of this deque if it is possible to do so
	 * immediately without violating capacity restrictions.
	 * When using a capacity-restricted deque, it is generally preferable to use method
	 * offerFirst(E).
	 * @param mixed $e
	 * @throws IllegalStateException if the element cannot be added at this time due to
	 *         capacity restrictions.
	 * @throws ClassCastException if the class of the specified element prevents it from
	 *         being added to this deque.
	 * @throws NullPointerException if the specified element is null and this deque does
	 *         not permit null elements.
	 * @throws \InvalidArgumentException if some property of the specified element
	 *         prevents it from being added to this deque.
	 */
	public function addFirst($e);

	/**
	 * Inserts the specified element at the end of this deque if it is possible to do so
	 * immediately without violating capacity restrictions.
	 * When using a capacity-restricted deque, it is generally preferable to use method
	 * offerLast(E). This method is equivalent to add(E).
	 * @param mixed $e
	 * @throws IllegalStateException if the element cannot be added at this time due to
	 *         capacity restrictions.
	 * @throws ClassCastException if the class of the specified element prevents it from
	 *         being added to this deque.
	 * @throws NullPointerException if the specified element is null and this deque does
	 *         not permit null elements.
	 * @throws \InvalidArgumentException if some property of the specified element
	 *         prevents it from being added to this deque.
	 */
	public function addLast($e);

	/**
	 * Inserts the specified element at the front of this deque unless it would violate
	 * capacity restrictions.
	 * When using a capacity-restricted deque, this method is generally preferable to the
	 * addFirst(E) method, which can fail to insert an element only by throwing an
	 * exception.
	 * @param mixed $e
	 * @return boolean
	 * @throws ClassCastException if the class of the specified element prevents it from
	 *         being added to this deque.
	 * @throws NullPointerException if the specified element is null and this deque does
	 *         not permit null elements.
	 * @throws \InvalidArgumentException if some property of the specified element
	 *         prevents it from being added to this deque.
	 */
	public function offerFirst($e);

	/**
	 * Inserts the specified element at the end of this deque unless it would violate
	 * capacity restrictions.
	 * When using a capacity-restricted deque, this method is generally preferable to the
	 * addLast(E) method, which can fail to insert an element only by throwing an
	 * exception.
	 * @param mixed $e
	 * @return boolean
	 * @throws ClassCastException if the class of the specified element prevents it from
	 *         being added to this deque.
	 * @throws NullPointerException if the specified element is null and this deque does
	 *         not permit null elements.
	 * @throws \InvalidArgumentException if some property of the specified element
	 *         prevents it from being added to this deque.
	 */
	public function offerLast($e);

	/**
	 * Retrieves and removes the first element of this deque.
	 * This method differs from pollFirst only in that it throws an exception if this
	 * deque is empty.
	 * @return mixed the head of this deque.
	 * @throws NoSuchElementException if this deque is empty
	 */
	public function removeFirst();

	/**
	 * Retrieves and removes the last element of this deque.
	 * This method differs from pollLast only in that it throws an exception if this deque
	 * is empty.
	 * @return mixed the tail of this deque.
	 * @throws NoSuchElementException if this deque is empty
	 */
	public function removeLast();

	/**
	 * Retrieves and removes the first element of this deque, or returns null if this
	 * deque is empty.
	 * @return mixed the head of this deque, or null if this deque is empty.
	 */
	public function pollFirst();

	/**
	 * Retrieves and removes the last element of this deque, or returns null if this deque
	 * is empty.
	 * @return mixed the tail of this deque, or null if this deque is empty.
	 */
	public function pollLast();

	/**
	 * Retrieves, but does not remove, the first element of this deque.
	 * This method differs from peekFirst only in that it throws an exception if this
	 * deque is empty.
	 * @return mixed the head of this deque.
	 */
	public function getFirst();

	/**
	 * Retrieves, but does not remove, the last element of this deque.
	 * This method differs from peekLast only in that it throws an exception if this deque
	 * is empty.
	 * @return mixed the tail of this deque.
	 */
	public function getLast();

	/**
	 * Retrieves, but does not remove, the first element of this deque, or returns null if
	 * this deque is empty.
	 * @return mixed the head of this deque, or null if this deque is empty.
	 */
	public function peekFirst();

	/**
	 * Retrieves, but does not remove, the last element of this deque, or returns null if
	 * this deque is empty.
	 * @return mixed the tail of this deque, or null if this deque is empty.
	 */
	public function peekLast();

	/**
	 * Removes the first occurrence of the specified element from this deque.
	 * If the deque does not contain the element, it is unchanged. More formally, removes
	 * the first element e such that (o==null ? e==null : o.equals(e)) (if such an element
	 * exists). Returns true if this deque contained the specified element (or
	 * equivalently, if this deque changed as a result of the call).
	 * @param mixed $o
	 * @return boolean
	 * @throws ClassCastException if the class of the specified element is incompatible
	 *         with this deque (optional).
	 * @throws NullPointerException if the specified element is null and this deque does
	 *         not permit null elements (optional).
	 */
	public function removeFirstOccurrence($o);

	/**
	 * Removes the last occurrence of the specified element from this deque.
	 * If the deque does not contain the element, it is unchanged. More formally, removes
	 * the last element e such that (o==null ? e==null : o.equals(e)) (if such an element
	 * exists). Returns true if this deque contained the specified element (or
	 * equivalently, if this deque changed as a result of the call).
	 * @param mixed $o
	 * @return boolean
	 * @throws ClassCastException if the class of the specified element is incompatible
	 *         with this deque (optional).
	 * @throws NullPointerException if the specified element is null and this deque does
	 *         not permit null elements (optional).
	 */
	public function removeLastOccurrence($o);

	/**
	 * Pushes an element onto the stack represented by this deque (in other words, at the
	 * head of this deque) if it is possible to do so immediately without violating
	 * capacity restrictions, returning true upon success and throwing an
	 * IllegalStateException if no space is currently available.
	 * This method is equivalent to addFirst(E).
	 * @param mixed $e
	 * @throws IllegalStateException if the element cannot be added at this time due to
	 *         capacity restrictions.
	 * @throws ClassCastException if the class of the specified element prevents it from
	 *         being added to this deque.
	 * @throws NullPointerException if the specified element is null and this deque does
	 *         not permit null elements.
	 * @throws \InvalidArgumentException if some property of the specified element
	 *         prevents it from being added to this deque.
	 */
	public function push($e);

	/**
	 * Pops an element from the stack represented by this deque.
	 * In other words, removes and returns the first element of this deque. This method is
	 * equivalent to removeFirst().
	 * @return mixed the element at the front of this deque (which is the top of the stack
	 *         represented by this deque).
	 * @throws NoSuchElementException if this deque is empty.
	 */
	public function pop();

	/**
	 * Returns an iterator over the elements in this deque in reverse sequential order.
	 * The elements will be returned in order from last (tail) to first (head).
	 * @return Iterator
	 */
	public function descendingIterator();
}
?>