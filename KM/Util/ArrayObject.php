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

use KM\IO\InvalidObjectException;
use KM\IO\IOException;
use KM\IO\ObjectInputStream;
use KM\IO\ObjectOutputStream;
use KM\IO\ObjectStreamConstants;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\ClassCastException;
use KM\Lang\ClassNotFoundException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\Reflect\PrimitiveType;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Util\ArrayObject\Itr;
use KM\Util\ArrayObject\Node;
use KM\Util\Iterator;

/**
 * An object to replace a PHP array.
 * No backing arrays exist in this collection,
 * making it suitable for non-PHP standard serialization.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ArrayObject extends Object implements \IteratorAggregate, \ArrayAccess,
    Serializable
{

    /**
     * The number of elements in this array object.
     *
     * @var int @Transient
     */
    private $size = 0;

    /**
     * Pointer to the first element in the stack
     *
     * @var \KM\Util\ArrayObject\Node @Transient
     */
    private $first = null;

    /**
     * Pointer to the last element in the stack
     *
     * @var \KM\Util\ArrayObject\Node @Transient
     */
    private $last = null;

    /**
     * Constructs a new ArrayObject with the given input.
     *
     * @param array $input
     */
    public function __construct(array $input = null)
    {
        if ($input != null) {
            $this->putAll($input);
        }
    }

    /**
     * Returns the first element in this collection or null if this collection
     * is empty.
     *
     * @return \KM\Util\ArrayObject\Node
     */
    public function getFirst()
    {
        return $this->first;
    }

    /**
     * Returns the number of key-value mappings in this collection.
     *
     * @return int
     */
    public function size()
    {
        return $this->size;
    }

    /**
     * Returns <code>true</code> if this collection contains no elements.
     *
     * @return boolean <code>true</code> if this collection contains no
     *         elements.
     */
    public function isEmpty()
    {
        return ($this->size == 0);
    }

    /**
     * Returns the value to which the specified key is mapped or
     * <code>null</code> if this collection contains no mapping for the key.
     *
     * @param mixed $key The key whose associates value is to be returned,
     * @return mixed The value to which the specified key is mapped, or
     *         <code>null</code> if this collection contains no mapping for the
     *         key.
     * @throws NullPointerException if the specified key is null.
     * @throws ClassCastException if the key is neither an integer nor a string.
     */
    public function get($key)
    {
        return (($e = $this->getNode($key)) == null) ? null : $e->getValue();
    }

    /**
     * Returns the first node whose key matches the given key.
     *
     * @param mixed $key
     * @throws NullPointerException if the specified key is null.
     * @throws ClassCastException if the key is neither an integer nor a string.
     * @return NULL \KM\Util\ArrayObject\Node
     */
    private function getNode($key)
    {
        /* @var $e Node */
        if ($key === null) {
            throw new NullPointerException();
        }
        if (!is_int($key) && !is_string($key)) {
            throw new ClassCastException('keys must be integers or strings');
        }
        
        if ($this->size == 0) {
            return null;
        }
        
        $first = $this->first;
        if ($first->getKey() === $key) {
            return $first;
        }
        if (($e = $first->next) != null) {
            do {
                if ($e->getKey() === $key) {
                    return $e;
                }
            } while (($e = $e->next) != null);
        }
        return null;
    }

    /**
     * Returns <code>true</code> if this collection contains a mapping for the
     * specified key.
     *
     * @param mixed $key The key whose presence in this collection is to be
     *        tested.
     * @return boolean <code>true</code> if this collection contains a mapping
     *         for the specified key.
     * @throws NullPointerException if the specified key is null.
     * @throws ClassCastException if the key is neither an integer nor a string.
     */
    public function containsKey($key)
    {
        return $this->getNode($key) != null;
    }

    /**
     * Returns <code>true</code> if this collection maps one or more keys to the
     * specified value.
     *
     * @param mixed $value The value whose presence in this collection is to be
     *        tested.
     * @return boolean <code>true</code> if this collection maps one or more
     *         keys to the specified value.
     */
    public function containsValue($value)
    {
        if ($this->size == 0) {
            return false;
        }
        $first = $this->first;
        if ($first->getValue() == $value) {
            return true;
        }
        if (($e = $first->next) != null) {
            do {
                if ($e->getValue() == $value) {
                    return true;
                }
            } while (($e = $e->next) != null);
        }
        return false;
    }

    /**
     * Associates the specified value with the specified key in this collection.
     * If the collection previously contained a mapping for the key, the old
     * value is replaced by the specified value.
     *
     * @param mixed $key The key with which the specified value is to be
     *        associated. If no value is given, a numeric key will be
     *        assigned, representing an imputed zero-based indexing of the
     *        collection.
     * @param mixed $value The value to be associated with the specified key.
     * @return mixed The previous value associated with the specified key or
     *         <code>null</code> if there was no previous mapping for the key. A
     *         <code>null</code> return may also indicate that the map
     *         previously associated <code>null</code> with the key.
     * @throws ClassCastException if the key, if specified, is neither an
     *         integer nor a string.
     */
    public function put($key, $value)
    {
        return $this->putVal($key, $value);
    }

    /**
     * Associates the specified value with the specified key in this collection.
     *
     * @param mixed $key The key with which the specified value is to be
     *        associated. If no value is given, a numeric key will be
     *        assigned, representing an imputed zero-based indexing of the
     *        collection.
     * @param mixed $value The value to be associated with the specified key.
     * @return mixed The previous value associated with the specified key or
     *         <code>null</code> if there was no previous mapping for the key. A
     *         <code>null</code> return may also indicate that the map
     *         previously associated <code>null</code> with the key.
     * @throws ClassCastException if the key, if specified, is neither an
     *         integer nor a string.
     */
    private function putVal($key, $value)
    {
        if ($key === null) {
            $key = $this->size;
        }
        if (!is_int($key) && !is_string($key)) {
            throw new ClassCastException(
                'only integer and string types permitted for keys');
        }
        
        $node = $this->getNode($key);
        if ($node != null) {
            $oldValue = $node->getValue();
            $node->setValue($value);
            return $oldValue;
        }
        $node = new Node($this->last, $key, $value, null);
        if ($this->size == 0) {
            $this->first = $node;
        } else {
            $this->last->next = $node;
        }
        $this->size++;
        $this->last = $node;
        return null;
    }

    /**
     * Copies all of the elements from the specified array into this array
     * object.
     *
     * @param array $c mAppings to be stored in this collection.
     */
    public function putAll(array $c)
    {
        foreach ($c as $key => $value) {
            $this->putVal($key, $value);
        }
    }

    /**
     * Removes the mapping for a key from this collection if it is present.
     *
     * @param mixed $key The key whose mapping is to be removed from this
     *        collection.
     * @return mixed The previous value associated with the specified key, or
     *         <code>null</code> if there was no mapping for the key.
     * @throws ClassCastException if the key is neither an integer nor a string.
     */
    public function remove($key)
    {
        return (($e = $this->removeNode($key)) == null) ? null : $e->getValue();
    }

    /**
     * Removes the mapping for a key from this collection if it is present.
     *
     * @param mixed $key The key whose mapping is to be removed from this
     *        collection.
     * @return mixed The previous value associated with the specified key, or
     *         <code>null</code> if there was no mapping for the key.
     * @throws ClassCastException if the key is neither an integer nor a string.
     */
    private function removeNode($key)
    {
        $node = $this->getNode($key);
        if ($node != null) {
            $prev = $node->prev;
            $next = $node->next;
            if ($prev != null) {
                $prev->next = $next;
            } else {
                $this->first = $next;
            }
            if ($next != null) {
                $next->prev = $prev;
            } else {
                $this->last = $prev;
            }
            $this->size--;
            return $node;
        }
        return null;
    }

    /**
     * Removes all of the mappings from this collection.
     * The collection will be
     * empty after this call returns.
     */
    public function clear()
    {
        if ($this->first != null) {
            if (($e = $this->first->next) != null) {
                do {
                    $e->setValue(null);
                    $e->prev = null;
                } while (($e = $e->next) != null);
            }
        }
        $this->last = null;
        $this->first = null;
        $this->size = 0;
    }
    
    /* ---------- View methods ---------- */
    
    /**
     * Returns the external iterator.
     *
     * @return \KM\Util\Iterator
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new Itr($this);
    }

    /**
     * Returns a view of the keys contained in this collection.
     * The set is not
     * backed by the collection, so any changes to the collection are NOT
     * reflected in the set, and vice-versa.
     *
     * @return \KM\Util\ArrayObject
     */
    public function keySet()
    {
        $arr = [];
        if (($e = $this->first) != null) {
            do {
                $arr[] = $e->getKey();
            } while (($e = $e->next) != null);
        }
        return new self($arr);
    }

    /**
     * Returns a view of the values contained in this collection.
     * The set is not
     * backed by the collection, so any changes to the collection are NOT
     * reflected in the set, and vice-versa.
     *
     * @return \KM\Util\ArrayObject
     */
    public function values()
    {
        $arr = [];
        if (($e = $this->first) != null) {
            do {
                $arr[] = $e->getValue();
            } while (($e = $e->next) != null);
        }
        return new self($arr);
    }

    /**
     * Returns an array containing all of the elements in this collection in the
     * proper sequence (from first to last element).
     *
     * @return array
     */
    public function toArray()
    {
        $a = [];
        if (($e = $this->first) != null) {
            do {
                $key = $e->getKey();
                $value = $e->getValue();
                $a[$key] = $value;
            } while (($e = $e->next) != null);
        }
        return $a;
    }
    
    /* ---------- Sorting methods ---------- */
    
    /**
     * Sorts the elements in this array object by key, maintaining key to data
     * correlations.
     * This is useful mainly for associative arrays.
     *
     * @return boolean <code>true</code> on success, <code>false</code>
     *         otherwise.
     */
    public function ksort()
    {
        $a = $this->toArray();
        $success = ksort($a);
        if ($success) {
            $this->clear();
            $this->putAll($a);
            return true;
        }
        return false;
    }

    /**
     * Sorts the elements in this array object by key in reverse order,
     * maintaining key to data correlations.
     * This is useful mainly for
     * associative arrays.
     *
     * @return boolean <code>true</code> on success, <code>false</code>
     *         otherwise.
     */
    public function krsort()
    {
        $a = $this->toArray();
        $success = krsort($a);
        if ($success) {
            $this->clear();
            $this->putAll($a);
            return true;
        }
        return false;
    }

    /**
     * This function implements a sort algorithm that orders alphanumeric
     * strings in the way a human being would while maintaining key/value
     * associations.
     *
     * @return boolean <code>true</code> on success, <code>false</code>
     *         otherwise.
     */
    public function natsort()
    {
        $a = $this->toArray();
        $success = natsort($a);
        if ($success) {
            $this->clear();
            $this->putAll($a);
            return true;
        }
        return false;
    }

    /**
     * Sort an array using a case insensitive "natural order" algorithm.This
     * function implements a sort algorithm that orders alphanumeric strings in
     * the way a human being would while maintaining key/value associations.
     * This is described as a "natural ordering".
     *
     * @return boolean <code>true</code> on success, <code>false</code>
     *         otherwise.
     */
    public function natcasesort()
    {
        $a = $this->toArray();
        $success = natcasesort($a);
        if ($success) {
            $this->clear();
            $this->putAll($a);
            return true;
        }
        return false;
    }

    /**
     * Sorts an array.
     * Elements will be arranged from lowest to highest when
     * this function has completed.
     *
     * @return boolean <code>true</code> on success, <code>false</code>
     *         otherwise.
     */
    public function sort()
    {
        $a = $this->toArray();
        $success = sort($a);
        if ($success) {
            $this->clear();
            $this->putAll($a);
            return true;
        }
        return false;
    }

    /**
     * Sorts an array in reverse order.
     * This function sorts an array in reverse
     * order (highest to lowest).
     *
     * @return boolean <code>true</code> on success, <code>false</code>
     *         otherwise.
     */
    public function rsort()
    {
        $a = $this->toArray();
        $success = rsort($a);
        if ($success) {
            $this->clear();
            $this->putAll($a);
            return true;
        }
        return false;
    }
    
    /* ---------- ArrayAccess implementation ---------- */
    
    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset The offset to check.
     * @return boolean <code>True</code> if the specified offset exists in this
     *         object, <code>false</code> otherwise.
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     * @return mixed The value mapped at the specified offset.
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->put($offset, $value);
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset.
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
    
    /* ---------- Serialization methods ---------- */
    
    /**
     * Saves the state of this instance to the specified stream, i.e.
     * serializes
     * it.
     *
     * @param ObjectOutputStream $s
     * @throws IOException if an I/O error occurs
     */
    private function writeObject(ObjectOutputStream $s)
    {
        // Write out any serialization
        $s->defaultWriteObject();
        
        // Write out size
        $s->writeInt($this->size);
        
        // Write out all elements in the proper order
        for ($e = $this->first; $e != null; $e = $e->next) {
            $s->writeMixed($e->getKey());
            $s->writeMixed($e->getValue());
        }
    }

    /**
     * Reconstitutes the instance from the specified stream, i.e.
     * deserializes
     * it.
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
        $mappings = $s->readInt();
        echo '<pre>mappings: '; print_r($mappings); echo '</pre>'; die();
        if ($mappings < 0) {
            throw new InvalidObjectException(
                'Illegal mappings count: ' . $mappings);
        } elseif ($mappings > 0) {
            // Read in all elements in the proper order
            for ($i = 0; $i < $mappings; $i++) {
                $key = $s->readMixed();
                $value = $s->readMixed();
                $this->putVal($key, $value);
            }
        }
    }
}
?>