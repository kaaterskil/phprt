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
namespace KM\IO\ObjectInputStream\HandleTable;

use KM\Lang\ArrayIndexOutOfBoundsException;
use KM\Lang\Object;

/**
 * Simple list of integer handles.
 *
 * @author Blair
 */
class HandleList extends Object
{

    /**
     * The backing handle list.
     *
     * @var int[]
     */
    private $list = [];

    /**
     * The number of handles in the list.
     *
     * @var int
     */
    private $size = 0;

    /**
     * Constructs a new HandleList.
     */
    public function __construct()
    {}

    /**
     * Adds the given handle to the list at the next available index.
     *
     * @param int $handle
     */
    public function add($handle)
    {
        $this->list[$this->size++] = $handle;
    }

    /**
     * Returns hte handle at the given index.
     *
     * @param int $index
     * @throws ArrayIndexOutOfBoundsException
     * @return int
     */
    public function get($index)
    {
        if (!isset($this->list[$index]) || $index >= $this->size) {
            throw new ArrayIndexOutOfBoundsException();
        }
        return $this->list[$index];
    }

    /**
     * Returns the number of handles in the list.
     *
     * @return int
     */
    public function size()
    {
        return $this->size;
    }
}
?>