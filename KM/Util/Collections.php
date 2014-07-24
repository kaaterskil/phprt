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
use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\Util\Collection;
use KM\Util\Collections\EmptyIterator;
use KM\Util\Collections\EmptyListIterator;
use KM\Util\Collections\EmptyMap;
use KM\Util\Collections\EmptySet;
use KM\Util\Collections\UnmodifiableCollection;
use KM\Util\Collections\UnmodifiableMap;
use KM\Util\Collections\UnmodifiableSet;
use KM\Util\Iterator;
use KM\Util\ListInterface;
use KM\Util\ListIterator;
use KM\Util\Map;
use KM\Util\Set;
use KM\Util\Collections\EmptyList;
use KM\Util\Collections\UnmodifiableList;
use KM\Util\Collections\SetFromMap;
use KM\Util\Collections\SingletonIterator;
use KM\Util\Collections\SingletonList;

/**
 * This class consists exclusive of static methods that operate on or return collections.
 * It contains polymorphic algorithms that operate on collections, wrappers, which return a new
 * collection backed by a specified collection, and a few other odds and ends.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Collections extends Object {
	
	/**
	 * An immutable empty List
	 * @var ListInterface
	 */
	private static $EMPTY_LIST;
	
	/**
	 * An immutable empty Map
	 * @var Map
	 */
	private static $EMPTY_MAP;
	
	/**
	 * An immutable empty Set
	 * @var Set
	 */
	private static $EMPTY_SET;

	/**
	 * Suppresses default constructor, ensuring non-instantiability.
	 */
	private function __construct() {
	}
	
	/* ---------- Algorithms ---------- */
	
	/**
	 * Sorts the specified list into ascending order, according to the
	 * {@linkplain Comparable natural ordering} of its elements.
	 * All elements in the list must implement the {@link Comparable}
	 * interface. Furthermore, all elements in the list must be
	 * <i>mutually comparable</i> (that is, {@code e1.compareTo(e2)}
	 * must not throw a {@code ClassCastException} for any elements
	 * {@code e1} and {@code e2} in the list).
	 *
	 * <p>This sort is guaranteed to be <i>stable</i>: equal elements will
	 * not be reordered as a result of the sort.
	 *
	 * <p>The specified list must be modifiable, but need not be resizable.
	 *
	 * <p>Implementation note: This implementation is a stable, adaptive,
	 * iterative mergesort that requires far fewer than n lg(n) comparisons
	 * when the input array is partially sorted, while offering the
	 * performance of a traditional mergesort when the input array is
	 * randomly ordered. If the input array is nearly sorted, the
	 * implementation requires approximately n comparisons. Temporary
	 * storage requirements vary from a small constant for nearly sorted
	 * input arrays to n/2 object references for randomly ordered input
	 * arrays.
	 *
	 * <p>The implementation takes equal advantage of ascending and
	 * descending order in its input array, and can take advantage of
	 * ascending and descending order in different parts of the same
	 * input array. It is well-suited to merging two or more sorted arrays:
	 * simply concatenate the arrays and sort the resulting array.
	 *
	 * <p>The implementation was adapted from Tim Peters's list sort for Python
	 * (<a href="http://svn.python.org/projects/python/trunk/Objects/listsort.txt">
	 * TimSort</a>). It uses techniques from Peter McIlroy's "Optimistic
	 * Sorting and Information Theoretic Complexity", in Proceedings of the
	 * Fourth Annual ACM-SIAM Symposium on Discrete Algorithms, pp 467-474,
	 * January 1993.
	 *
	 * <p>This implementation dumps the specified list into an array, sorts
	 * the array, and iterates over the list resetting each element
	 * from the corresponding position in the array. This avoids the
	 * n<sup>2</sup> log(n) performance that would result from attempting
	 * to sort a linked list in place.
	 * @param ListInterface $list The list to be sorted.
	 */
	public static function sort(ListInterface $list) {
		/* @var $i ListIterator */
		$a = $list->toArray();
		sort( $a );
		$i = $list->listIterator();
		for($j = 0; $j < count( $a ); $j++) {
			$i->next();
			$i->set( $a[$j] );
		}
	}

	/**
	 * Sorts the specified list according to the order induced by the
	 * specified comparator.
	 * All elements in the list must be <i>mutually
	 * comparable</i> using the specified comparator (that is,
	 * {@code c.compare(e1, e2)} must not throw a {@code ClassCastException}
	 * for any elements {@code e1} and {@code e2} in the list).
	 *
	 * <p>This sort is guaranteed to be <i>stable</i>: equal elements will
	 * not be reordered as a result of the sort.
	 *
	 * <p>The specified list must be modifiable, but need not be resizable.
	 *
	 * <p>Implementation note: This implementation is a stable, adaptive,
	 * iterative mergesort that requires far fewer than n lg(n) comparisons
	 * when the input array is partially sorted, while offering the
	 * performance of a traditional mergesort when the input array is
	 * randomly ordered. If the input array is nearly sorted, the
	 * implementation requires approximately n comparisons. Temporary
	 * storage requirements vary from a small constant for nearly sorted
	 * input arrays to n/2 object references for randomly ordered input
	 * arrays.
	 *
	 * <p>The implementation takes equal advantage of ascending and
	 * descending order in its input array, and can take advantage of
	 * ascending and descending order in different parts of the same
	 * input array. It is well-suited to merging two or more sorted arrays:
	 * simply concatenate the arrays and sort the resulting array.
	 *
	 * <p>The implementation was adapted from Tim Peters's list sort for Python
	 * (<a href="http://svn.python.org/projects/python/trunk/Objects/listsort.txt">
	 * TimSort</a>). It uses techniques from Peter McIlroy's "Optimistic
	 * Sorting and Information Theoretic Complexity", in Proceedings of the
	 * Fourth Annual ACM-SIAM Symposium on Discrete Algorithms, pp 467-474,
	 * January 1993.
	 *
	 * <p>This implementation dumps the specified list into an array, sorts
	 * the array, and iterates over the list resetting each element
	 * from the corresponding position in the array. This avoids the
	 * n<sup>2</sup> log(n) performance that would result from attempting
	 * to sort a linked list in place.
	 * @param ListInterface $list The list to be sorted.
	 * @param Comparator $c The comparator to determine the order of the list.
	 */
	public static function sortWithComparator(ListInterface $list, Comparator $c) {
		/* @var $i ListIterator */
		$a = $list->toArray();
		usort( $a, array(
			$c,
			'compare'
		) );
		$i = $list->listIterator();
		for($j = 0; $j < count( $a ); $j++) {
			$i->next();
			$i->set( $a[$j] );
		}
	}
	
	/* ---------- Unmodifiable Wrappers ---------- */
	
	/**
	 * Returns an unmodifiable view of the specified collection.
	 * This method allows modules to provide users with "read-only" access to internal collections.
	 * Query operations on the returned collection "read through" to the specified collection, and
	 * attempts to modify the returned collection, whether direct or via its iterator, result in an
	 * UnsupportedOperationException.
	 * @param Collection $c
	 * @return \KM\Util\Collections\UnmodifiableCollection
	 */
	public static function unmodifiableCollection(Collection $c) {
		return new UnmodifiableCollection( $c );
	}

	/**
	 * Returns an unmodifiable view of the specified set.
	 * The method allows modules to provide users with "read-only" access to internal sets. Query
	 * operations on the returned set "read through" to the specified set, and attempts to modify
	 * the returned set, whether direct or via its iterator, result in an
	 * UnsupportedOperationException.
	 * @param Set $s
	 * @return \KM\Util\Collections\UnmodifiableSet
	 */
	public static function unmodifiableSet(Set $s) {
		return new UnmodifiableSet( $s );
	}

	/**
	 * Returns an unmodifiable view of the specified list.
	 * This method allows modules to provide users with "read-only" access to internal lists. Query
	 * operations on the returned list "read through" to the specified list, and attempts to modify
	 * the returned list, whether direct or via its iterator, result in an
	 * <tt>UnsupportedOperationException</tt>.<p>
	 * @param ListInterface $list The list for which an unmodifiable view is to be returned.
	 * @return \KM\Util\Collections\UnmodifiableList An unmodifiable view of the specified list.
	 */
	public static function unmodifiableList(ListInterface $list) {
		return new UnmodifiableList( $list );
	}

	/**
	 * Returns an unmodifiable view of the specified map.
	 * The method allows modules to provide users with "read-only" access to internal maps. Query
	 * operations on the returned set "read through" to the specified set, and attempts to modify
	 * the returned set, whether direct or via its iterator, result in an
	 * UnsupportedOperationException.
	 * @param Map $m
	 * @return \KM\Util\Collections\UnmodifiableMap
	 */
	public static function unmodifiableMap(Map $m) {
		return new UnmodifiableMap( $m );
	}
	
	/* ---------- Empty Collections ---------- */
	
	/**
	 * Returns an iterator that has no elements.
	 * @return \KM\Util\Iterator
	 */
	public static function emptyIterator() {
		return EmptyIterator::getEmptyIterator();
	}

	/**
	 * Returns an iterator that has no elements.
	 * @return \KM\Util\ListIterator
	 */
	public static function emptyListIterator() {
		return EmptyListIterator::getEmptyIterator();
	}

	/**
	 * Returns the empty List
	 * @param $typeParameter A value denoting the type parameter declared by this
	 *        GenericDeclaration object.
	 * @return \KM\Util\ListInterface
	 */
	public static final function emptyList($typeParameter = null) {
		if ((self::$EMPTY_LIST == null) ||
			 (!empty( $typeParameter ) && $typeParameter != self::$EMPTY_LIST->getTypeParameters())) {
			self::$EMPTY_LIST = new EmptyList( $typeParameter );
		}
		return self::$EMPTY_LIST;
	}

	/**
	 * Returns the empty map.
	 * @return \KM\Util\Map
	 */
	public static final function emptyMap() {
		if (self::$EMPTY_MAP == null) {
			self::$EMPTY_MAP = new EmptyMap();
		}
		return self::$EMPTY_MAP;
	}

	/**
	 * Returns the empty set.
	 * @return \KM\Util\Set
	 */
	public static function emptySet() {
		if (self::$EMPTY_SET == null) {
			self::$EMPTY_SET = new EmptySet();
		}
		return self::$EMPTY_SET;
	}

	public static function singletonIterator($e) {
		return new SingletonIterator( $e );
	}

	/**
	 * Returns an immutable list containing only the specified object.
	 * The returned list is serializable.
	 * @param Object $o
	 * @return \KM\Util\Collections\SingletonList
	 */
	public static function singletonList(Object $o) {
		return new SingletonList( $o );
	}
	
	/* ---------- Miscellaneous ---------- */
	
	/**
	 * Returns true if the specified arguments are equal or both null.
	 * @param Object $o1
	 * @param Object $o2
	 * @return boolean
	 */
	public static function eq(Object $o1 = null, Object $o2 = null) {
		return ($o1 == null ? $o2 == null : $o1->equals( $o2 ));
	}

	/**
	 * Returns a set backed by the specified map.
	 * The resulting set displays
	 * the same ordering, concurrency, and performance characteristics as the
	 * backing map. In essence, this factory method provides a {@link Set}
	 * implementation corresponding to any {@link Map} implementation. There
	 * is no need to use this method on a {@link Map} implementation that
	 * already has a corresponding {@link Set} implementation (such as {@link
	 * HashMap} or {@link TreeMap}).
	 *
	 * <p>Each method invocation on the set returned by this method results in
	 * exactly one method invocation on the backing map or its <tt>keySet</tt>
	 * view, with one exception. The <tt>addAll</tt> method is implemented
	 * as a sequence of <tt>put</tt> invocations on the backing map.
	 *
	 * <p>The specified map must be empty at the time this method is invoked,
	 * and should not be accessed directly after this method returns. These
	 * conditions are ensured if the map is created empty, passed directly
	 * to this method, and no reference to the map is retained, as illustrated
	 * in the following code fragment:
	 * <pre>
	 * Set&lt;Object&gt; weakHashSet = Collections.newSetFromMap(
	 * new WeakHashMap&lt;Object, Boolean&gt;());
	 * </pre>
	 * @param Map $map The backing map
	 * @return \KM\Util\Set
	 */
	public static function newSetFromMap(Map $map) {
		return new SetFromMap( $map );
	}
}
?>