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
namespace KM\IO\ObjectOutputStream;

use KM\Lang\Object;

/**
 * Object wrapper for a PHP hash table which maps objects to integer handles
 * assigned in ascending order.
 *
 * @author Blair
 */
class HandleTable extends Object
{

    /**
     * The underlying handle table.
     *
     * @var mixed[]
     */
    private $objs;

    /**
     * The size of the table.
     *
     * @var int
     */
    private $size = 0;

    /**
     * Creates a new HandleTable.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Assigns the next available handle to the given object and returns the
     * handle value.
     * Handles are assigned in ascending order starting at 0.
     *
     * @param mixed $obj
     * @return int
     */
    public function assign($obj)
    {
        if (($handle = $this->lookup($obj)) != -1) {
            return $handle;
        }
        $handle = $this->size;
        $this->objs[$handle] = $obj;
        $this->size++;
        return $handle;
    }

    /**
     * Looks up and returns the handle associated with the given object, or -1
     * if no mapping found.
     *
     * @param mixed $obj
     * @return int
     */
    public function lookup($obj)
    {
        if ($this->size == 0) {
            return -1;
        }
        // We must set the third argument to true; otherwise the function will
        // attempt to cast the second argument (the haystack) to the same type
        // as the first (the needle).
        if (($handle = array_search($obj, $this->objs, true)) !== false) {
            return $handle;
        }
        return -1;
    }

    /**
     * Resets the table to its initial empty state.
     */
    public function clear()
    {
        $this->objs = [];
        $this->size = 0;
    }

    /**
     * Returns the number of mappings currently in the table.
     *
     * @return int
     */
    public function size()
    {
        return $this->size;
    }
}
?>