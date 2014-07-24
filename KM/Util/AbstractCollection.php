<?php
/**
 * Copyright (c) 2009-2014 Kaaterskil Management, LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace KM\Util;

use KM\Lang\ClassCastException;
use KM\Lang\Object;
use KM\Lang\Reflect\GenericDeclaration;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Lang\System;
use KM\Lang\UnsupportedOperationException;
use KM\Util\Iterator;
use KM\Lang\Clazz;

/**
 * This class provides a skeletal implementation of the Collection interface, to
 * minimize the effort required to implement this interface.
 * To implement an unmodifiable collection, the programmer needs only to extend
 * this class and provide implementations for the iterator and size methods.
 * (The iterator returned by the iterator method must implement hasNext and
 * next.)
 * To implement a modifiable collection, the programmer must additionally
 * override this class's add method (which otherwise throws an
 * UnsupportedOperationException), and the iterator returned by the iterator
 * method must additionally implement its remove method.
 * The programmer should generally provide a void (no argument) and Collection
 * constructor, as per the recommendation in the Collection interface
 * specification.
 * The documentation for each non-abstract method in this class describes its
 * implementation in detail. Each of these methods may be overridden if the
 * collection being implemented admits a more efficient implementation.
 *
 * @author Blair
 */
abstract class AbstractCollection extends Object implements Collection, GenericDeclaration
{

    /**
     * An array of <code>Type</code> objects that represent the parameter types
     * of this collection.
     *
     * @var \KM\Lang\Reflect\Type[]
     */
    protected $typeParameters = array();

    /**
     * Sole constructor, with the given type parameters.
     *
     * @param mixed $typeParameters An array of string values or a
     *            comma-delimited string of values denoting the type parameters
     *            declared by this GenericDeclaration object.
     */
    protected function __construct($typeParameters = '<string>')
    {
        $this->typeParameters = ReflectionUtility::parseTypeParameters($typeParameters);
    }

    /**
     * Returns an array of <code>Type</code> objects that represent the type
     * variables declared by the generic declaration represented by this object,
     * in declaration order. Returns an array of length 0 if the underlying
     * generic declaration declares no types.
     *
     * @return \KM\Lang\Reflect\Type[] An array of <code>Type</code> objects
     *         that represent the types declared by this generic declaration.
     * @see \KM\Util\Collection::getTypeParameters()
     */
    public function getTypeParameters()
    {
        return $this->typeParameters;
    }

    /**
     * Sets the <code>Type</code> objects that represent the type variables
     * declared by the generic declaration represented by this object, in
     * declaration order.
     *
     * @param string $typeParameters An array of string values or a
     *            comma-delimited string of values denoting the type parameters
     *            declared by this GenericDeclaration object.
     * @see \KM\Util\Collection::setTypeParameters()
     */
    public function setTypeParameters($typeParameters)
    {
        $this->typeParameters = ReflectionUtility::parseTypeParameters($typeParameters);
    }

    /**
     * Tests that a given value matches the this collection's specified
     * parameter type. For objects whose class is not an instance of this
     * collection's parameter type, this method will throw a ClassCastException.
     * For scalar, array or resource values, if their type does not match this
     * collection's parameter type, this method will throw a
     * ClassCastExeception. Otherwise, this method will return true.
     *
     * @param mixed $o The value to test against this collection's parameter
     *            type.
     * @throws ClassCastException if the given value does not match this
     *         collection's parameter type.
     * @return boolean
     */
    protected function testTypeParameters($o)
    {
        /* @var $clazz Clazz */
        if ($o != null) {
            if ($o instanceof Collection) {
                if ($this->typeParameters != $o->getTypeParameters()) {
                    throw new ClassCastException();
                }
            } else {
                $name = is_object($o) ? get_class($o) : gettype($o);
                $type = ReflectionUtility::typeFor($name);
                if (count($this->typeParameters) > 1) {
                    throw new ClassCastException();
                }
                if ($this->typeParameters[0] != $type) {
                    // Allow subclasses if the type parameter is an Object.
                    if ($this->typeParameters[0] instanceof Clazz) {
                        $clazz = $this->typeParameters[0];
                        if ($clazz->isAssignableFrom($type)) {
                            return true;
                        }
                    }
                    $format = 'Expected {%s}, got {%s}';
                    throw new ClassCastException(
                        sprintf($format, $this->typeParameters[0]->getName(), $type->getName()));
                }
            }
        }
        return true;
    }

    /**
     * Returns an iterator over the elements contained in this collection.
     *
     * @return \KM\Util\Iterator
     * @see IteratorAggregate::getIterator()
     */
    public abstract function getIterator();

    /**
     * Returns the number of elements in this collection. If this collection
     * contains more than Integer.MAX_VALUE elements, returns Integer.MAX_VALUE.
     *
     * @return int
     * @see \KM\Util\Collection::size()
     */
    public abstract function size();

    /**
     * Returns true if this collection contains no elements.
     *
     * @return boolean
     * @see \KM\Util\Collection::isEmpty()
     */
    public function isEmpty()
    {
        return $this->size() == 0;
    }

