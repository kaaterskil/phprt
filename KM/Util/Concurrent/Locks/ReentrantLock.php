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

use KM\Lang\IllegalMonitorStateException;
use KM\Lang\IllegalStateException;
use KM\Lang\InterruptedException;
use KM\Lang\Object;
use KM\Lang\System;
use KM\Util\Concurrent\TimeUnit;

/**
 * A reentrant mutual exclusion <code>Lock</code> with the same basic behavior
 * and semantics as the implicit monitor lock. <code>ReentrantLock</code> is
 * <em>owned</em> by the thread last successfully locking, but not yet unlocking
 * it. A thread invoking <code>lock</code> will return, successfully acquiring
 * the lock, when the lock is not owned by another thread. The method will
 * return immediately if the current thread already owns the lock. This can be
 * checked using methods #isHeldByCurrentThread, and #getHoldCount.
 *
 * @author Blair
 */
class ReentrantLock extends Object implements Lock
{

    /**
     * The file resource.
     *
     * @var resource
     */
    private $sync;

    /**
     * The file name.
     *
     * @var string
     */
    private $fname;

    /**
     * The count of lock holders.
     *
     * @var int
     */
    private $holdCount = 0;

    /**
     * The PID of the creator
     *
     * @var int
     */
    private $exclusiveOwnerThread = 0;

    /**
     * Creates an instance of ReentrantLock with the given <code>key</code>. If
     * <code>key</code> is not specified, the current process ID will be used.
     *
     * @param string $key A unique identifier, or the process ID if null.
     */
    public function __construct($key = null)
    {
        if ($key === null) {
            $key = getmypid();
        }
        $dir = System::getProperty('user.dir') . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        $this->fname = $dir . $key . '.lck';
        
        // Create a new resource or get the existing resource with the same
        // filename.
        $this->sync = fopen($this->fname, 'w+');
    }

    /**
     * Releases all resources prior to garbage collection.
     */
    public function __destruct()
    {
        if ($this->sync != null) {
            fclose($this->sync);
            unlink($this->fname);
            $this->sync = null;
        }
    }

    /**
     * Acquires the lock
     * Acquires the lock if it is not held by another thread and returns
     * immediately, setting the lock hold count to one. If the current thread
     * already holds the lock then the hold count is incremented by one and the
     * method returns immediately. If the lock is held by another thread then
     * the current thread becomes disabled for thread scheduling purposes and
     * lies dormant until the lock has been acquired, at which time the lock
     * hold count is set to one.
     * @throws IllegalStateException
     * @throws LockFailureException
     * @see \KM\Util\Concurrent\Locks\Lock::lock()
     */
    public function lock()
    {
        if ($this->sync == null) {
            throw new IllegalStateException('Resource to lock lost');
        }
        
        $this->acquire(1);
        
        if (! flock($this->sync, LOCK_EX)) {
            throw new LockFailureException('Lock failed');
        }
        // Write something to the file for debugging purposes.
        ftruncate($this->sync, 0);
        fwrite($this->sync, "Locked\n");
        fflush($this->sync);
    }

    /**
     * Acquires in exclusive mode. Implemented by invoking at least one
     * tryAcquire() method, returning on success.
     *
     * @param int $arg The acquire argument, This value is conveyed to
     *            tryAcquire().
     * @throws InterruptedException
     */
    protected function acquire($arg)
    {
        if (! $this->tryAcquire($arg)) {
            throw new InterruptedException();
        }
    }

    /**
     * Fair version of tryAcquire. Don't grant access unless recursive call or
     * no waiters or is first.
     *
     * @param int $acquires
     * @return boolean
     */
    protected final function tryAcquire($acquires)
    {
        $acquires = (int) $acquires;
        $current = getmypid();
        $c = $this->holdCount;
        if ($c == 0) {
            $this->exclusiveOwnerThread = $current;
            $nextc = $c + $acquires;
            $this->holdCount = $nextc;
            return true;
        } elseif ($current == $this->exclusiveOwnerThread) {
            $nextc = $c + $acquires;
            $this->holdCount = $nextc;
            return true;
        }
        return false;
    }

    /**
     * Attempts to acquire in exclusive mode, aborting if interrupted, and
     * failing if the given timeout elapses. Implemented by first checking
     * interrupt status, then invoking at least once tryAcquire, returning on
     * success. Otherwise, the thread is queued, possibly repeatedly blocking
     * and unblocking, invoking tryAcquire until success or the thread is
     * interrupted or the timeout elapses. This method can be used to implement
     * method Lock#tryLock(long, TimeUnit).
     *
     * @param int $arg The acquire argument. This value is conveyed to
     *            tryAcquire() but is otherwise uninterpreted.
     * @param float $microTimeout The maximum number of microseconds to wait.
     * @return boolean <code>True</code> if acquired, <code>false</code> if
     *         timed out.
     */
    protected function tryAcquireNanos($arg, $microTimeout)
    {
        return $this->tryAcquire($arg) || $this->doAcquireMicros($arg, $microTimeout);
    }

