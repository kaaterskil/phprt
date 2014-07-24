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

use KM\IO\InvalidClassException;
use KM\IO\IOException;
use KM\IO\ObjectInputStream;
use KM\IO\ObjectOutputStream;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\ClassNotFoundException;
use KM\Lang\ArrayIndexOutOfBoundsException;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\Object;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Lang\System;
use KM\Util\AbstractList;
use KM\Util\ListInterface;
use KM\Util\Vector\Enumeration;
use KM\Util\Vector\ListItr;
use KM\Util\Vector\Itr;

/**
 * The <code>Vector</code> class implements a grow-able array of objects.
 *
 * Like an array, it contains components that can be accessed using an integer
 * index. However, the size of a <code>Vector</code> can grow or shrink as
 * needed to accommodate adding and removing items after the <code>Vector</code>
 * has been created.
 *
 * <p>Each vector tries to optimize storage management by maintaining a
 * <code>capacity</code> and a <code>capacityIncrement</code>. The
 * <code>capacity</code> is always at least as large as the vector size; it is
 * usually larger because as components are added to the vector, the vector's
 * storage increases in chunks the size of <code>capacityIncrement</code>. An
 * application can increase the capacity of a vector before inserting a large
 * number of components; this reduces the amount of incremental reallocation.
 *
 * @author Blair
 */
