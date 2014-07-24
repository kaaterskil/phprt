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

use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\Util\Iterator;
use KM\Util\ListHelpers\Itr;
use KM\Util\ListHelpers\ListItr;

/**
 * This class provides a skeletal implementation of the List interface to
 * minimize the effort required to implement this interface backed by a "random
 * access" data store (such as an array). For sequential access data (such as a
 * linked list), AbstractSequentialList should be used in preference to this
 * class. To implement an unmodifiable list, the programmer needs only to extend
 * this class and provide implementations for the get(int) and size() methods.
 * To implement a modifiable list, the programmer must additionally override the
 * set(int, E) method (which otherwise throws an UnsupportedOperationException).
 * If the list is variable-size the programmer must additionally override the
 * add(int, E) and remove(int) methods. The programmer should generally provide
 * a void (no argument) and collection constructor, as per the recommendation in
 * the Collection interface specification. Unlike the other abstract collection
 * implementations, the programmer does not have to provide an iterator
 * implementation; the iterator and list iterator are implemented by this class,
 * on top of the "random access" methods: get(int), set(int, E), add(int, E) and
 * remove(int). The documentation for each non-abstract method in this class
 * describes its implementation in detail. Each of these methods may be
 * overridden if the collection being implemented admits a more efficient
 * implementation.
 *
 * @author Blair
 */
abstract class AbstractList extends AbstractCollection implements ListInterface
{

    /**
     * The number of times this list has been <i>structurally modified</i>.
     * Structural modifications are those that change the size of the list, or
     * otherwise perturb it in such a fashion that iterations in progress may
     * yield incorrect results. <p>This field is used by the iterator and list
     * iterator implementation returned by the <code>iterator</code>and
     * <code>listIterator</code>methods. If the value of this field changes
     * unexpectedly, the iterator (or list iterator) will throw a
     * <code>ConcurrentModificationException</code>in response to the
     * <code>next</code>, <code>remove</code>, <code>previous</code>,
     * <code>set</code>or <code>add</code>operations. This provides
     * <i>fail-fast</i> behavior, rather than non-deterministic behavior in the
     * face of concurrent modification during iteration. <p><b>Use of this field
     * by subclasses is optional.</b> If a subclass wishes to provide fail-fast
     * iterators (and list iterators), then it merely has to increment this
     * field in its <code>add(int, E)</code>and <code>remove(int)</code>methods
     * (and any other methods that it overrides that result in structural
     * modifications to the list). A single call to <code>add(int, E)</code>or
     * <code>remove(int)</code>must add no more than one to this field, or the
     * iterators (and list iterators) will throw bogus
     * <code>ConcurrentModificationExceptions</code>. If an implementation does
     * not wish to provide fail-fast iterators, this field may be ignored.
     *
     * @var int
     */
    protected $modCount = 0;

    /**
     * Sole constructor, with the given type parameter.
     *
     * @param string $typeParameter A value denoting the type parameter declared
     *            by this GenericDeclaration object.
     */
    protected function __construct($typeParameter = null)
    {
        parent::__construct($typeParameter);
    }

    /**
     * Appends the specified element to the end of this list (optional
     * operation). Lists that support this operation may place limitations on
     * what elements may be added to this list. In particular, some lists will
     * refuse to add null elements, and others will impose restrictions on the
     * type of elements that may be added. List classes should clearly specify
     * in their documentation any restrictions on what elements may be added.
     * This implementation calls set(size(), e). Note that this implementation
     * throws an UnsupportedOperationException unless set(int, E) is overridden.
     *
     * @param mixed $e
     * @return boolean
     * @see \KM\Util\AbstractCollection::add()
     */
    public function add($e)
    {
        $this->testTypeParameters($e);
        $this->set($this->size(), $e);
        return true;
    }

    /**
     * Returns the element at the specified position in this list.
     *
     * @param unknown $index
     * @see \KM\Util\ListInterface::get()
     */
    public abstract function get($index);

    /**
     * This implementation always throws an UnsupportedOperationException.
     *
     * @param int $index
     * @param mixed $element
     * @throws UnsupportedOperationException
     * @see \KM\Util\ListInterface::set()
     */
    public function set($index, $element)
    {
        throw new UnsupportedOperationException();
    }

