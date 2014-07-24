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
namespace KM\IO\ObjectInputStream;

use KM\IO\ObjectInputStream;
use KM\IO\ObjectInputStream\HandleTable\HandleList;
use KM\Lang\ClassNotFoundException;
use KM\Lang\Object;

/**
 * Unsynchronized table which tracks wire handle to object mappings, as well as
 * ClassNotFoundExceptions associated with deserialized objects.
 * This class implements an exception-propagation algorithm for determining
 * which objects should have ClassNotFoundExceptions associated with them,
 * taking into account cycles and discontinuities (e.g., skipped fields) in the
 * object graph.
 *
 * <p>General use of the table is as follows: during deserialization, a given
 * object is first assigned a handle by calling the assign method. This method
 * leaves the assigned handle in an "open" state, wherein dependencies on the
 * exception status of other handles can be registered by calling the
 * markDependency method, or an exception can be directly associated with the
 * handle by calling markException. When a handle is tagged with an exception,
 * the HandleTable assumes responsibility for propagating the exception to any
 * other objects which depend (transitively) on the exception-tagged object.
 *
 * <p>Once all exception information/dependencies for the handle have been
 * registered, the handle should be "closed" by calling the finish method on it.
 * The act of finishing a handle allows the exception propagation algorithm to
 * aggressively prune dependency links, lessening the performance/memory impact
 * of exception tracking.
 *
 * <p>Note that the exception propagation algorithm used depends on handles
 * being assigned/finished in LIFO order; however, for simplicity as well as
 * memory conservation, it does not enforce this constraint.
 *
 * @author Blair
 */
class HandleTable extends Object
{

    private static $STATUS_OK = -1;

    private static $STATUS_UNKNOWN = 2;

    private static $STATUS_EXCEPTION = 3;

    /**
     * Array mapping handle -> object status.
     *
     * @var int[]
     */
    public $status;

    /**
     * Array mapping handle => object/exception (depending on status).
     *
     * @var mixed[]
     */
    public $entries;

    /**
     * Array mapping handle => list of dependent handles (if any).
     *
     * @var HandleList[]
     */
    public $deps;

    /**
     * Lowest unresolved dependency.
     *
     * @var int
     */
    public $lowDep = -1;

    /**
     * The number of handles in the table.
     *
     * @var int
     */
    public $size = 0;

    /**
     * Creates a new HandleTable.
     */
    public function __construct()
    {
        $this->status = [];
        $this->entries = [];
        $this->deps = [];
    }

    /**
     * Assigns the next available handle to the given object and returns the
     * assigned handle.
     * Once object has been completely deserialized (and all dependencies and
     * other objects identified), the handle should be closed by passing it to
     * finish().
     *
     * @param mixed $obj
     * @return int
     */
    public function assign($obj)
    {
        $this->status[$this->size] = self::$STATUS_UNKNOWN;
        $this->entries[$this->size] = $obj;
        return $this->size++;
    }

    /**
     * Registers a dependency (in exception status) of one handle on
     * another.
     * The dependent handle must be "open" (i.e., assigned, but not finished
     * yet). No action is taken if either dependent or target handle is
     * NULL_HANDLE.
     *
     * @param int $dependent
     * @param int $target
     */
    public function markDependency($dependent, $target)
    {
        if ($dependent == ObjectInputStream::NULL_HANDLE ||
             $target == ObjectInputStream::NULL_HANDLE) {
            return;
        }
        switch ($this->status[$dependent]) {
            case self::$STATUS_UNKNOWN:
                switch ($this->status[$target]) {
                    case self::$STATUS_OK:
                        // Ignore dependencies on objects with no exception.
                        break;
                    
                    case self::$STATUS_EXCEPTION:
                        // Eagerly propagate exception
                        $this->markException($dependent,
                            $this->entries[$target]);
                        break;
                    
                    case self::$STATUS_UNKNOWN:
                        if (!isset($this->deps[$target]) ||
                             $this->deps[$target] === null) {
                            $this->deps[$target] = new HandleList();
                        }
                        $this->deps[$target]->add($dependent);
                        
                        // Remember lowest unresolved target seem.
                        if ($this->lowDep < 0 || $this->lowDep > $target) {
                            $this->lowDep = $target;
                        }
                        break;
                    
                    default:
                        trigger_error();
                }
                break;
            
            case self::$STATUS_EXCEPTION:
                break;
            
            default:
                trigger_error();
        }
    }

