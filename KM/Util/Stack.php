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

/**
 * The <code>Stack</code> class represents a last-in-first-out (LIFO) stack of
 * objects.
 * It extends class <tt>Vector</tt> with five operations that allow a vector to
 * be treated as a stack. The usual <tt>push</tt> and <tt>pop</tt> operations
 * are provided, as well as a method to <tt>peek</tt> at the top item on the
 * stack, a method to test for whether the stack is <tt>empty</tt>, and a method
 * to <tt>search</tt> the stack for an item and discover how far it is from the
 * top.
 *
 * <p> When a stack is first created, it contains no items.
 *
 * <p>A more complete and consistent set of LIFO stack operations is provided by
 * the <code>Deque</code> interface and its implementations, which should be
 * used in preference to this class. For example:
 * <pre>
 * <code>Deque<Integer> stack = new ArrayDeque<Integer>();</code>
 * </pre>
 *
 * @author Blair
 */
class Stack extends Vector
{

    /**
     * Creates an empty stack.
     *
     * @param string $typeParameter A value denoting the type parameter declared
     *        by this GenericDeclaration object.
     */
    public function __construct($typeParameter)
    {
        parent::__construct($typeParameter);
    }

    /**
     * Pushes an item onto the top of this stack.
     * This has exactly the same effect as addElement($item).
     *
     * @param mixed $item The item to be pushed onto this stack.
     * @return mixed The item argument.
     */
    public function push($item)
    {
        $this->testTypeParameters($item);
        $this->addElement($item);
        return $item;
    }

    /**
     * Removes the object at the top of this stack and returns that object as
     * the value of this function.
     *
     * @return mixed The object at the top of this stack (the last item of the
     *         Vector object).
     * @throws EmptyStackException if this stack is empty.
     */
    public function pop()
    {
        $len = $this->size();
        $obj = $this->peek();
        $this->removeElementAt($len - 1);
        return $obj;
    }

    /**
     * Looks at the object at the top of this stack without removing it from the
     * stack.
     *
     * @return mixed The object at the top of the stack (the last item of the
     *         Vector object.)
     * @throws EmptyStackException if this stack is empty.
     */
    public function peek()
    {
        $len = $this->size();
        if ($len == 0) {
            throw new EmptyStackException();
        }
        return $this->elementAt($len - 1);
    }

    /**
     * Tests if this stack is empty.
     *
     * @return boolean True is and only if this stack contains no items; false
     *         otherwise.
     * @see \KM\Util\Vector::isEmpty()
     */
    public function isEmpty()
    {
        return $this->size() == 0;
    }

    /**
     * Returns the 1-based position where an object is on this stack.
     *
     * If the object <tt>o</tt> occurs as an item in this stack, this method
     * returns the distance from the top of the stack of the occurrence nearest
     * the top of the stack; the topmost item on the stack is considered to be
     * at distance <tt>1</tt>. The <tt>equals</tt> method is used to compare
     * <tt>o</tt> to the items in this stack.
     *
     * @param mixed $o The desired object
     * @return int The 1-based position from the top of the stack where the
     *         object is located. The
     *         return value <code>-1</code> indicates that the object is not on
     *         the stack.
     */
    public function search($o)
    {
        $i = $this->lastIndexOf($o);
        if ($i >= 0) {
            return $this->size() - $i;
        }
        return -1;
    }
}
?>