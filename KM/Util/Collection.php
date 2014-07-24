<?php

/**
 * Macchiato Library
 *
 * PHP version 5.4
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
 * KAATERSKIL MANAGEMENT, LLC BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category Macchiato
 * @package Application
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
namespace KM\Util;

use KM\Lang\Object;

/**
 * The root interface in the collection hierarchy.
 * A collection represents a group of objects, known as its elements. Some collections
 * allow duplicate elements and others do not. Some are ordered and others unordered. The
 * JDK does not provide any direct implementations of this interface: it provides
 * implementations of more specific sub-interfaces like Set and List. This interface is
 * typically used to pass collections around and manipulate them where maximum generality
 * is desired.
 *
 * Bags or multisets (unordered collections that may contain duplicate elements) should
 * implement this interface directly.
 *
 * All general-purpose Collection implementation classes (which typically implement
 * Collection indirectly through one of its sub-interfaces) should provide two "standard"
 * constructors: a void (no arguments) constructor, which creates an empty collection, and
 * a constructor with a single argument of type Collection, which creates a new collection
 * with the same elements as its argument. In effect, the latter constructor allows the
 * user to copy any collection, producing an equivalent collection of the desired
 * implementation type. There is no way to enforce this convention (as interfaces cannot
 * contain constructors) but all of the general-purpose Collection implementations in the
 * Java platform libraries comply.
 *
 * The "destructive" methods contained in this interface, that is, the methods that modify
 * the collection on which they operate, are specified to throw
 * UnsupportedOperationException if this collection does not support the operation. If
 * this is the case, these methods may, but are not required to, throw an
 * UnsupportedOperationException if the invocation would have no effect on the collection.
 * For example, invoking the addAll(Collection) method on an unmodifiable collection may,
 * but is not required to, throw the exception if the collection to be added is empty.
 *
 * Some collection implementations have restrictions on the elements that they may
 * contain. For example, some implementations prohibit null elements, and some have
 * restrictions on the types of their elements. Attempting to add an ineligible element
 * throws an unchecked exception, typically NullPointerException or ClassCastException.
 * Attempting to query the presence of an ineligible element may throw an exception, or it
 * may simply return false; some implementations will exhibit the former behavior and some
 * will exhibit the latter. More generally, attempting an operation on an ineligible
 * element whose completion would not result in the insertion of an ineligible element
 * into the collection may throw an exception or it may succeed, at the option of the
 * implementation. Such exceptions are marked as "optional" in the specification for this
 * interface.
 *
 * It is up to each collection to determine its own synchronization policy. In the absence
 * of a stronger guarantee by the implementation, undefined behavior may result from the
 * invocation of any method on a collection that is being mutated by another thread; this
 * includes direct invocations, passing the collection to a method that might perform
 * invocations, and using an existing iterator to examine the collection.
 *
 * Many methods in Collections Framework interfaces are defined in terms of the equals
 * method. For example, the specification for the contains(Object o) method says: "returns
 * true if and only if this collection contains at least one element e such that (o==null
 * ? e==null : o.equals(e))." This specification should not be construed to imply that
 * invoking Collection.contains with a non-null argument o will cause o.equals(e) to be
 * invoked for any element e. Implementations are free to implement optimizations whereby
 * the equals invocation is avoided, for example, by first comparing the hash codes of the
 * two elements. (The Object.hashCode() specification guarantees that two objects with
 * unequal hash codes cannot be equal.) More generally, implementations of the various
 * Collections Framework interfaces are free to take advantage of the specified behavior
 * of underlying Object methods wherever the implementor deems it appropriate.
 *
 * @package Application\Stdlib\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface Collection extends \IteratorAggregate {

	/**
	 * Returns an iterator over the elements in this collection.
	 * There are no guarantees concerning the order in which the elements are returned
	 * (unless this collection is an instance of some class that provides a guarantee).
	 * @return \KM\Util\Iterator
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator();

	/**
	 * Returns the number of elements in this collection.
	 * If this collection contains more than Integer.MAX_VALUE elements, returns
	 * Integer.MAX_VALUE.
	 * @return int
	 */
	public function size();

	/**
	 * Returns true if this collection contains no elements.
	 * @return boolean
	 */
	public function isEmpty();

	/**
	 * Returns true if this collection contains the specified element.
	 * More formally, returns true if and only if this collection contains at least one
	 * element e such that (o==null ? e==null : o.equals(e)).
	 * @param mixed $o
	 * @return boolean
	 */
	public function contains($o = null);

	/**
	 * Returns an array containing all of the elements in this collection; the runtime
	 * type of the returned array is that of the specified array.
	 * If the collection fits in the specified array, it is returned therein. Otherwise, a
	 * new array is allocated with the runtime type of the specified array and the size of
	 * this collection.
	 *
	 * If this collection fits in the specified array with room to spare (i.e., the array
	 * has more elements than this collection), the element in the array immediately
	 * following the end of the collection is set to null. (This is useful in determining
	 * the length of this collection only if the caller knows that this collection does
	 * not contain any null elements.)
	 *
	 * If this collection makes any guarantees as to what order its elements are returned
	 * by its iterator, this method must return the elements in the same order.
	 *
	 * Like the toArray() method, this method acts as bridge between array-based and
	 * collection-based APIs. Further, this method allows precise control over the runtime
	 * type of the output array, and may, under certain circumstances, be used to save
	 * allocation costs.
	 *
	 * Suppose x is a collection known to contain only strings. The following code can be
	 * used to dump the collection into a newly allocated array of String:
	 *
	 * String[] y = x.toArray(new String[0]);
	 *
	 * Note that toArray(new Object[0]) is identical in function to toArray().
	 * @param array $a
	 * @return Object[]
	 */
	public function toArray(array $a = null);

	/**
	 * Ensures that this collection contains the specified element (optional operation).
	 * Returns true if this collection changed as a result of the call. (Returns false if
	 * this collection does not permit duplicates and already contains the specified
	 * element.)
	 *
	 * Collections that support this operation may place limitations on what elements may
	 * be added to this collection. In particular, some collections will refuse to add
	 * null elements, and others will impose restrictions on the type of elements that may
	 * be added. Collection classes should clearly specify in their documentation any
	 * restrictions on what elements may be added.
	 *
	 * If a collection refuses to add a particular element for any reason other than that
	 * it already contains the element, it must throw an exception (rather than returning
	 * false). This preserves the invariant that a collection always contains the
	 * specified element after this call returns.
	 * @param mixed $e
	 * @return boolean
	 */
	public function add($e);

	/**
	 * Removes a single instance of the specified element from this collection, if it is
	 * present (optional operation).
	 * More formally, removes an element e such that (o==null ? e==null : o.equals(e)), if
	 * this collection contains one or more such elements. Returns true if this collection
	 * contained the specified element (or equivalently, if this collection changed as a
	 * result of the call).
	 * @param mixed $o
	 * @return boolean
	 */
	public function remove($o = null);

	/**
	 * Returns true if this collection contains all of the elements in the specified
	 * collection.
	 * @param Collection $c
	 * @return boolean
	 */
	public function containsAll(Collection $c);

	/**
	 * Adds all of the elements in the specified collection to this collection (optional
	 * operation).
	 * The behavior of this operation is undefined if the specified collection is modified
	 * while the operation is in progress. (This implies that the behavior of this call is
	 * undefined if the specified collection is this collection, and this collection is
	 * nonempty.)
	 * @param Collection $c
	 * @return boolean
	 */
	public function addAll(Collection $c);

	/**
	 * Removes all of this collection's elements that are also contained in the specified
	 * collection (optional operation).
	 * After this call returns, this collection will contain no elements in common with
	 * the specified collection.
	 * @param Collection $c
	 * @return boolean
	 */
	public function removeAll(Collection $c);

	/**
	 * Retains only the elements in this collection that are contained in the specified
	 * collection (optional operation).
	 * In other words, removes from this collection all of its elements that are not
	 * contained in the specified collection.
	 * @param Collection $c
	 * @return boolean
	 */
	public function retainAll(Collection $c);

	/**
	 * Removes all of the elements from this collection (optional operation).
	 * The collection will be empty after this method returns.
	 * @return void
	 */
	public function clear();

	/**
	 * Returns an array of <code>Type</code> objects that represent the type variables declared by
	 * the generic declaration represented by this object, in declaration order.
	 * Returns an array of length 0 if the underlying generic declaration declares no types.
	 * @return \KM\Lang\Reflect\Type[] An array of <code>Type</code> objects that represent the
	 *         types declared by this generic declaration.
	 */
	public function getTypeParameters();

	/**
	 * Sets the <code>Type</code> objects that represent the type variables declared by
	 * the generic declaration represented by this object, in declaration order.
	 * @return \KM\Lang\Reflect\Type[] An array of <code>Type</code> objects that represent the
	 *         types declared by this generic declaration.
	 */
	public function setTypeParameters($typeParameters);
}
?>