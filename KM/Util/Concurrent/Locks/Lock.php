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

use KM\Util\Concurrent\TimeUnit;

/**
 * A lock is a tool for controlling access to a shared resource by multiple
 * threads. Commonly, a lock provides exclusive access to a shared resource:
 * only one thread at a time can acquire the lock and all access to the shared
 * resource requires that the lock be acquired first.
 *
 * @author Blair
 */
interface Lock
{

    /**
     * Acquires the lock. If the lock is not available then the current thread
     * becomes disabled for thread scheduling purposes and lies dormant until
     * the lock has been acquired. A <code>Lock</code> implementation may be
     * able to detect erroneous use of the lock, such as an invocation that
     * would cause deadlock, and may throw an (unchecked) exception in such
     * circumstances. The circumstances and the exception type must be
     * documented by that <code>Lock</code> implementation.
     */
    public function lock();

    /**
     * Acquires the lock only if it is free within the given waiting time at the
     * time of invocation. Acquires the lock if it is available and returns
     * immediately with the value <code>true</code>. If the lock is not
     * available then this method will return immediately with the value
     * <code>false</code>. This usage ensures that the lock is unlocked if it
     * was acquired, and doesn't try to unlock if the lock was not acquired.
     *
     * @param int $timeout The maximum time to wait for the lock.
     * @param TimeUnit $timeUnit The time unit of the time argument.
     * @return boolean <code>True</code> if the lock was acquired,
     *         <code>false</code> if the waiting time elapsed before the lock
     *         was acquired.
     */
    public function tryLock($timeout = null, TimeUnit $timeUnit = null);

    /**
     * Releases the lock. A <code>Lock</code> implementation will usually impose
     * restrictions on which thread can release a lock (typically only the
     * holder of the lock can release it) and may throw an (unchecked) exception
     * if the restriction is violated. Any restrictions and the exception type
     * must be documented by that <code>Lock</code> implementation.
     */
    public function unlock();
}
?>