    /**
     * This implementation iterates over the elements in the collection,
     * checking each element in turn for equality with the specified element.
     *
     * @param mixed $o
     * @return boolean
     * @see \KM\Util\Collection::contains()
     */
    public function contains($o = null)
    {
        $this->testTypeParameters($o);
        
        $it = $this->getIterator();
        if ($o == null) {
            while ($it->hasNext()) {
                if ($it->next() == null) {
                    return true;
                }
            }
        } else {
            while ($it->hasNext()) {
                if ($o === $it->next()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * This implementation returns an array containing all the elements returned
     * by this collection's iterator in the same order, stored in consecutive
     * elements of the array, starting with index 0.
     *
     * @param array $a
     * @return mixed[]
     * @see \KM\Util\Collection::toArray()
     */
    public function toArray(array $a = null)
    {
        if ($a == null) {
            return $this->toArray0();
        }
        return $this->toArray1($a);
    }

    /**
     * This implementation returns an array containing all the elements returned
     * by this collection's iterator, in the same order, stored in consecutive
     * elements of the array, starting with index 0. The length o the returned
     * array is equal to the number of elements returned by the iterator, even
     * if the size of this collection changes during iteration, as might happen
     * if the collection permits concurrent modification during iteration. THe
     * size() method is called only as an optimization hint; the correct result
     * is returned even if the iterator returns a different number of elements.
     *
     * @return array
     */
    protected function toArray0()
    {
        // Estimate the size of the array; be prepared to see more or fewer
        // elements.
        $r = array();
        $size = $this->size();
        $it = $this->getIterator();
        for ($i = 0; $i < count($r); $i ++) {
            if (! $it->hasNext()) {
                // Fewer elements than expected
                return Arrays::copyOf($r, $i);
            }
            $r[$i] = $it->next();
        }
        return $it->hasNext() ? static::finishToArray($r, $it) : $r;
    }

    /**
     * This implementation returns an array containing all the elements returned
     * by this collection's iterator in the same order, stored in consecutive
     * elements of the array, starting with index 0. If the number of elements
     * returned by the iterator is too large to fit into the specified array,
     * then the elements are returned in a newly allocated array with length
     * equal to the number of elements returned by the iterator, even if the
     * size of this collection changes during iteration. The size() method is
     * called only as an optimization hint; the correct result is returned even
     * if the iterator returns a different number of elements.
     *
     * @param array $a
     * @return array
     */
    protected function toArray1(array $a)
    {
        // Estimate the size of the array; be prepared to see more or fewer
        // elements.
        $size = $this->size();
        $r = count($a) >= $size ? $a : array_fill(0, $size, null);
        $it = $this->getIterator();
        
        for ($i = 0; $i < count($r); $i ++) {
            if (! $it->hasNext()) {
                // Fewer elements than expected
                if ($a == $r) {
                    $r[$i] = null;
                } elseif (count($a) < $i) {
                    return Arrays::copyOf($r, $i);
                } else {
                    System::arraycopy($r, 0, $a, 0, $i);
                    if (count($a) > $i) {
                        $a[$i] = null;
                    }
                }
                return $a;
            }
            $r[$i] = $it->next();
        }
        // More elements than expected
        return $it->hasNext() ? static::finishToArray($r, $it) : $r;
    }

    /**
     * Reallocates the array being used within toArray() when the iterator
     * returned more elements than expected, and finishes filling it from the
     * iterator.
     *
     * @param array $r The array, replete with previously stored elements.
     * @param appIterator $it The in-progress iterator over this collection.
     * @return array containing the elements in the given array, plus any
     *         further elements returned by the iterator, trimmed to size.
     */
    private static function finishToArray(array $r, appIterator $it)
    {
        $i = count($r);
        while ($it->hasNext()) {
            $cap = count($r);
            if ($i == $cap) {
                $newCap = $cap + ($cap >> $i) + 1;
                if ($newCap - PHP_INT_MAX - 8 > 0) {
                    $newCap = static::hugeCapacity($cap + 1);
                }
                $r = Arrays::copyOf($r, $newCap);
            }
            $r[$i ++] = $it->next();
        }
        return ($i == count($r)) ? $r : Arrays::copyOf($r, $i);
    }

    private static function hugeCapacity($minCapacity)
    {
        if ($minCapacity < 0) {
            throw new \OverflowException('Required array size too large');
        }
        return ($minCapacity > (PHP_INT_MAX - 8)) ? PHP_INT_MAX : PHP_INT_MAX - 8;
    }
    
    /* ---------- Modification Operations ---------- */
    
    /**
     * Ensures that this collection contains the specified element (optional
     * operation). Returns true if this collection changed as a result of the
     * call. (Returns false if this collection does not permit duplicates and
     * already contains the specified element.)
     * Collections that support this operation may place limitations on what
     * elements may be added to this collection. In particular, some collections
     * will refuse to add null elements, and others will impose restrictions on
     * the type of elements that may be added. Collection classes should clearly
     * specify in their documentation any restrictions on what elements may be
     * added.
     * If a collection refuses to add a particular element for any reason other
     * than that it already contains the element, it must throw an exception
     * (rather than returning false). This preserves the invariant that a
     * collection always contains the specified element after this call returns.
     * This implementation always throws an UnsupportedOperationException.
     *
     * @param mixed $e
     * @return boolean
     * @throws UnsupportedOperationException
     * @see \KM\Util\Collection::add()
     */
    public function add($e)
    {
        throw new UnsupportedOperationException();
    }

    /**
     * This implementation iterates over the collection looking for the
     * specified element. If it finds the element, it removes the element from
     * the collection using the iterator's remove method.
     * Note that this implementation throws an UnsupportedOperationException if
     * the iterator returned by this collection's iterator method does not
     * implement the remove method and this collection contains the specified
     * object.
     *
     * @param mixed $o
     * @return boolean
     * @see \KM\Util\Collection::remove()
     */
    public function remove($o = null)
    {
        $this->testTypeParameters($o);
        
        $it = $this->getIterator();
        if ($o == null) {
            while ($it->hasNext()) {
                if ($it->next() == null) {
                    $it->remove();
                    return true;
                }
            }
        } else {
            while ($it->hasNext()) {
                if ($o->equals($it->next())) {
                    $it->remove();
                    return true;
                }
            }
        }
        return false;
    }
    
    /* ---------- Bulk Operations ---------- */
    
    /**
     * This implementation iterates over the specified collection, checking each
     * element returned by the iterator in turn to see if it's contained in this
     * collection. If all elements are so contained true is returned, otherwise
     * false.
     *
     * @param Collection $c
     * @return boolean
     * @see \KM\Util\Collection::containsAll()
     */
    public function containsAll(Collection $c)
    {
        foreach ($c as $e) {
            if (! $this->contains($e)) {
                return false;
            }
        }
        return true;
    }

    /**
     * This implementation iterates over the specified collection, and adds each
     * object returned by the iterator to this collection, in turn.
     * Note that this implementation will throw an UnsupportedOperationException
     * unless add is overridden (assuming the specified collection is
     * non-empty).
     * @param Collection $c
     * @return boolean
     * @see \KM\Util\Collection::addAll()
     */
    public function addAll(Collection $c)
    {
        $modified = false;
        foreach ($c as $e) {
            if ($this->add($e)) {
                $modified = true;
            }
        }
        return $modified;
    }

    /**
     * This implementation iterates over this collection, checking each element
     * returned by the iterator in turn to see if it's contained in the
     * specified collection. If it's so contained, it's removed from this
     * collection with the iterator's remove method.
     * Note that this implementation will throw an UnsupportedOperationException
     * if the iterator returned by the iterator method does not implement the
     * remove method and this collection contains one or more elements in common
     * with the specified collection.
     * @param Collection $c
     * @return boolean
     * @see \KM\Util\Collection::removeAll()
     */
    public function removeAll(Collection $c)
    {
        $modified = false;
        $it = $this->getIterator();
        while ($it->hasNext()) {
            if ($c->contains($it->next())) {
                $it->remove();
                $modified = true;
            }
        }
        return $modified;
    }

    /**
     * This implementation iterates over this collection, checking each element
     * returned by the iterator in turn to see if it's contained in the
     * specified collection. If it's not so contained, it's removed from this
     * collection with the iterator's remove method.
     * Note that this implementation will throw an UnsupportedOperationException
     * if the iterator returned by the iterator method does not implement the
     * remove method and this collection contains one or more elements not
     * present in the specified collection.
     * @param Collection $c
     * @return boolean
     * @see \KM\Util\Collection::retainAll()
     */
    public function retainAll(Collection $c)
    {
        $modified = false;
        $it = $this->getIterator();
        while ($it->hasNext()) {
            if (! $c->contains($it->next())) {
                $it->remove();
                $modified = true;
            }
        }
        return $modified;
    }

    /**
     * Removes all of the elements from this collection (optional operation).
     * The collection will be empty after this method returns.
     * This implementation iterates over this collection, removing each element
     * using the Iterator.remove operation. Most implementations will probably
     * choose to override this method for efficiency.
     * Note that this implementation will throw an UnsupportedOperationException
     * if the iterator returned by this collection's iterator method does not
     * implement the remove method and this collection is non-empty.
     * @return void
     * @see \KM\Util\Collection::clear()
     */
    public function clear()
    {
        $it = $this->getIterator();
        while ($it->hasNext()) {
            $it->next();
            $it->remove();
        }
    }
    
    /* ---------- String Conversion ---------- */
    
    /**
     * Returns a string representation of this collection. The string
     * representation consists of a list of the collection's elements in the
     * order they are returned by its iterator, enclosed in square brackets
     * ("[]"). Adjacent elements are separated by the characters ", " (comma and
     * space). Elements are converted to strings as by String.valueOf(Object).
     *
     * @return string
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        $it = $this->getIterator();
        if (! $it->hasNext()) {
            return "[]";
        }
        
        $string = "[";
        while (true) {
            $e = $it->next();
            $string .= ($e == $this ? "(this Collection)" : $e);
            if (! $it->hasNext()) {
                return $string .= "]";
            }
            $string .= ', ';
        }
    }
}
?>