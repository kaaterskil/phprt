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
use KM\Util\Collection;
use KM\Util\Set;
use KM\Lang\Reflect\GenericDeclaration;

/**
 * An object that maps keys to values.
 * A map cannot contain duplicate keys; each key can map to at most one value.
 *
 * This interface takes the place of the Dictionary class, which was a totally abstract
 * class rather than an interface.
 *
 * The Map interface provides three collection views, which allow a map's contents to be
 * viewed as a set of keys, collection of values, or set of key-value mappings. The order
 * of a map is defined as the order in which the iterators on the map's collection views
 * return their elements. Some map implementations, like the TreeMap class, make specific
 * guarantees as to their order; others, like the HashMap class, do not.
 *
 * Note: great care must be exercised if mutable objects are used as map keys. The
 * behavior of a map is not specified if the value of an object is changed in a manner
 * that affects equals comparisons while the object is a key in the map. A special case of
 * this prohibition is that it is not permissible for a map to contain itself as a key.
 * While it is permissible for a map to contain itself as a value, extreme caution is
 * advised: the equals and hashCode methods are no longer well defined on such a map.
 *
 * All general-purpose map implementation classes should provide two "standard"
 * constructors: a void (no arguments) constructor which creates an empty map, and a
 * constructor with a single argument of type Map, which creates a new map with the same
 * key-value mappings as its argument. In effect, the latter constructor allows the user
 * to copy any map, producing an equivalent map of the desired class. There is no way to
 * enforce this recommendation (as interfaces cannot contain constructors) but all of the
 * general-purpose map implementations in the JDK comply.
 *
 * The "destructive" methods contained in this interface, that is, the methods that modify
 * the map on which they operate, are specified to throw UnsupportedOperationException if
 * this map does not support the operation. If this is the case, these methods may, but
 * are not required to, throw an UnsupportedOperationException if the invocation would
 * have no effect on the map. For example, invoking the putAll(Map) method on an
 * unmodifiable map may, but is not required to, throw the exception if the map whose
 * mappings are to be "superimposed" is empty.
 *
 * Some map implementations have restrictions on the keys and values they may contain. For
 * example, some implementations prohibit null keys and values, and some have restrictions
 * on the types of their keys. Attempting to insert an ineligible key or value throws an
 * unchecked exception, typically NullPointerException or ClassCastException. Attempting
 * to query the presence of an ineligible key or value may throw an exception, or it may
 * simply return false; some implementations will exhibit the former behavior and some
 * will exhibit the latter. More generally, attempting an operation on an ineligible key
 * or value whose completion would not result in the insertion of an ineligible element
 * into the map may throw an exception or it may succeed, at the option of the
 * implementation. Such exceptions are marked as "optional" in the specification for this
 * interface.
 *
 * This interface is a member of the Java Collections Framework.
 *
 * Many methods in Collections Framework interfaces are defined in terms of the equals
 * method. For example, the specification for the containsKey(Object key) method says:
 * "returns true if and only if this map contains a mapping for a key k such that
 * (key==null ? k==null : key.equals(k))." This specification should not be construed to
 * imply that invoking Map.containsKey with a non-null argument key will cause
 * key.equals(k) to be invoked for any key k. Implementations are free to implement
 * optimizations whereby the equals invocation is avoided, for example, by first comparing
 * the hash codes of the two keys. (The Object.hashCode() specification guarantees that
 * two objects with unequal hash codes cannot be equal.) More generally, implementations
 * of the various Collections Framework interfaces are free to take advantage of the
 * specified behavior of underlying Object methods wherever the implementor deems it
 * appropriate.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface Map extends GenericDeclaration {

	/* ---------- Query Operations ---------- */
	
	/**
	 * Returns the number of key-value mappings in this map.
	 * If the map contains more than Integer.MAX_VALUE elements, returns
	 * Integer.MAX_VALUE.
	 * @return int The number of key-value mappings in this map
	 */
	public function size();

	/**
	 * Returns true if this map contains no key-value mappings.
	 * @return boolean
	 */
	public function isEmpty();

	/**
	 * Returns true if this map contains a mapping for the specified key.
	 * More formally, returns true if and only if this map contains a mapping for a key k
	 * such that (key==null ? k==null : key.equals(k)). (There can be at most one such
	 * mapping.)
	 * @param mixed $key
	 * @return boolean
	 */
	public function containsKey($key = null);

	/**
	 * Returns true if this map maps one or more keys to the specified value.
	 * More formally, returns true if and only if this map contains at least one mapping
	 * to a value v such that (value==null ? v==null : value.equals(v)). This operation
	 * will probably require time linear in the map size for most implementations of the
	 * Map interface.
	 * @param mixed $value
	 * @return boolean
	 */
	public function containsValue($value = null);

	/**
	 * Returns the value to which the specified key is mapped, or null if this map
	 * contains no mapping for the key.
	 *
	 * More formally, if this map contains a mapping from a key k to a value v such that
	 * (key==null ? k==null : key.equals(k)), then this method returns v; otherwise it
	 * returns null. (There can be at most one such mapping.)
	 *
	 * If this map permits null values, then a return value of null does not necessarily
	 * indicate that the map contains no mapping for the key; it's also possible that the
	 * map explicitly maps the key to null. The containsKey operation may be used to
	 * distinguish these two cases.
	 * @param mixed $key
	 * @return Object
	 */
	public function get($key = null);
	
	/* ---------- Modification Operations ---------- */

	/**
	 * Associates the specified value with the specified key in this map (optional
	 * operation).
	 * If the map previously contained a mapping for the key, the old value is replaced by
	 * the specified value. (A map m is said to contain a mapping for a key k if and only
	 * if m.containsKey(k) would return true.)
	 * @param mixed $key
	 * @param mixed $value
	 * @return mixed
	 */
	public function put($key, $value);

	/**
	 * Removes the mapping for a key from this map if it is present (optional operation).
	 * More formally, if this map contains a mapping from key k to value v such that
	 * (key==null ? k==null : key.equals(k)), that mapping is removed. (The map can
	 * contain at most one such mapping.)
	 *
	 * Returns the value to which this map previously associated the key, or null if the
	 * map contained no mapping for the key.
	 *
	 * If this map permits null values, then a return value of null does not necessarily
	 * indicate that the map contained no mapping for the key; it's also possible that the
	 * map explicitly mapped the key to null.
	 *
	 * The map will not contain a mapping for the specified key once the call returns.
	 * @param mixed $key
	 * @return Object
	 */
	public function remove($key = null);
	
	/* ---------- Bulk Operations ---------- */

	/**
	 * Copies all of the mappings from the specified map to this map (optional operation).
	 * The effect of this call is equivalent to that of calling put(k, v) on this map once
	 * for each mapping from key k to value v in the specified map. The behavior of this
	 * operation is undefined if the specified map is modified while the operation is in
	 * progress.
	 * @param Map $m
	 * @return void
	 */
	public function putAll(Map $m);

	/**
	 * Removes all of the mappings from this map (optional operation).
	 * The map will be empty after this call returns.
	 * @return void
	 */
	public function clear();
	
	/* ---------- Views ---------- */

	/**
	 * Returns a Set view of the keys contained in this map.
	 * The set is backed by the map, so changes to the map are reflected in the set, and
	 * vice-versa. If the map is modified while an iteration over the set is in progress
	 * (except through the iterator's own remove operation), the results of the iteration
	 * are undefined. The set supports element removal, which removes the corresponding
	 * mapping from the map, via the Iterator.remove, Set.remove, removeAll, retainAll,
	 * and clear operations. It does not support the add or addAll operations.
	 * @return Set
	 */
	public function keySet();

	/**
	 * Returns a Collection view of the values contained in this map.
	 * The collection is backed by the map, so changes to the map are reflected in the
	 * collection, and vice-versa. If the map is modified while an iteration over the
	 * collection is in progress (except through the iterator's own remove operation), the
	 * results of the iteration are undefined. The collection supports element removal,
	 * which removes the corresponding mapping from the map, via the Iterator.remove,
	 * Collection.remove, removeAll, retainAll and clear operations. It does not support
	 * the add or addAll operations.
	 * @return Collection
	 */
	public function values();

	/**
	 * Returns a Set view of the mappings contained in this map.
	 * The set is backed by the map, so changes to the map are reflected in the set, and
	 * vice-versa. If the map is modified while an iteration over the set is in progress
	 * (except through the iterator's own remove operation, or through the setValue
	 * operation on a map entry returned by the iterator) the results of the iteration are
	 * undefined. The set supports element removal, which removes the corresponding
	 * mapping from the map, via the Iterator.remove, Set.remove, removeAll, retainAll and
	 * clear operations. It does not support the add or addAll operations.
	 * @return Set
	 */
	public function entrySet();
}
?>