    /**
     * This implementation always throws an UnsupportedOperationException.
     *
     * @param int $index
     * @param mixed $element
     * @throws UnsupportedOperationException
     * @see \KM\Util\ListInterface::addAtIndex()
     */
    public function addAt($index, $element)
    {
        throw new UnsupportedOperationException();
    }

    /**
     * This implementation always throws an UnsupportedOperationException.
     *
     * @param mixed $index
     * @throws UnsupportedOperationException
     * @see \KM\Util\ListInterface::remove()
     */
    public function removeAt($index)
    {
        throw new UnsupportedOperationException();
    }
    
    /* ---------- Search Operations ---------- */
    
    /**
     * Returns the index of the first occurrence of the specified element in
     * this list, or -1 if this list does not contain the element. More
     * formally, returns the lowest index i such that (o==null ? get(i)==null :
     * o.equals(get(i))), or -1 if there is no such index. This implementation
     * first gets a list iterator (with listIterator()). Then, it iterates over
     * the list until the specified element is found or the end of the list is
     * reached.
     *
     * @param mixed $o
     * @return int
     * @see \KM\Util\ListInterface::indexOf()
     */
    public function indexOf($o = null)
    {
        $it = $this->listIterator();
        if ($o == null) {
            while ($it->hasNext()) {
                if ($it->next() == null) {
                    return $it->previousIndex();
                }
            }
        } else {
            while ($it->hasNext()) {
                if ($o === $it->next()) {
                    return $it->previousIndex();
                }
            }
        }
        return - 1;
    }

    /**
     * Returns the index of the last occurrence of the specified element in this
     * list, or -1 if this list does not contain the element. More formally,
     * returns the highest index i such that (o==null ? get(i)==null :
     * o.equals(get(i))), or -1 if there is no such index. This implementation
     * first gets a list iterator that points to the end of the list (with
     * listIterator(size())). Then, it iterates backwards over the list until
     * the specified element is found, or the beginning of the list is reached.
     *
     * @param mixed $o
     * @return int
     * @see \KM\Util\ListInterface::lastIndexOf()
     */
    public function lastIndexOf($o = null)
    {
        $it = $this->listIterator();
        if ($o == null) {
            while ($it->hasPrevious()) {
                if ($it->previous() == null) {
                    return $it->nextIndex();
                }
            }
        } else {
            while ($it->hasPrevious()) {
                if ($o === $it->previous()) {
                    return $it->nextIndex();
                }
            }
        }
    }
    
    /* ---------- Bulk Operations ---------- */
    
    /**
     * Removes all of the elements from this list (optional operation). The list
     * will be empty after this call returns. This implementation calls
     * removeRange(0, size()). Note that this implementation throws an
     * UnsupportedOperationException unless remove(int index) or removeRange(int
     * fromIndex, int toIndex) is overridden.
     *
     * @see \KM\Util\AbstractCollection::clear()
     */
    public function clear()
    {
        $this->removeRange(0, $this->size());
    }

    /**
     * Inserts all of the elements in the specified collection into this list at
     * the specified position (optional operation). Shifts the element currently
     * at that position (if any) and any subsequent elements to the right
     * (increases their indices). The new elements will appear in this list in
     * the order that they are returned by the specified collection's iterator.
     * The behavior of this operation is undefined if the specified collection
     * is modified while the operation is in progress. (Note that this will
     * occur if the specified collection is this list, and it's nonempty.) This
     * implementation gets an iterator over the specified collection and
     * iterates over it, inserting the elements obtained from the iterator into
     * this list at the appropriate position, one at a time, using
     * addAtIndex(int, E). Many implementations will override this method for
     * efficiency.
     *
     * @param int $index
     * @param Collection $c
     * @return boolean
     * @see \KM\Util\ListInterface::addAllAt()
     */
    public function addAllAt($index, Collection $c)
    {
        $modified = false;
        foreach ($c as $e) {
            $this->addAt($index ++, $e);
            $modified = true;
        }
        return $modified;
    }
    
    /* ---------- Iterators ---------- */
    