class Vector extends AbstractList implements ListInterface, \IteratorAggregate,
    Serializable
{

    private static $MAX_ARRAY_SIZE = PHP_INT_MAX;

    /**
     * The array buffer into which the components of the vector are stored.
     * The capacity of the vector is the length of the array and is at least
     * large enough to contain all the vector's elements. @Transient
     *
     * @var mixed[]
     */
    protected $elementData = array();

    /**
     * The number of valid components in this Vector object.
     *
     * @var int
     */
    protected $elementCount = 0;

    /**
     * The amount by which the capacity of the vector is automatically
     * incremented when its size becomes greater than its capacity.
     *
     * @var int
     */
    protected $capacityIncrement = 0;

    /**
     * Constructs a Vector containing the elements of the specified collection
     * in the order they area returned by the collection's iterator.
     *
     * If no collection is specified, an empty vector is constructed with an
     * internal data array of size 10 and its standard capacity increment is
     * zero.
     *
     * @param string $typeParameter A value denoting the type parameter declared
     *        by this GenericDeclaration object.
     * @param Collection $c
     */
    public function __construct($typeParameter = null, Collection $c = null)
    {
        parent::__construct($typeParameter);
        if ($c != null) {
            $this->testTypeParameters($c);
            $this->elementData = $c->toArray();
            $this->elementCount = count($this->elementData);
        } else {
            $this->elementData = array_fill(0, 10, null);
        }
    }

    /**
     * Returns a deep copy.
     */
    public function __clone()
    {
        $clone = array();
        foreach ($this->elementData as $key => $value) {
            $clone[$key] = $value;
        }
        $this->elementData = $clone;
    }

    /**
     * Trims the capacity of this vector to be the vector's current size.
     *
     * If the capacity of this vector is larger than its current size, then the
     * capacity is changed to equal the size by replacing its internal dat
     * array, kept in the field <code>elementData</code>, with a smaller one. An
     * application can use this operation to minimize the storage of a vector.
     */
    public function trimtoSize()
    {
        $oldCapacity = count($this->elementData);
        if ($this->elementCount < $oldCapacity) {
            $this->elementData = Arrays::copyOf($this->elementData,
                $this->elementCount);
        }
    }

    /**
     * Increases the capacity of this vector, if necessary, to ensure that it
     * can hold at least the number of components specified by the minimum
     * capacity argument.
     *
     * <p>If the current capacity of this vector is less than
     * <code>minCapacity</code>, then its capacity is increased by replacing its
     * internal data array, kept in the field <code>elementData</code>, with a
     * larger one. The size of the new data array will be the old size plus
     * <code>capacityIncrement</code>, unless the value of
     * <code>capacityIncrement</code> is less than or equal to zero, in which
     * case the new capacity will be twice the old capacity; but if this new
     * size is still smaller than <code>minCapacity</code>, then the new
     * capacity will be <code>minCapacity</code>.
     *
     * @param int $minCapacity
     */
    public function ensureCapacity($minCapacity)
    {
        if ($minCapacity > 0) {
            $this->ensureCapacityHelper($minCapacity);
        }
    }

    private function ensureCapacityHelper($minCapacity)
    {
        if ($minCapacity - count($this->elementData) > 0) {
            $this->grow($minCapacity);
        }
    }

    private function grow($minCapacity)
    {
        $oldCapacity = count($this->elementData);
        $newCapacity = $oldCapacity +
             (($this->capacityIncrement > 0) ? $this->capacityIncrement : $oldCapacity);
        if ($newCapacity - $minCapacity < 0) {
            $newCapacity = $minCapacity;
        }
        if ($newCapacity - self::$MAX_ARRAY_SIZE > 0) {
            $newCapacity = self::hugeCapacity($minCapacity);
        }
        $this->elementData = Arrays::copyOf($this->elementData, $newCapacity);
    }

    private static function hugeCapacity($minCapacity)
    {
        if ($minCapacity < 0) {
            throw new \OverflowException();
        }
        return self::$MAX_ARRAY_SIZE;
    }

    /**
     * Sets the size of this vector.
     *
     * If the new size is greater than the current size, new <code>null</code>
     * items are added to the end of the vector. If the new size is less than
     * the current size, all components at index <code>newSize</code> and
     * greater are discarded.
     *
     * @param int $newSize
     */
    public function setSize($newSize)
    {
        if ($newSize > $this->elementCount) {
            $this->ensureCapacityHelper($newSize);
        } else {
            for ($i = $newSize; $i < $this->elementCount; $i++) {
                $this->elementData[$i] = null;
            }
        }
        $this->elementCount = $newSize;
    }

    /**
     * Returns the current capacity of this Vector.
     *
     * @return int
     */
    public function capacity()
    {
        return count($this->elementData);
    }

    /**
     * Returns the number of components in this Vector.
     *
     * @return int
     * @see \KM\Util\AbstractCollection::size()
     */
    public function size()
    {
        return $this->elementCount;
    }

    /**
     * Tests if this Vector has no components.
     *
     * @return boolean
     * @see \KM\Util\AbstractCollection::isEmpty()
     */
    public function isEmpty()
    {
        return $this->elementCount == 0;
    }

    /**
     * Returns an enumeration of the components of this vector.
     * The returned
     * Enumeration object will generate all items in this vector. The first item
     * generated is the item at index 0.
     *
     * @return \KM\Util\Vector\Enumeration
     */
    public function elements()
    {
        return new Enumeration($this->elementData, $this->elementCount);
    }

    /**
     * Returns true if this vector contains the specified element.
     *
     * @param mixed $o
     * @return boolean
     * @see \KM\Util\AbstractCollection::contains()
     */
    public function contains($o = null)
    {
        return $this->indexOffWithOffset($o, 0) >= 0;
    }

    /**
     * Returns the index of the first occurrence of the specified element in
     * this vector, or -1 if this vector does not contain the element.
     *
     * @param mixed $o The element to search for.
     * @return int The index of the first occurrence of the specified element in
     *         this vector, or -1 if this vector does not contain the element.
     * @see \KM\Util\AbstractList::indexOf()
     */
    public function indexOf($o = null)
    {
        return $this->indexOffWithOffset($o, 0);
    }

    /**
     * Returns the index of the first occurrence of the specified element in
     * this vector, searching forwards from $offset, or returns -1 if the
     * element is not found.
     *
     * @param mixed $o The element to search for.
     * @param int $offset Index to start searching from.
     * @return int The index of the first occurrence of the element in this
     *         vector at position $offset or later in the vector.
     */
    public function indexOffWithOffset($o = null, $offset)
    {
        if ($o == null) {
            for ($i = $offset; $i < $this->elementCount; $i++) {
                if ($this->elementData[$i] == null) {
                    return $i;
                }
            }
        } else {
            for ($i = $offset; $i < $this->elementCount; $i++) {
                if ($o instanceof Object && $o->equals($this->elementData[$i])) {
                    return $i;
                } elseif ($o == $this->elementData[$i]) {
                    return $i;
                }
            }
        }
        return -1;
    }

    /**
     * Returns the index of the last occurrence of the specified element in this
     * vector, or -1 if this vector does not contain the element.
     *
     * @param mixed $o The element to search for.
     * @return int The index of the last occurrence of the specified element in
     *         this vector, or -1 if this vector does not contain the element.
     * @see \KM\Util\AbstractList::lastIndexOf()
     */
    public function lastIndexOf($o = null)
    {
        return $this->lastIndeOfWithOffset($o, $this->elementCount - 1);
    }

    /**
     * Returns the index of the last occurrence of the specified element in this
     * vector, searching backwards from $offset, or returns -1 if the element is
     * not found.
     *
     * @param mixed $o The element to search for.
     * @param int $offset The index to start searching backwards from.
     * @throws IndexOutOfBoundsException If the specified $offset is greater
     *         than or equal to the current size of the vector.
     * @return int The index of the last occurrence of the element at position
     *         less than or equal to $offset in this vector, or -1 if the
     *         element is not found.
     */
    public function lastIndeOfWithOffset($o = null, $offset)
    {
        if ($offset >= $this->elementCount) {
            throw new IndexOutOfBoundsException(
                $offset . '>=' . $this->elementCount);
        }
        if ($o == null) {
            for ($i = $offset; $i >= 0; $i--) {
                if ($this->elementData[$i] == null) {
                    return $i;
                }
            }
        } else {
            for ($i = $offset; $i >= 0; $i--) {
                if ($o instanceof Object && $o->equals($this->elementData[$i])) {
                    return $i;
                } elseif ($o == $this->elementData[$i]) {
                    return $i;
                }
            }
        }
        return -1;
    }

    /**
     * Returns the component at the specified $index.
     *
     * @param int $index
     * @throws ArrayIndexOutOfBoundsException
     * @return mixed
     */
    public function elementAt($index)
    {
        $index = (int) $index;
        if ($index >= $this->elementCount) {
            throw new ArrayIndexOutOfBoundsException(
                $index . '>=' . $this->elementCount);
        }
        return $this->elementData($index);
    }

    /**
     * Returns the first component of this vector.
     *
     * @throws NoSuchElementException
     * @return mixed
     */
    public function firstElement()
    {
        if ($this->elementCount == 0) {
            throw new NoSuchElementException();
        }
        return $this->elementData(0);
    }

    /**
     * Returns the last component of this vector.
     *
     * @throws NoSuchElementException
     * @return mixed
     */
    public function lastElement()
    {
        if ($this->elementCount == 0) {
            throw new NoSuchElementException();
        }
        return $this->elementData($this->elementCount - 1);
    }

    /**
     * Sets the component at the specified $index of this vector to be the
     * specified object.
     *
     * The previous component at that position is discarded.
     *
     * @param mixed $obj
     * @param int $index
     * @throws ArrayIndexOutOfBoundsException
     */
    public function setElementAt($obj, $index)
    {
        $this->testTypeParameters($obj);
        if ($index >= $this->elementCount) {
            throw new ArrayIndexOutOfBoundsException(
                $index . '>=' . $this->elementCount);
        }
        $this->elementData[$index] = $obj;
    }

    /**
     * Deletes the component at the specified index.
     *
     * Each component in this vector with an index greater or equal to the
     * specified <code>index</code> is shifted downward to have an index one
     * smaller than the value it had previously. The size of this vector is
     * decreased by <code>1</code>. The index must be a value greater than or
     * equal to <code>0</code> and less than the current size of the vector.
     *
     * This method is identical in functionality to the <code>remove(int)</code>
     * method (which is part of the <code>List</code> interface). Note that the
     * <code>remove</code> method returns the old value that was stored at the
     * specified position.
     *
     * @param int $index The index of the object to remove.
     * @throws ArrayIndexOutOfBoundsException If the index is out of range.
     */
    public function removeElementAt($index)
    {
        if ($index >= $this->elementCount) {
            throw new ArrayIndexOutOfBoundsException(
                $index . '>=' . $this->elementCount);
        } elseif ($index < 0) {
            throw new ArrayIndexOutOfBoundsException($index);
        }
        $j = $this->elementCount - $index - 1;
        if ($j > 0) {
            System::arraycopy($this->elementData, $index + 1,
                $this->elementData, $index, $j);
        }
        $this->elementCount--;
        $this->elementData[$this->elementCount] = null;
    }

    /**
     * Inserts the specified object as a component in this vector at the
     * specified <code>index</code>.
     *
     * Each component in this vector with an index greater or equal to the
     * specified <code>index</code> is shifted upward to have an index one
     * greater than the value it had previously.
     *
     * <p>The index must be a value greater than or equal to <code>0</code> and
     * less than or equal to the current size of the vector. (If the index is
     * equal to the current size of the vector, the new element is appended to
     * the Vector.)
     *
     * <p>This method is identical in functionality to the <code>add(int,
     * Object)</code> method (which is part of the <code>List</code> interface).
     * Note that the <code>add</code> method reverses the order of the
     * parameters, to more closely match array usage.
     *
     * @param mixed $obj The component to insert.
     * @param int $index
     * @throws ArrayIndexOutOfBoundsException
     */
    public function insertElementAt($obj, $index)
    {
        $this->testTypeParameters($obj);
        if ($index > $this->elementCount) {
            throw new ArrayIndexOutOfBoundsException(
                $index . '>=' . $this->elementCount);
        }
        $this->ensureCapacityHelper($this->elementCount + 1);
        System::arraycopy($this->elementData, $index, $this->elementData,
            $this->elementCount - 1, $index);
        $this->elementData[$index] = $obj;
        $this->elementCount++;
    }

    /**
     * Adds the specified component to the end of this vector, increasing its
     * size by one.
     *
     * The capacity of this vector is increased if its size becomes greater than
     * its capacity.
     *
     * <p>This method is identical in functionality to the
     * <code>add(Object)</code> method (which is part of the <code>List</code>
     * interface).
     *
     * @param mixed $obj The component to be added.
     */
    public function addElement($obj)
    {
        $this->testTypeParameters($obj);
        $this->ensureCapacityHelper($this->elementCount + 1);
        $this->elementData[$this->elementCount++] = $obj;
    }

    /**
     * Removes the first (lowest-indexed) occurrence of the argument from this
     * vector.
     *
     * If the object is found in this vector, each component in the
     * vector with an index greater or equal to the object's index is shifted
     * downward to have an index one smaller than the value it had previously.
     *
     * <p>This method is identical in functionality to the
     * <code>remove(Object)</code> method (which is part of the
     * <code>List</code> interface).
     *
     * @param mixed $obj The component to be removed.
     * @return boolean True if the argument was a component of this vector;
     *         false otherwise.
     */
    public function removeElement($obj)
    {
        $i = $this->indexOf($obj);
        if ($i >= 0) {
            $this->removeElementAt($i);
            return true;
        }
        return false;
    }

    /**
     * Returns
     * an array containing all of the elements in this Vector in the correct
     * order; the runtime type of the returned array is that of the specified
     * array.
     *
     * If the Vector fits in the specified array, it is returned therein.
     * Otherwise, a new array is allocated with the runtime type of the
     * specified array and the size of this Vector.
     *
     * <p>If the Vector fits in the specified array with room to spare (i.e.,
     * the array has more elements than the Vector), the element in the array
     * immediately following the end of the Vector is set to null. (This is
     * useful in determining the length of the Vector <em>only</em> if the
     * caller knows that the Vector does not contain any null elements.)
     *
     * @param array $a The array into which the elements of the Vector are to be
     *        stored, if it is big enough; otherwise, a new array is
     *        allocated for this purpose.
     * @return array
     */
    public function toArray(array $a = null)
    {
        if ($a == null) {
            return $this->toArray0();
        }
        return $this->toArray1($a);
    }

    protected function toArray0()
    {
        $result = array();
        foreach ($this->elementData as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

    protected function toArray1(array $a)
    {
        if (count($a) < $this->elementCount) {
            $copy = array_fill(0, $this->elementCount, null);
            System::arraycopy($this->elementData, 0, $copy, 0,
                $this->elementCount);
            return $copy;
        }
        System::arraycopy($this->elementData, 0, $a, 0, $this->elementCount);
        if (count($a) > $this->elementCount) {
            $a[$this->elementCount] = null;
        }
        return $a;
    }
    
    /* ---------- Positional Access Operations ---------- */
    
    /**
     * Returns the element at the specified position in this Vector.
     *
     * @param int $index
     * @return mixed
     */
    protected function elementData($index)
    {
        $index = (int) $index;
        return $this->elementData[$index];
    }

    /**
     * Returns the element at the specified position in this Vector.
     *
     * @param int $index Index of the element to return.
     * @throws ArrayIndexOutOfBoundsException if the index is out of range.
     * @return mixed
     * @see \KM\Util\AbstractList::get()
     */
    public function get($index)
    {
        $index = (int) $index;
        if ($index >= $this->elementCount) {
            throw new ArrayIndexOutOfBoundsException($index);
        }
        return $this->elementData($index);
    }

    /**
     * Replaces the element at the specified position in this Vector with the
     * specified element.
     *
     * @param int $index Index of the element to replace.
     * @param mixed $element Element to be stored at the specified position.
     * @throws ArrayIndexOutOfBoundsException if the index is out of range.
     * @return mixed The element previously at the specified position.
     * @see \KM\Util\AbstractList::set()
     */
    public function set($index, $element)
    {
        $this->testTypeParameters($element);
        $index = (int) $index;
        if ($index >= $this->elementCount) {
            throw new ArrayIndexOutOfBoundsException($index);
        }
        $oldValue = $this->elementData($index);
        $this->elementData[$index] = $element;
        return $oldValue;
    }

    /**
     * Appends the specified element to the end of this Vector.
     *
     * @param mixed $e The element to be appended to this Vector.
     * @return boolean
     * @see \KM\Util\AbstractList::add()
     */
    public function add($e)
    {
        $this->testTypeParameters($e);
        $this->ensureCapacityHelper($this->elementCount + 1);
        $this->elementData[$this->elementCount++] = $e;
        return true;
    }

    /**
     * Removes the first occurrence of the specified element in this Vector.
     *
     * If the Vector does not contain the element, it is unchanged.
     *
     * @param mixed $o The element to be removed from this Vector, if present.
     * @return boolean True if the Vector contained the specified element.
     */
    public function remove($o = null)
    {
        return $this->removeElement($o);
    }

    /**
     * Inserts the specified element at the specified position in this Vector.
     * Shifts the element currently at that position (if any) and any subsequent
     * elements to the right (adds one to their indices).
     *
     * @param int $index Index at which the specified element is to be inserted.
     * @param mixed $element Element to be inserted.
     */
    public function addAt($index, $element)
    {
        $this->insertElementAt($element, $index);
    }

    /**
     * Removes the element at the specified position in this Vector.
     * Shifts any subsequent elements to the left (subtracts one from their
     * indices). Returns the element that was removed from this Vector.
     *
     * @param int $index The index of the element to be removed.
     * @throws ArrayIndexOutOfBoundsException if the index is out of range.
     * @return mixed
     */
    public function removeAt($index)
    {
        $index = (int) $index;
        if ($index >= $this->elementCount) {
            throw new ArrayIndexOutOfBoundsException($index);
        }
        $oldValue = $this->elementData($index);
        
        $numMoved = $this->elementCount - $index - 1;
        if ($numMoved > 0) {
            System::arraycopy($this->elementData, $index + 1,
                $this->elementData, $index, $numMoved);
        }
        $this->elementData[--$this->elementCount] = null;
        
        return $oldValue;
    }

    /**
     * Removes all of the elements from this Vector.
     *
     * The Vector will be empty after this call returns (unless it throws an
     * exception).
     *
     * @see \KM\Util\AbstractList::clear()
     */
    public function clear()
    {
        $this->removeAllElements();
    }
    
    /* ---------- Bulk Operations ---------- */
    
    /**
     * Returns true if this Vector contains all of the elements in the specified
     * collection.
     *
     * @param Collection $c A collection whose elements will be tested for
     *        containment in this Vector.
     * @return boolean True if this Vector contains all of the elements in the
     *         specified collection.
     */
    public function containsAll(Collection $c)
    {
        return parent::containsAll($c);
    }

    /**
     * Appends all of the elements in the specified Collection to the end of
     * this Vector, in the order that they are returned by the specified
     * Collection's Iterator.
     *
     * The behavior of this operation is undefined if the specified Collection
     * is modified while the operation is in progress. (This implies that the
     * behavior of this call is undefined if the specified Collection is this
     * Vector, and this Vector is nonempty.)
     *
     * @param Collection $c Elements to be inserted into this Vector.
     * @return boolean True if this Vector changed as a result of the call.
     */
    public function addAll(Collection $c)
    {
        $this->testTypeParameters($c);
        $a = $c->toArray();
        $numNew = count($a);
        $this->ensureCapacityHelper($this->elementCount + $numNew);
        System::arraycopy($a, 0, $this->elementData, $this->elementCount,
            $numNew);
        $this->elementCount += $numNew;
        return $numNew != 0;
    }

    /**
     * Removes from this Vector all of its elements that are contained in the
     * specified collection.
     *
     * @param Collection $c A collection of elements to be removed from the
     *        Vector.
     * @return boolean True if this Vector changed as a result of the call.
     */
    public function removeAll(Collection $c)
    {
        return parent::removeAll($c);
    }

    /**
     * Retains only the elements in this Vector that are contained in the
     * specified Collection.
     *
     * In other words, removes from this Vector all of its elements that are not
     * contained in the specified Collection.
     *
     * @param Collection $c A collection of elements to be retained in this
     *        Vector (all other elements are removed).
     * @return boolean True if this Vector changed as a result of the call.
     */
    public function retainAll(Collection $c)
    {
        return parent::retainAll($c);
    }

    /**
     * Inserts all of the elements in the specified Collection into this Vector
     * at the specified position.
     *
     * Shifts the element currently at that position (if any) and any subsequent
     * elements to the right (increases their indices). The new elements will
     * appear in the Vector in the order that they are returned by the specified
     * Collection's iterator.
     *
     * @param int $index Index at which to insert the first element from the
     *        specified collection.
     * @param Collection $c Elements to be inserted into this Vector.
     * @throws ArrayIndexOutOfBoundsException if the index is out of range.
     * @return boolean True if this Vector changed as a result of the call.
     */
    public function addAllAtIndex($index, Collection $c)
    {
        $this->testTypeParameters($c);
        $index = (int) $index;
        if ($index < 0 || $index > $this->elementCount) {
            throw new ArrayIndexOutOfBoundsException($index);
        }
        
        $a = $c->toArray();
        $numNew = count($a);
        $this->ensureCapacityHelper($this->elementCount + $numNew);
        
        $numMoved = $this->elementCount - $index;
        if ($numMoved > 0) {
            System::arraycopy($this->elementData, $index, $this->elementData,
                $index + $numNew, $numMoved);
        }
        System::arraycopy($a, 0, $this->elementData, $index, $numNew);
        $this->elementCount += $numNew;
        return $numNew != 0;
    }

    /**
     * Compares the specified Object with this Vector for equality.
     *
     * Returns true if and only if the specified Object is also a List, both
     * Lists have the same size, and all corresponding pairs of elements in the
     * two Lists are <em>equal</em>. (Two elements <code>e1</code> and
     * <code>e2</code> are <em>equal</em> if <code>(e1==null ? e2==null :
     * e1.equals(e2))</code>.) In other words, two Lists are defined to be equal
     * if they contain the same elements in the same order.
     *
     * @param Object $obj The Object to be compared for equality with this
     *        Vector.
     * @return boolean True if the specified Object is equal to this Vector.
     */
    public function equals(Object $obj = null)
    {
        return parent::equals($obj);
    }

    /**
     * Returns the hash code value for this Vector.
     */
    public function hashCode()
    {
        return parent::hashCode();
    }

    /**
     * Returns a string representation of this Vector, containing the string
     * representation of each element.
     *
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }

    /**
     * Returns a list iterator over the elements in this list (in proper
     * sequence), starting at the specified position in the list.
     *
     * The specified index indicates the first element that would be returned by
     * an initial call to <code>next</code>. An initial call to
     * <code>previous</code> would return the element with the specified index
     * minus one.
     *
     * @param int $index
     * @throws IndexOutOfBoundsException
     * @throws \KM\Lang\IllegalArgumentException
     * @return \KM\Util\ListIterator
     * @see \KM\Util\AbstractList::listIterator()
     */
    public function listIterator($index = 0)
    {
        $index = (int) $int;
        if ($index < 0 || $index > $this->elementCount) {
            throw new IndexOutOfBoundsException('Index: ' . $index);
        }
        return new ListItr($index, $this);
    }

    /**
     * Returns an iterator over the elements in this list in proper sequence.
     *
     * @return \KM\Util\Iterator
     * @see \KM\Util\AbstractCollection::getIterator()
     */
    public function getIterator()
    {
        return new Itr($this);
    }
    
    /* ---------- Serialization Methods ---------- */
    
    /**
     * Saves this vector instance to a stream (i.e.
     * serializes it).
     *
     * @param ObjectOutputStream $s
     * @throws IOException if an I/O error occurs
     */
    private function writeObject(ObjectOutputStream $s)
    {
        // Write out any serialization
        $s->defaultWriteObject();
        
        // Write out size
        $s->writeInt($this->elementCount);
        
        // Write out elements in proper order
        $type = ReflectionUtility::typeFor($this->typeParameters[0]);
        for ($i = 0; $i < $this->elementCount; $i++) {
            $s->writeMixed($this->elementData[$i], $type);
        }
    }

    /**
     * Reconstitutes this vector instance from a stream (i.e.
     * deserializes it).
     *
     * @param ObjectInputStream $s
     * @throws IOException if an I/O error occurs
     * @throws ClassNotFoundException
     */
    private function readObject(ObjectInputStream $s)
    {
        // Read in any serialization
        $s->defaultReadObject();
        
        // Read in size
        $size = $s->readInt();
        if ($size < 0) {
            throw new InvalidClassException('Illegal size: ' . $size);
        }
        
        // Read in elements in proper order
        for ($i = 0; $i < $size; $i++) {
            if ($i == 0) {
                $e = $s->readMixed();
                $this->typeParameters = [
                    ReflectionUtility::typeForValue($e)->getTypeName()
                ];
                $this->elementData[$i] = $e;
            } else {
                $this->elementData[$i] = $s->readMixed();
            }
        }
    }
}
?>