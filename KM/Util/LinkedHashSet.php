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

use KM\IO\Serializable;

/**
 * <p>Hash table and linked list implementation of the <tt>Set</tt> interface,
 * with predictable iteration order.
 * This implementation differs from
 * <tt>HashSet</tt> in that it maintains a doubly-linked list running through
 * all of its entries. This linked list defines the iteration ordering,
 * which is the order in which elements were inserted into the set
 * (<i>insertion-order</i>). Note that insertion order is <i>not</i> affected
 * if an element is <i>re-inserted</i> into the set. (An element <tt>e</tt>
 * is reinserted into a set <tt>s</tt> if <tt>s.add(e)</tt> is invoked when
 * <tt>s.contains(e)</tt> would return <tt>true</tt> immediately prior to
 * the invocation.)
 *
 * <p>This implementation spares its clients from the unspecified, generally
 * chaotic ordering provided by {@link HashSet}, without incurring the
 * increased cost associated with {@link TreeSet}. It can be used to
 * produce a copy of a set that has the same order as the original, regardless
 * of the original set's implementation:
 * <pre>
 * void foo(Set s) {
 * Set copy = new LinkedHashSet(s);
 * ...
 * }
 * </pre>
 * This technique is particularly useful if a module takes a set on input,
 * copies it, and later returns results whose order is determined by that of
 * the copy. (Clients generally appreciate having things returned in the same
 * order they were presented.)
 *
 * <p>This class provides all of the optional <tt>Set</tt> operations, and
 * permits null elements. Like <tt>HashSet</tt>, it provides constant-time
 * performance for the basic operations (<tt>add</tt>, <tt>contains</tt> and
 * <tt>remove</tt>), assuming the hash function disperses elements
 * properly among the buckets. Performance is likely to be just slightly
 * below that of <tt>HashSet</tt>, due to the added expense of maintaining the
 * linked list, with one exception: Iteration over a <tt>LinkedHashSet</tt>
 * requires time proportional to the <i>size</i> of the set, regardless of
 * its capacity. Iteration over a <tt>HashSet</tt> is likely to be more
 * expensive, requiring time proportional to its <i>capacity</i>.
 *
 * <p>A linked hash set has two parameters that affect its performance:
 * <i>initial capacity</i> and <i>load factor</i>. They are defined precisely
 * as for <tt>HashSet</tt>. Note, however, that the penalty for choosing an
 * excessively high value for initial capacity is less severe for this class
 * than for <tt>HashSet</tt>, as iteration times for this class are unaffected
 * by capacity.
 *
 * <p><strong>Note that this implementation is not synchronized.</strong>
 * If multiple threads access a linked hash set concurrently, and at least
 * one of the threads modifies the set, it <em>must</em> be synchronized
 * externally. This is typically accomplished by synchronizing on some
 * object that naturally encapsulates the set.
 *
 * If no such object exists, the set should be "wrapped" using the
 * {@link Collections#synchronizedSet Collections.synchronizedSet}
 * method. This is best done at creation time, to prevent accidental
 * unsynchronized access to the set: <pre>
 * Set s = Collections.synchronizedSet(new LinkedHashSet(...));</pre>
 *
 * <p>The iterators returned by this class's <tt>iterator</tt> method are
 * <em>fail-fast</em>: if the set is modified at any time after the iterator
 * is created, in any way except through the iterator's own <tt>remove</tt>
 * method, the iterator will throw a {@link ConcurrentModificationException}.
 * Thus, in the face of concurrent modification, the iterator fails quickly
 * and cleanly, rather than risking arbitrary, non-deterministic behavior at
 * an undetermined time in the future.
 *
 * <p>Note that the fail-fast behavior of an iterator cannot be guaranteed
 * as it is, generally speaking, impossible to make any hard guarantees in the
 * presence of unsynchronized concurrent modification. Fail-fast iterators
 * throw <tt>ConcurrentModificationException</tt> on a best-effort basis.
 * Therefore, it would be wrong to write a program that depended on this
 * exception for its correctness: <i>the fail-fast behavior of iterators
 * should be used only to detect bugs.</i>
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class LinkedHashSet extends HashSet implements Serializable {

	/**
	 * Constructs a new linked hash set with the same elements as the specified collection.
	 * @param string $typeParameter A value denoting the type parameter declared by this
	 *        GenericDeclaration object.
	 * @param Collection $c The collection whose elements are to be places into this set.
	 */
	public function __construct($typeParameter = null, Collection $c = null) {
		parent::__construct( $typeParameter, $c );
	}
}
?>