    /**
     * Associates a ClassNotFoundException (if one not already associated) with
     * the currently active handle and propagates it to other referencing
     * objects as appropriate.
     * The specified handle must be "open" (i.e., assigned, but not finished
     * yet).
     *
     * @param int $handle
     * @param ClassNotFoundException $ex
     */
    public function markException($handle, ClassNotFoundException $ex)
    {
        /* @var $dlist HandleList */
        switch ($this->status[$handle]) {
            case self::$STATUS_UNKNOWN:
                $this->status[$handle] = self::$STATUS_EXCEPTION;
                $this->entries[$handle] = $ex;
                
                // Propagate exception to dependents.
                $dlist = $this->deps[$handle];
                if ($dlist != null) {
                    $ndeps = $dlist->size();
                    for ($i = 0; $i < $ndeps; $i++) {
                        $this->markException($dlist->get($i), $ex);
                    }
                    $this->deps[$handle] = null;
                }
                break;
            
            case self::$STATUS_EXCEPTION:
                break;
            
            default:
                trigger_error();
        }
    }

    /**
     * Marks given handle as finished, meaning that no new dependencies will be
     * marked for handle.
     * Calls to the assign and finish methods must occur in LIFO order.
     *
     * @param int $handle
     */
    public function finish($handle)
    {
        $end;
        if ($this->lowDep < 0) {
            // No pending unknowns, only resolve current handle.
            $end = $handle + 1;
        } elseif ($this->lowDep >= $handle) {
            // PEnding unknowns are clearable, resolve all upward handles.
            $end = $this->size;
            $this->lowDep = -1;
        } else {
            // Unresolved back refs present, can't resolve anything yet.
            return;
        }
        
        for ($i = $handle; $i < $end; $i++) {
            switch ($this->status[$i]) {
                case self::$STATUS_UNKNOWN:
                    $this->status[$i] = self::$STATUS_OK;
                    $this->deps[$i] = null;
                    break;
                
                case self::$STATUS_OK:
                case self::$STATUS_EXCEPTION:
                    break;
                
                default:
                    trigger_error();
            }
        }
    }

    /**
     * Assigns a new object to the given handle.
     * The object previously associated with the handle is forgotten. This
     * method has no effect if the given handle already has an exception
     * associated with it. This method may be called at any time after the
     * handle is assigned.
     *
     * @param int $handle
     * @param mixed $obj
     */
    public function setObject($handle, $obj)
    {
        switch ($this->status[$handle]) {
            case self::$STATUS_UNKNOWN:
            case self::$STATUS_OK:
                $this->entries[$handle] = $obj;
                break;
            
            case self::$STATUS_EXCEPTION:
                break;
            
            default:
                trigger_error();
        }
    }

    /**
     * Looks up and returns object associated with the given handle.
     * Returns null if the given handle is NULL_HANDLE, or if it has an
     * associated ClassNotFoundException.
     *
     * @param unknown $handle
     * @return Ambigous <NULL, multitype:>
     */
    public function lookupObject($handle)
    {
        return ($handle != ObjectInputStream::NULL_HANDLE &&
             $this->status[$handle] != self::$STATUS_EXCEPTION) ? $this->entries[$handle] : null;
    }

    /**
     * Looks up and returns ClassNotFoundException associated with the given
     * handle.
     * Returns null if the given handle is NULL_HANDLE, or if there is no
     * ClassNotFoundException associated with the handle.
     *
     * @param int $handle
     * @return ClassNotFoundException
     */
    public function lookupException($handle)
    {
        return ($handle != ObjectInputStream::NULL_HANDLE &&
             $this->status[$handle] == self::$STATUS_EXCEPTION) ? $this->entries[$handle] : null;
    }

    /**
     * Resets the table to its initial state.
     */
    public function clear()
    {
        $this->status = [];
        $this->entries = [];
        $this->deps = [];
        $this->lowDep = -1;
        $this->size = 0;
    }

    /**
     * Returns the number of handles registered int he table.
     *
     * @return int
     */
    public function size()
    {
        return $this->size;
    }
}
?>