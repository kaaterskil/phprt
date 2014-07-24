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

/**
 * An iterator for lists that allows the programmer to traverse the list in either
 * direction, modify the list during iteration, and obtain the iterator's current position
 * in the list.
 * A ListIterator has no current element; its cursor position always lies between the
 * element that would be returned by a call to previous() and the element that would be
 * returned by a call to next(). An iterator for a list of length n has n+1 possible
 * cursor positions, as illustrated by the carets (^) below:
 *
 * Element(0) Element(1) Element(2) ... Element(n-1)
 * cursor positions: ^ ^ ^ ^ ^
 *
 * Note that the remove() and set(Object) methods are not defined in terms of the cursor
 * position; they are defined to operate on the last element returned by a call to next()
 * or previous().
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface ListIterator extends Iterator {

	/**
	 * Returns true if this list iterator has more elements when traversing the list in
	 * the reverse direction.
	 * (In other words, returns true if previous() would return an element rather than
	 * throwing an exception.)
	 * @return int
	 */
	public function hasPrevious();

	/**
	 * Returns the previous element in the list and moves the cursor position backwards.
	 * This method may be called repeatedly to iterate through the list backwards, or
	 * intermixed with calls to next() to go back and forth. (Note that alternating calls
	 * to next and previous will return the same element repeatedly.)
	 * @return mixed
	 */
	public function previous();

	/**
	 * Returns the index of the element that would be returned by a subsequent call to
	 * next().
	 * (Returns list size if the list iterator is at the end of the list.)
	 * @return int
	 */
	public function nextIndex();

	/**
	 * Returns the index of the element that would be returned by a subsequent call to
	 * previous().
	 * (Returns -1 if the list iterator is at the beginning of the list.)
	 * @return int
	 */
	public function previousIndex();

	/**
	 * Replaces the last element returned by next() or previous() with the specified
	 * element (optional operation).
	 * This call can be made only if neither remove() nor add(E) have been called after
	 * the last call to next or previous.
	 * @param mixed $e
	 */
	public function set($e);

	/**
	 * Inserts the specified element into the list (optional operation).
	 * The element is inserted immediately before the element that would be returned by
	 * next(), if any, and after the element that would be returned by previous(), if any.
	 * (If the list contains no elements, the new element becomes the sole element on the
	 * list.) The new element is inserted before the implicit cursor: a subsequent call to
	 * next would be unaffected, and a subsequent call to previous would return the new
	 * element. (This call increases by one the value that would be returned by a call to
	 * nextIndex or previousIndex.)
	 * @param mixed $e
	 */
	public function add($e);
}
?>