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

use KM\Lang\ClassCastException;
use KM\Lang\Clazz;
use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\Lang\Reflect\MixedType;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Lang\Reflect\Type;
use KM\Lang\UnsupportedOperationException;
use KM\Util\AbstractMap\BaseKeySet;
use KM\Util\AbstractMap\BaseValuesCollection;
use KM\Util\Collection;
use KM\Util\Iterator;
use KM\Util\Map;
use KM\Util\Set;

/**
 * This class provides a skeletal implementation of the Map interface, to minimize the
 * effort required to implement this interface.
 *
 * To implement an unmodifiable map, the programmer needs only to extend this class and
 * provide an implementation for the entrySet method, which returns a set-view of the
 * map's mappings. Typically, the returned set will, in turn, be implemented atop
 * AbstractSet. This set should not support the add or remove methods, and its iterator
 * should not support the remove method.
 *
 * To implement a modifiable map, the programmer must additionally override this class's
 * put method (which otherwise throws an UnsupportedOperationException), and the iterator
 * returned by entrySet().iterator() must additionally implement its remove method.
 * The programmer should generally provide a void (no argument) and map constructor, as
 * per the recommendation in the Map interface specification.
 *
 * The documentation for each non-abstract method in this class describes its
 * implementation in detail. Each of these methods may be overridden if the map being
 * implemented admits a more efficient implementation.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class AbstractMap extends Object implements Map {
	
	/**
	 * The set of keys
	 * @var Set
	 */
	protected $keySet = null;
	
	/**
	 * The collection of values
	 * @var Collection
	 */
	protected $values = null;
	
	/**
	 * An array of <code>Type</code> objects that represent the parameter types of this collection.
	 * @var \KM\Lang\Reflect\Type[]
	 */
	protected $typeParameters;

	/**
	 * Sole constructor.
	 * @param mixed $typeParameters An array of string values or a comma-delimited string of values
	 *        denoting the type parameters declared by this GenericDeclaration object.
	 */
	protected function __construct($typeParameters = null) {
		if (empty( $typeParameters )) {
			$typeParameters = '<string, string>';
		}
		$this->typeParameters = ReflectionUtility::parseTypeParameters( $typeParameters, 2 );
	}

	/**
	 * Returns an array of <code>Type</code> objects that represent the type variables declared by
	 * the generic declaration represented by this object, in declaration order.
	 * Returns an array of length 0 if the underlying generic declaration declares no types.
	 * @return \KM\Lang\Reflect\Type[] An array of <code>Type</code> objects that represent the
	 *         types declared by this generic declaration.
	 * @see \KM\Lang\Reflect\GenericDeclaration::getTypeParameters()
	 */
	public function getTypeParameters() {
		return $this->typeParameters;
	}

	/**
	 * Checks whether the given <code>key</code> and <code>value</code> types match the declared
	 * type parameters in this generic declaration object.
	 * The method returns if both types are the same or subclasses of the declared type parameters.
	 * @param mixed $key The given <code>key</code> to test. May be null.
	 * @param mixed $value The given <code>Value</code> to test. May be null.
	 * @throws IllegalArgumentException if both the given <code>key</code> and <code>value</code>
	 *         are null.
	 * @throws ClassCastException if either the given <code>key</code> and <code>value</code> types
	 *         do not match their declared type parameters.
	 */
	public function testTypeParameters($key, $value) {
		if ($key === null && $value === null) {
			throw new IllegalArgumentException();
		}
		$control = $this->getTypeParameters();
		if ($key != null) {
			$this->testTypeParameter( $key, $control[0] );
		}
		if ($value != null) {
			$this->testTypeParameter( $value, $control[1] );
		}
	}

	/**
	 * Checks is the given candidate parameter type is the same or a subclass of the given type.
	 * @param mixed $candidate The candidate value, either a scalar, array or an object.
	 * @param Type $control The <code>Type</code> representing the type value to test against.
	 * @throws ClassCastException if the candidate's type value is not the same or a subclass of the
	 *         type to compare against.
	 */
	private function testTypeParameter($candidate, Type $controlType) {
		/* @var $controlClazz Clazz */
		if ($controlType == MixedType::MIXED()) {
			return;
		}
		
		if (is_object( $candidate )) {
			$candidateType = ReflectionUtility::typeFor( get_class( $candidate ) );
		} else {
			$candidateType = ReflectionUtility::typeFor( gettype( $candidate ) );
		}
		if (($controlType instanceof Clazz) && ($candidateType instanceof Clazz)) {
			$controlClazz = $controlType;
			if ($controlClazz->isAssignableFrom( $candidateType )) {
				return;
			}
		} elseif ($candidateType->getName() == $controlType->getName()) {
			return;
		}
		throw new ClassCastException(
			sprintf( 'Expected {%s}, got {%s}', $controlType->getName(), $candidateType->getName() ) );
	}

	/**
	 * Checks whether the type parameters of the given <code>Collection</code> match the declared
	 * type parameters of this generic declaration object.
	 * @param Map $candidate The <code>Map</code> to test.
	 * @throws ClassCastException if the type parameters of the given <code>Collection</code> do not
	 *         match the declared type parameters of this Map.
	 */
	protected function testCollectionTypeParameters(Map $candidate = null) {
		if ($candidate != null) {
			$ct1 = $this->getTypeParameters();
			$ct2 = $candidate->getTypeParameters();
			if ($ct1 != $ct2) {
				$format = 'Expected key {%s}, got {%s}; expected value {%s}, got {%s}';
				throw new ClassCastException( sprintf( $format, $ct1[0], $ct2[0], $ct1[1], $ct2[1] ) );
			}
		}
	}
	
	/* ---------- Query Operations ---------- */
	
	/**
	 * Returns the number of key-value mappings in this map.
	 * @return int
	 * @see \KM\Util\Map::size()
	 */
	public function size() {
		return $this->entrySet()->size();
	}

	/**
	 * Returns true if this map contains no key-value mappings.
	 * @return boolean
	 * @see \KM\Util\Map::isEmpty()
	 */
	public function isEmpty() {
		return $this->size() == 0;
	}

	/**
	 * Returns true if this map maps one or more keys to the specified value.
	 * @param mixed $value
	 * @return boolean
	 * @see \KM\Util\Map::containsValue()
	 */
	public function containsValue($value = null) {
		/* @var $i Iterator */
		/* @var $e Map\Entry */
		$i = $this->entrySet()->getIterator();
		if ($value == null) {
			while ( $i->hasNext() ) {
				$e = $i->next();
				if ($e->getValue() == null) {
					return true;
				}
			}
		} else {
			while ( $i->hasNext() ) {
				$e = $i->next();
				if ($value == $e->getValue()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Returns true if this map contains a mapping for the specified key.
	 * @param mixed $key
	 * @return boolean
	 * @see \KM\Util\Map::containsKey()
	 */
	public function containsKey($key = null) {
		/* @var $i Iterator */
		/* @var $e Map\Entry */
		$i = $this->entrySet()->getIterator();
		if ($key == null) {
			while ( $i->hasNext() ) {
				$e = $i->next();
				if ($e->getKey() == null) {
					return true;
				}
			}
		} else {
			while ( $i->hasNext() ) {
				$e = $i->next();
				if ($key == $e->getKey()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Returns the value to which the specified key is mapped, or null if this map
	 * contains no mapping for the key.
	 * @param mixed $key
	 * @return \Application\Stdlib\Object NULL
	 * @see \KM\Util\Map::get()
	 */
	public function get($key = null) {
		/* @var $i Iterator */
		/* @var $e Map\Entry */
		$i = $this->entrySet()->getIterator();
		if ($key == null) {
			while ( $i->hasNext() ) {
				$e = $i->next();
				if ($e->getKey() == null) {
					return $e->getValue();
				}
			}
		} else {
			while ( $i->hasNext() ) {
				$e = $i->next();
				if ($key == $e->getKey()) {
					return $e->getValue();
				}
			}
		}
		return null;
	}
	
	/* ---------- Modification Operations ---------- */
	
	/**
	 * Associates the specified value with the specified key in this map (optional
	 * operation).
	 * @param mixed $key
	 * @param mixed $value
	 * @throws UnsupportedOperationException
	 * @see \KM\Util\Map::put()
	 */
	public function put($key, $value) {
		throw new UnsupportedOperationException();
	}

	/**
	 * Removes the mapping for a key from this map if it is present (optional operation).
	 * @param mixed $key
	 * @return \Application\Stdlib\Object null
	 * @see \KM\Util\Map::remove()
	 */
	public function remove($key = null) {
		/* @var $i Iterator */
		/* @var $e Map\Entry */
		/* @var $correctEntry Map\Entry */
		$i = $this->entrySet()->getIterator();
		$correctEntry = null;
		if ($key == null) {
			while ( $correctEntry == null && $i->hasNext() ) {
				$e = $i->next();
				if ($e->getKey() == null) {
					$correctEntry = $e;
				}
			}
		} else {
			while ( $correctEntry == null && $i->hasNext() ) {
				$e = $i->next();
				if ($key == $e->getkey()) {
					$correctEntry = $e;
				}
			}
		}
		
		$oldValue = null;
		if ($correctEntry != null) {
			$oldValue = $correctEntry->getValue();
			$i->remove();
		}
		return $oldValue;
	}
	
	/* ---------- Bulk Operations ---------- */
	
	/**
	 * Copies all of the mappings from the specified map to this map (optional operation).
	 * @param Map $m
	 * @see \KM\Util\Map::putAll()
	 */
	public function putAll(Map $m) {
		/* @var $e Map/Entry */
		foreach ( $m->entrySet() as $e ) {
			$this->put( $e->getKey(), $e->getValue() );
		}
	}

	/**
	 * Removes all of the mappings from this map (optional operation).
	 * @see \KM\Util\Map::clear()
	 */
	public function clear() {
		$this->entrySet()->clear();
	}
	
	/* ---------- Views ---------- */
	
	/**
	 * Returns a Set view of the keys contained in this map.
	 * @return \KM\Util\Set
	 * @see \KM\Util\Map::keySet()
	 */
	public function keySet() {
		if ($this->keySet == null) {
			$this->keySet = new BaseKeySet( $this );
		}
		return $this->keySet;
	}

	/**
	 * Returns a Collection view of the values contained in this map.
	 * @return \KM\Util\Collection
	 * @see \KM\Util\Map::values()
	 */
	public function values() {
		if ($this->values == null) {
			$this->values = new BaseValuesCollection( $this );
		}
		return $this->values;
	}

	/**
	 * Returns a Set view of the mappings contained in this map.
	 * @return \KM\Util\Set
	 * @see \KM\Util\Map::entrySet()
	 */
	public abstract function entrySet();
	
	/* ---------- Comparison ---------- */
	
	/**
	 * Compares the specified object with this map for equality.
	 * Returns <tt>true</tt> if the given object is also a map and the two maps represent the same
	 * mappings. More formally, two maps <tt>m1</tt> and <tt>m2</tt> represent the same mappings if
	 * <tt>m1.entrySet().equals(m2.entrySet())</tt>. This ensures that the <tt>equals</tt> method
	 * works properly across different implementations of the <tt>Map</tt> interface.
	 *
	 * <p>This implementation first checks if the specified object is this map; if so it returns
	 * <tt>true</tt>. Then, it checks if the specified object is a map whose size is identical to
	 * the size of this map; if not, it returns <tt>false</tt>. If so, it iterates over this map's
	 * <tt>entrySet</tt> collection, and checks that the specified map contains each mapping that
	 * this map contains. If the specified map fails to contain such a mapping, <tt>false</tt> is
	 * returned. If the iteration completes, <tt>true</tt> is returned.
	 * @param Object $o
	 * @return boolean
	 * @see \KM\Lang\Object::equals()
	 */
	public function equals(Object $o = null) {
		/* @var $m Map */
		/* @var $i Iterator */
		/* @var $e Map\ENtry */
		if ($o == null) {
			return false;
		}
		if ($o === $this) {
			return true;
		}
		
		if (!$o instanceof Map) {
			return false;
		}
		$m = $o;
		if ($m->size() != $this->size()) {
			return false;
		}
		
		try {
			$i = $this->entrySet()->getIterator();
			while ( $i->hasNext() ) {
				$e = $i->next();
				$key = $e->getKey();
				$value = $e->getValue();
				if ($value == null) {
					if (!($m->get( $key ) == null && $m->containsKey( $key ))) {
						return false;
					}
				} else {
					if ($value != $m->get( $key )) {
						return false;
					}
				}
			}
		} catch ( ClassCastException $cce ) {
			return false;
		} catch ( NullPointerException $npe ) {
			return false;
		}
		return true;
	}

	/**
	 * Returns a string representation of this map.
	 * The string representation consists of a list of key-value mappings in the order returned by
	 * the map's entrySet view's iterator enclosed in braces. Adjacent mappings are separated by a
	 * comma and a space. Each key-value mapping is rendered as the key followed by an equals sign
	 * followed by the associated value. Keys and values are converted to strings.
	 * @return string
	 * @see \KM\Lang\Object::__toString()
	 */
	public function __toString() {
		/* @var $i Iterator */
		/* @var $e Map\Entry */
		$i = $this->entrySet()->getIterator();
		if (!$i->hasNext()) {
			return '{}';
		}
		
		$result = '{';
		for(;;) {
			$e = $i->next();
			$key = $e->getKey();
			$value = $e->getValue();
			$result . ($key == $this ? '(this Map)' : $key);
			$result . '=';
			$result . ($value == $this ? '(this Map)' : $value);
			if (!$i->hasNext()) {
				return $result . '}';
			}
			$result . ', ';
		}
	}

	/**
	 * Returns a shallow copy of this AbstractMap instance: the keys and values themselves are not
	 * cloned.
	 */
	public function __clone() {
		$this->keySet = null;
		$this->values = null;
	}
}
?>