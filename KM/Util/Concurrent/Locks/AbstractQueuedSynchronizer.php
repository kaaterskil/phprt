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
namespace KM\Util\Concurrent\Locks;

use KM\Lang\Object;
use KM\Lang\InterruptedException;
use KM\Lang\NullPointerException;
use KM\Lang\UnsupportedOperationException;
use KM\Util\Concurrent\Locks\Node;

/**
 * Provides a framework for implementing blocking locks and related
 * synchronizers (semaphores, events, etc) that rely on first-in-first-out
 * (FIFO) wait queues. This class is designed to be a useful basis for most
 * kinds of synchronizers that rely on a single atomic int value to represent
 * state. Subclasses must define the protected methods that change this state,
 * and which define what that state means in terms of this object being acquired
 * or released. Given these, the other methods in this class carry out all
 * queuing and blocking mechanics. Subclasses can maintain other state fields,
 * but only the atomically updated int value manipulated using methods
 * getState(), setState(int) and compareAndSetState(int, int) is tracked with
 * respect to synchronization.
 * Subclasses should be defined as non-public internal helper classes that are
 * used to implement the synchronization properties of their enclosing class.
 * Class AbstractQueuedSynchronizer does not implement any synchronization
 * interface. Instead it defines methods such as acquireInterruptibly(int) that
 * can be invoked as appropriate by concrete locks and related synchronizers to
 * implement their public methods.
 * This class supports either or both a default exclusive mode and a shared
 * mode. When acquired in exclusive mode, attempted acquires by other threads
 * cannot succeed. Shared mode acquires by multiple threads may (but need not)
 * succeed. This class does not "understand" these differences except in the
 * mechanical sense that when a shared mode acquire succeeds, the next waiting
 * thread (if one exists) must also determine whether it can acquire as well.
 * Threads waiting in the different modes share the same FIFO queue. Usually,
 * implementation subclasses support only one of these modes, but both can come
 * into play for example in a ReadWriteLock. Subclasses that support only
 * exclusive or only shared modes need not define the methods supporting the
 * unused mode.
 * This class defines a nested AbstractQueuedSynchronizer.ConditionObject class
 * that can be used as a Condition implementation by subclasses supporting
 * exclusive mode for which method isHeldExclusively() reports whether
 * synchronization is exclusively held with respect to the current thread,
 * method release(int) invoked with the current getState() value fully releases
 * this object, and acquire(int), given this saved state value, eventually
 * restores this object to its previous acquired state. No
 * AbstractQueuedSynchronizer method otherwise creates such a condition, so if
 * this constraint cannot be met, do not use it. The behavior of
 * AbstractQueuedSynchronizer.ConditionObject depends of course on the semantics
 * of its synchronizer implementation.
 * This class provides inspection, instrumentation, and monitoring methods for
 * the internal queue, as well as similar methods for condition objects. These
 * can be exported as desired into classes using an AbstractQueuedSynchronizer
 * for their synchronization mechanics.
 * Serialization of this class stores only the underlying atomic integer
 * maintaining state, so deserialized objects have empty thread queues. Typical
 * subclasses requiring serializability will define a readObject method that
 * restores this to a known initial state upon deserialization.
 *
 * @package KM\Util\Concurrent\Locks
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class AbstractQueuedSynchronizer extends Object
{

    /**
     * The wait queue. Not used.
     *
     * @var \SplQueue
     */
    private $queue;

    /**
     * Head of the wait queue, lazily initialized. Except for initialization, it
     * is modified only via method setHead. Note: If head exists, its waitStatus
     * is guaranteed not to be CANCELLED.
     *
     * @var Node
     */
    private $head;

    /**
     * Tail of the wait queue, lazily initialized. Modified only via method
     * enqueue to add new wait node.
     *
     * @var Node
     */
    private $tail;

    /**
     * The synchronization state.
     *
     * @var int
     */
    private $state = 0;
    
    /* ---------- Constructor ---------- */
    
    /**
     * Creates a new AbstractQueuedSynchronizer instance with initial
     * synchronization state of zero.
     */
    protected function __construct()
    {
        // We're not using this queue but rather constructing a virtual one
        // without a
        // container using node.prev and node.next.
        $this->queue = new \SplQueue();
    }
    
    /* ---------- Getter/Setters ---------- */
    
    /**
     * Returns the current value of synchronization state.
     *
     * @return int
     */
    protected final function getState()
    {
        return $this->state;
    }

    /**
     * Sets the value of synchronization state.
     *
     * @param int $newState
     */
    protected final function setState($newState)
    {
        $this->state = (int) $newState;
    }

    /**
     * Sets head of queue to be node, thus dequeuing. Called only by acquire
     * methods.
     *
     * @param Node $node
     * @return Node The old head value
     */
    private function setHead(Node $node)
    {
        $this->head = $node;
        $node->thread = null;
        $node->prev = null;
    }
    
    /* ---------- Methods ---------- */
    
    /**
     * Acquires in shared mode, aborting if interrupted. Implemented by first
     * checking interrupt status, then invoking at least once #tryAcquireShared,
     * returning on success. Otherwise the thread is queued, possibly repeatedly
     * blocking and unblocking, invoking #tryAcquireShared until success or the
     * thread is interrupted.
     *
     * @param int $arg
     */
    public final function acquireSharedInterruptibly($arg)
    {
        if ($this->tryAcquireShared($arg) < 0) {
            $this->doAcquireSharedInterruptibly($arg);
        }
    }

    /**
     * Attempts to acquire in shared mode, aborting if interrupted, and failing
     * if the given timeout elapses. Implemented by first checking interrupt
     * status, then invoking at least once #tryAcquireShared, returning on
     * success. Otherwise, the thread is queued, possibly repeatedly blocking
     * and unblocking, invoking #tryAcquireShared until success or the thread is
     * interrupted or the timeout elapses.
     *
     * @param int $arg
     * @param int $microTimeout
     * @return boolean
     */
    public final function tryAcquireSharedNanos($arg, $microTimeout)
    {
        return $this->tryAcquireShared($arg) >= 0 || $this->doAcquireSharedNanos($arg, $microTimeout);
    }

    /**
     * Releases in shared mode. Implemented by unblocking one or more threads if
     * #tryReleaseShared returns true.
     *
     * @param int $arg
     * @return boolean
     */
    public final function releaseShared($arg)
    {
        if ($this->tryReleaseShared($arg)) {
            $this->doReleaseShared();
            return true;
        }
        return false;
    }

    /**
     * Attempts to acquire in shared mode. This method should query if the state
     * of the object permits it to be acquired in the shared mode, and if so to
     * acquire it.
     * This method is always invoked by the thread performing acquire. If this
     * method reports failure, the acquire method may queue the thread, if it is
     * not already queued, until it is signaled by a release from some other
     * thread.
     * @param int $arg
     * @throws UnsupportedOperationException
     */
    protected function tryAcquireShared($arg)
    {
        throw new UnsupportedOperationException();
    }

    /**
     * Attempts to set the state to reflect a release in shared mode. This
     * method is always invoked by the thread performing release.
     *
     * @param int $arg
     * @throws UnsupportedOperationException
     */
    protected function tryReleaseShared($arg)
    {
        throw new UnsupportedOperationException();
    }

    /**
     * Creates and enqueues node for current thread and given mode.
     *
     * @param Node $mode
     * @return \Application\Stdlib\Concurrent\Node
     */
    private function addWaiter(Node $mode)
    {
        $node = new Node($this->currentThread(), $mode);
        
        // Try the fast path of enqueue; backup to full enqueue on failure
        $pred = $this->tail;
        if ($pred != null) {
            $node->prev = $pred;
            if ($this->compareAndSetTail($pred, $node)) {
                $pred->next = $node;
                return $node;
            }
        }
        $this->enqueue($node);
        return $node;
    }

    /**
     * Cancels an ongoing attempt to acquire.
     *
     * @param Node $node
     */
    private function cancelAcquire(Node $node)
    {
        /* @var $pred Node */
		/* @var $next Node */
		
		if ($node == null) {
            return;
        }
        $node->thread = null;
        
        // Skip cancelled predecessors
        $pred = $node->prev;
        while ($pred->waitStatus > 0) {
            $node->prev = $pred = $pred->prev;
        }
        
        // predNext is the apparent node to unsplice. CASes below will fail if
        // not, in
        // which case, we lost race vs another cancel or signal, so no further
        // action is
        // necessary.
        $predNext = $pred->next;
        
        // Can use unconditional write instead of CAS here. After this atomic
        // step, other
        // Nodes can skip past us. Before, we are free of interference from
        // other threads.
        $node->waitStatus = Node::CANCELLED;
        
        // If we are the tail, remove ourselves
        if ($node === $this->tail && $this->compareAndSetTail($node, $pred)) {
            $this->compareAndSetNext($pred, $predNext, null);
        } else {
            $ws = null;
            if ($pred !== $this->head && (($ws = $pred->waitStatus) == Node::SIGNAL ||
                 ($ws <= 0 && self::compareAndSetWaitStatus($pred, $ws, Node::SIGNAL))) && $pred->thread != null) {
                $next = $node->next;
                if ($next != null && $next->waitStatus <= 0) {
                    $this->compareAndSetNext($pred, $predNext, $next);
                }
            } else {
                $this->unparkSuccessor($node);
            }
            $node->next = $node;
        }
    }

    /**
     * Acquires in shared interruptible mode.
     *
     * @param int $arg
     * @throws InterruptedException
     */
    private function doAcquireSharedInterruptibly($arg)
    {
        $node = $this->addWaiter(Node::SHARED());
        $failed = true;
        while (true) {
            $p = $node->predecessor();
            if ($p === $this->head) {
                $r = $this->tryAcquireShared($arg);
                if ($r >= 0) {
                    $this->setHeadAndPropagate($node, $r);
                    $p->next = null;
                    $failed = false;
                    return;
                }
            }
            if ($this->shouldParkAfterFailedAcquire($p, $node) && $this->parkAndCheckInterrupt()) {
                throw new InterruptedException();
            }
        }
        if ($failed) {
            $this->cancelAcquire($node);
        }
    }

    /**
     * Acquire in shared timed mode.
     *
     * @param int $arg
     * @param float $microTimeout
     * @return boolean True, if acquired
     */
    private function doAcquireSharedNanos($arg, $microTimeout)
    {
        $lastTime = microtime(true);
        $node = $this->addWaiter(Node::SHARED());
        $failed = true;
        while (true) {
            $p = $node->predecessor();
            if ($p === $this->head) {
                $r = $this->tryAcquireShared($arg);
                if ($r >= 0) {
                    $this->setHeadAndPropagate($node, $r);
                    $p->next = null;
                    $failed = false;
                    return true;
                }
            }
            if ($microTimeout <= 0) {
                if ($failed) {
                    $this->cancelAcquire($node);
                }
                return false;
            }
            if ($this->shouldParkAfterFailedAcquire($p, $node) && $microTimeout > 0) {
                $this->parkMicros($microTimeout);
            }
            $now = microtime(true);
            $microTimeout -= $now - $lastTime;
            $lastTime = $now;
        }
    }

    /**
     * Release action for shared mode -- signal successor and ensure
     * propagation. (Note: For exclusive mode, release just amounts to calling
     * unparkSuccessor of head if it needs signal.)
     */
    private function doReleaseShared()
    {
        /* @var $head Node */
        while (true) {
            $h = $this->head;
            if ($h != null && $h !== $this->tail) {
                $ws = $h->waitStatus;
                if ($ws == Node::SIGNAL) {
                    if (! self::compareAndSetWaitStatus($h, Node::SIGNAL, 0)) {
                        continue;
                    }
                    $this->unparkSuccessor($h);
                } elseif ($ws == 0 && ! self::compareAndSetWaitStatus($h, 0, Node::PROPAGATE)) {
                    continue;
                }
            }
            if ($h === $this->head) {
                break;
            }
        }
    }

    /**
     * Inserts node into queue, initializing if necessary.
     *
     * @param Node $node
     * @return Node The node's predecessor
     */
    private function enqueue(Node $node)
    {
        while (true) {
            $t = $this->tail;
            if ($t == null) {
                if ($this->compareAndSetHead(new Node())) {
                    $this->tail = $this->head;
                }
            } else {
                $node->prev = $t;
                if ($this->compareAndSetTail($t, $node)) {
                    $t->next = $node;
                    return $t;
                }
            }
        }
    }

    /**
     * Convenience method to park and then check if interrupted.
     *
     * @return boolean True if interrupted
     */
    private final function parkAndCheckInterrupt()
    {
        $thread = $this->currentThread();
        return $thread->synchronized(function (\Thread $t)
        {
            $t->wait();
            return $t->isTerminated();
        }, $thread);
    }

    /**
     * Disables the current thread for thread scheduling purposes, for up to the
     * specified waiting time, unless the permit is available.
     *
     * @param int $microTimeout
     */
    private function parkMicros($microTimeout)
    {
        $thread = $this->currentThread();
        $thread->synchronized(
            function (\Thread $t, $microTimeout)
            {
                if ($microTimeout > 0) {
                    $t->wait($microTimeout);
                }
            }, $thread, $microTimeout);
    }

    /**
     * Sets head of queue, and checks if successor may be waiting in shared
     * mode, if so propagating if either propagate > 0 or PROPAGATE status was
     * set.
     *
     * @param Node $node
     * @param int $propagate
     */
    private function setHeadAndPropagate(Node $node, $propagate)
    {
        /*
         * $oldHead = $this->setHead( $node );
         */
        $h = $this->head;
        $this->setHead($node);
        
        /*
         * Try to signal the next queued node if propagation was initiated by
         * call or was recorded by a previous operation
         */
        if ($propagate > 0 || $h == null || $h->waitStatus < 0) {
            $s = $node->next;
            if ($s == null || $s->isShared()) {
                $this->doReleaseShared();
            }
        }
    }

    /**
     * Checks and updates status for a node that failed to acquire. Returns true
     * if thread should block. This is the main signal control in all acquire
     * loops. Requires that $pred == $node.prev.
     *
     * @param Node $pred
     * @param Node $node
     * @return boolean
     */
    private function shouldParkAfterFailedAcquire(Node $pred, Node $node)
    {
        $ws = $pred->waitStatus;
        if ($ws == Node::SIGNAL) {
            /*
             * This node has already set status asking a release to signal it,
             * so it can safely park.
             */
            return true;
        }
        if ($ws > 0) {
            /*
             * Predecessor was cancelled. Skip over predecessors and indicate
             * retry.
             */
            do {
                $node->prev = $pred = $pred->prev;
            } while ($pred->waitStatus > 0);
            $pred->next = $node;
        } else {
            /*
             * waitStatus must be 0 or PROPAGATE. Indicate that we need a
             * signal, but don't park yet. Caller will need to retry to make
             * sure it cannot acquire before parking.
             */
            self::compareAndSetWaitStatus($pred, $ws, Node::SIGNAL);
        }
        return false;
    }

    /**
     * Wakes up a node's successor if one exists.
     *
     * @param Node $node
     */
    private function unparkSuccessor(Node $node)
    {
        /* @var $s Node */
		/* @var $t Node */
		/*
		 * If status is negative (i.e., possibly needing signal) try to clear in
		 * anticipation of signaling. It is OK if this fails or if status is changed by
		 * waiting thread.
		 */
		$ws = $node->waitStatus;
        if ($ws < 0) {
            self::compareAndSetWaitStatus($node, $ws, 0);
        }
        
        /*
         * Thread to unpark is held in successor, which is normally just the
         * next node. But if cancelled or apparently null, traverse backwards
         * from tail to find the actual non-cancelled successor.
         */
        $s = $node->next;
        if ($s == null || $s->waitStatus > 0) {
            $s = null;
            for ($t = $this->tail; $t != null && $t != $node; $t = $t->prev) {
                if ($t->waitStatus <= 0) {
                    $s = $t;
                }
            }
        }
        if ($s != null) {
            $s->synchronized(function (\Thread $thread)
            {
                $thread->notify();
            }, $s->thread);
        }
    }
    
    /* ---------- Utility Methods ---------- */
    
    /**
     * CAS head field.
     *
     * @param Node $head
     */
    private final function compareAndSetHead(Node $head)
    {
        if ($this->head === null) {
            $this->head = $head;
            return true;
        }
        return false;
    }

    /**
     * CAS next field of a node.
     *
     * @param Node $node
     * @param Node $expect
     * @param Node $update
     * @return boolean
     */
    private final function compareAndSetNext(Node $node, Node $expect, Node $update)
    {
        if ($node->next === $expect) {
            $node->next = $update;
            return true;
        }
        return false;
    }

    /**
     * Atomically sets synchronization state to the given updated value if the
     * current state value equals the expected value.
     *
     * @param int $expect
     * @param int $update
     * @return boolean True if successful
     */
    protected final function compareAndSetState($expect, $update)
    {
        if ($this->state == (int) $expect) {
            $this->state = (int) $update;
            return true;
        }
        return false;
    }

    /**
     * CAS tail field.
     *
     * @param Node $expect
     * @param Node $update
     * @return boolean
     */
    private final function compareAndSetTail(Node $expect, Node $update)
    {
        if ($this->tail === $expect) {
            $this->tail = $update;
            return true;
        }
        return false;
    }

    /**
     * CAS waitStatus field.
     *
     * @param Node $node
     * @param int $expect
     * @param int $update
     * @return boolean
     */
    private static final function compareAndSetWaitStatus(Node $node, $expect, $update)
    {
        if ($node->waitStatus == (int) $expect) {
            $node->waitStatus = (int) $update;
            return true;
        }
        return false;
    }

    /**
     * Attempts to find the currently executing thread by peeking in this queue.
     *
     * @throws \RuntimeException
     * @return \Thread
     */
    private function currentThread()
    {
        /* @var $node Node */
        $pid = getmypid();
        $result = null;
        for ($this->queue->rewind(); $this->queue->valid(); $this->queue->next()) {
            $node = $this->queue->current();
            if ($pid == $node->thread->getCreatorId()) {
                $result = $node->thread;
                break;
            }
        }
        if ($result == null) {
            throw new \RuntimeException("Cannot find current thread");
        }
        return $result;
    }
}
?>