    /**
     * Returns an iterator over the elements in this list in proper sequence.
     * This implementation returns a straightforward implementation of the
     * iterator interface, relying in the lacking list's size(), get() and
     * remove() methods.
     *
     * @return \KM\Util\Iterator
     * @see \KM\Util\AbstractCollection::getIterator()
     */
    public function getIterator()
    {
        return new Itr($this);
    }

    /**
     * <p>This implementation returns a straightforward implementation of the
     * <code>ListIterator</code>interface that extends the implementation of the
     * <code>Iterator</code>interface returned by the
     * <code>iterator()</code>method. The
     * <code>ListIterator</code>implementation relies on the backing list's
     * <code>get(int)}, <code>set(int, E)}, <code>add(int, E)} and
     * <code>remove(int)</code>methods. <p>Note that the list iterator returned
     * by this implementation will throw an
     * <code>UnsupportedOperationException</code>in response to its
     * <code>remove}, <code>set</code>and <code>add</code>methods unless the
     * list's <code>remove(int)}, <code>set(int, E)}, and <code>add(int,
     * E)</code>methods are overridden. <p>This implementation can be made to
     * throw runtime exceptions in the face of concurrent modification, as
     * described in the specification for the (protected)
     * <code>#modCount</code>field.
     *
     * @param int $index
     * @return \KM\Util\Iterator
     * @see \KM\Util\ListInterface::listIterator()
     */
    public function listIterator($index = 0)
    {
        return new ListItr($index, $this);
    }
    
    /* ---------- Comparison ---------- */
    
    /**
     * Compares the specified object with this list for equality. Returns
     * <code>true</code>if and only if the specified object is also a list, both
     * lists have the same size, and all corresponding pairs of elements in the
     * two lists are <i>equal</i>. (Two elements <code>e1</code>and
     * <code>e2</code>are <i>equal</i> if <code>(e1==null ? e2==null :
     * e1.equals(e2))}.) In other words, two lists are defined to be equal if
     * they contain the same elements in the same order.<p> This implementation
     * first checks if the specified object is this list. If so, it returns
     * <code>true}; if not, it checks if the specified object is a list. If not,
     * it returns <code>false}; if so, it iterates over both lists, comparing
     * corresponding pairs of elements. If any comparison returns <code>false},
     * this method returns <code>false}. If either iterator runs out of elements
     * before the other it returns <code>false</code>(as the lists are of
     * unequal length); otherwise it returns <code>true</code>when the
     * iterations complete.
     *
     * @param Object $obj
     * @return boolean
     * @see \KM\Lang\Object::equals()
     */
    public function equals(Object $obj = null)
    {
        if ($obj == null) {
            return false;
        }
        if ($obj === $this) {
            return true;
        }
        if (! $obj instanceof ListInterface) {
            return false;
        }
        $e1 = $this->listIterator();
        $e2 = $obj->listIterator();
        while ($e1->hasNext() && $e2->hasNext()) {
            $o1 = $e1->next();
            $o2 = $e2->next();
            if (! ($o1 == null ? $o2 == null : $o1 == $o2)) {
                return false;
            }
        }
        return ! ($e1->hasNext() || $e2->hasNext());
    }

    /**
     * Removes from this list all of the elements whose index is between
     * fromIndex, inclusive, and toIndex, exclusive. Shifts any succeeding
     * elements to the left (reduces their index). This call shortens the list
     * by (toIndex - fromIndex) elements. (If toIndex==fromIndex, this operation
     * has no effect.) This method is called by the clear operation on this list
     * and its subLists. Overriding this method to take advantage of the
     * internals of the list implementation can substantially improve the
     * performance of the clear operation on this list and its subLists. This
     * implementation gets a list iterator positioned before fromIndex, and
     * repeatedly calls ListIterator.next followed by ListIterator.remove until
     * the entire range has been removed. Note: if ListIterator.remove requires
     * linear time, this implementation requires quadratic time.
     *
     * @param int $fromIndex
     * @param int $toIndex
     */
    protected function removeRange($fromIndex, $toIndex)
    {
        $it = $this->listIterator($fromIndex);
        $n = $toIndex - $fromIndex;
        for ($i = 0; $i < $n; $i ++) {
            $it->next();
            $it->remove();
        }
    }
}
?>