    /**
     * Acquires in exclusive timed mode.
     *
     * @param int $arg The acquire argument.
     * @param float $microTimeout Maximum wait time in microseconds.
     * @return boolean <code>True</code> if acquired, <code>false</code>
     *         otherwise.
     */
    private function doAcquireMicros($arg, $microTimeout)
    {
        $microTimeout = floatval($microTimeout);
        if ($nanosTimeout < 0) {
            return false;
        }
        $deadline = microtime(true) + $microTimeout;
        $failed = true;
        for (;;) {
            if ($this->tryAcquire($arg)) {
                $failed = false;
                return true;
            }
            $microTimeout = $deadline - microtime(true);
            if ($microTimeout < 0) {
                return false;
            }
            usleep($microTimeout);
        }
    }

    /**
     * Acquires the lock if it is not held by another thread within the given
     * waiting time.
     *
     * @param int $timeout The time to wait for the lock.
     * @param TimeUnit $timeUnit The time unit of the timeout argument.
     * @return boolean <code>True</code> if the lock was free and the lock was
     *         acquired by the current thread, or the lock was already held by
     *         the current thread; <code>false</code> if the waiting time
     *         elapsed before the lock could be acquired.
     * @see \KM\Util\Concurrent\Locks\Lock::tryLock()
     */
    public function tryLock($timeout = null, TimeUnit $timeUnit = null)
    {
        if ($timeout === null) {
            return $this->tryAcquire(1);
        } else {
            return $this->tryAcquireNanos(1, $timeUnit->toMicros($timeout));
        }
    }

    /**
     * Attempts to release this lock. If the current thread is the holder of
     * this lock then the hold count is decremented. If the hold count is now
     * zero then the lock is released. If the current thread is not the holder
     * of this lock then IllegalMonitorStateException is thrown.
     *
     * @throws IllegalStateException if the file resource was lost.
     * @throws IllegalMonitorStateException if the current thread does not this
     *         lock.
     * @throws LockFailureException if the unlock operation failed.
     * @see \KM\Util\Concurrent\Locks\Lock::unlock()
     */
    public function unlock()
    {
        if ($this->sync == null) {
            throw new IllegalStateException('Resource to lock lost');
        }
        
        if ($this->release(1)) {
            if (! flock($this->sync, LOCK_UN)) {
                throw new LockFailureException('Unlock failed');
            }
            // Write something to the file for debugging purposes.
            ftruncate($this->sync, 0);
            fwrite($this->sync, "Unlocked\n");
            fflush($this->sync);
        }
    }

    /**
     * Releases in exclusive mode. Implemented by unlocking one or more threads
     * if tryRelease() is <code>true</code>.
     *
     * @param int $arg The release argument. This value is conveyed to
     *            tryRelease() but is otherwise uninterpreted.
     * @return boolean The value returned from tryRelease().
     */
    protected function release($arg)
    {
        if ($this->tryRelease($arg)) {
            return true;
        }
        return false;
    }

    /**
     * Attempts to set the state to reflect a release in exclusive mode.
     *
     * @param int $releases The release argument.
     * @throws IllegalMonitorStateException if releasing would place this in an
     *         illegal state.
     * @return boolean <code>True</code> if this object is now in a fully
     *         released state so that any waiting threads may attempt to
     *         acquire; <code>false</code> otherwise.
     */
    protected function tryRelease($releases)
    {
        $releases = (int) $releases;
        $c = $this->holdCount - $releases;
        if (getmypid() != $this->exclusiveOwnerThread) {
            throw new IllegalMonitorStateException();
        }
        
        $free = false;
        if ($c == 0) {
            $free = true;
            $this->exclusiveOwnerThread = 0;
        }
        $this->holdCount = $c;
        return $free;
    }

    /**
     * Queries the number of holds on this lock by the current thread. A thread
     * has a hold on a lock for each lock action that is not matched by an
     * unlock action.
     *
     * @return int The number of holds on this lock by the current thread, or
     *         zero if this lock is not held by the current thread.
     */
    public function getHoldCount()
    {
        return $this->holdCount;
    }

    public function isHeldBy($owner)
    {
        return $owner == $this->exclusiveOwnerThread;
    }

    /**
     * Queries if this lock is held by the current thread.
     *
     * @return boolean <code>True</code> if the current thread holds this lock,
     *         <code>false</code> otherwise.
     */
    public function isHeldByCurrentThread()
    {
        return getmypid() == $this->exclusiveOwnerThread;
    }

    /**
     * Queries if this lock is held by any thread. This method is designed for
     * use in monitoring of the system state.
     *
     * @return boolean <code>True</code> if any thread holds this lock,
     *         <code>false</code> otherwise.
     */
    public function isLocked()
    {
        return $this->getHoldCount() != 0;
    }

    /**
     * Returns a string identifying this lock as well as its lock state. The
     * state, in brackets, includes either the string <code>Unlocked</code> or
     * the string <code>Locked by</code> followed by the process ID of the
     * owning thread.
     *
     * @return string A string identifying this lock as well as its lock state.
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        parent::__toString() . (getmypid() === false ? '[Unlocked]' : '[Locked by thread ' . getmypid() . ']');
    }
}
?>