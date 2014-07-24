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
namespace KM\Util\Concurrent;

use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\Util\Concurrent\Locks\AbstractQueuedSynchronizer;

/**
 * A synchronization aid that allows one or more threads to wait until a set of
 * operations being performed in other threads completes. A CountDownLatch is
 * initialized with a given count. The await methods block until the current
 * count reaches zero due to invocations of the countDown() method, after which
 * all waiting threads are released and any subsequent invocations of await
 * return immediately. This is a one-shot phenomenon -- the count cannot be
 * reset. If you need a version that resets the count, consider using a
 * CyclicBarrier. A CountDownLatch is a versatile synchronization tool and can
 * be used for a number of purposes. A CountDownLatch initialized with a count
 * of one serves as a simple on/off latch, or gate: all threads invoking await
 * wait at the gate until it is opened by a thread invoking countDown(). A
 * CountDownLatch initialized to N can be used to make one thread wait until N
 * threads have completed some action, or some action has been completed N
 * times. A useful property of a CountDownLatch is that it doesn't require that
 * threads calling countDown wait for the count to reach zero before proceeding,
 * it simply prevents any thread from proceeding past an await until all threads
 * could pass.
 *
 * @author Blair
 */
class CountDownLatch extends Object
{

    /**
     *
     * @var SyncQueue
     */
    private $sync;

    /**
     * Constructs a CountDownLatch initialized with the given count
     *
     * @param int $count
     * @throws IllegalArgumentException
     */
    public function __construct($count)
    {
        $count = (int) $count;
        if ($count < 0) {
            throw new IllegalArgumentException("count < 0");
        }
        $this->sync = new SyncQueue($count);
    }

    /**
     * Returns the current count. This method is typically used for debugging
     * and testing purposes.
     *
     * @return number
     */
    public function getCount()
    {
        return $this->sync->getCount();
    }

    /**
     * Causes the current thread to wait until the latch has counted down to
     * zero, unless the thread is interrupted, or the specified waiting time
     * elapses if given. If the current count is zero then this method returns
     * immediately. If the current count is greater than zero then the current
     * thread becomes disabled for thread scheduling purposes and lies dormant
     * until one of two things happen: - The count reaches zero due to
     * invocations of the countDown() method; or - Some other thread interrupts
     * the current thread. If the current thread: - has its interrupted status
     * set on entry to this method; or - is interrupted while waiting, then
     * InterruptedException is thrown and the current thread's interrupted
     * status is cleared.
     *
     * @param string $microTimeout
     * @return boolean
     */
    public function await($timeout = null, TimeUnit $unit = null)
    {
        if ($microTimeout == null) {
            $this->sync->acquireSharedInterruptibly(1);
        } else {
            return $this->sync->tryAcquireSharedNanos(1, $unit->toMicros($timeout));
        }
    }

    /**
     * Decrements the count of the latch, releasing all waiting threads if the
     * count reaches zero. If the current count is greater than zero then it is
     * decremented. If the new count is zero then all waiting threads are
     * re-enabled for thread scheduling purposes. If the current count equals
     * zero then nothing happens./ public function countDown() {
     * $this->sync->releaseShared(1); } /** Returns a string identifying this
     * latch, as well as its state. The state, in brackets, includes the String
     * {@code "Count ="} followed by the current count.
     *
     * @return string
     * @see \Application\Stdlib\Object::__toString()
     */
    public function __toString()
    {
        return parent::__toString() . "[Count=" . $this->sync->getCount() . "]";
    }
}

/**
 * Synchronization control For CountDownLatch. Uses AQS state to represent
 * count.
 *
 * @package KM\Util\Concurrent
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class SyncQueue extends AbstractQueuedSynchronizer
{

    public function __construct($count)
    {
        parent::__construct();
        $this->setState((int) $count);
    }

    public function getCount()
    {
        return $this->getState();
    }

    protected function tryAcquireShared($acquires)
    {
        return ($this->getState() == 0 ? 1 : - 1);
    }

    protected function tryReleaseShared($releases)
    {
        while (true) {
            $c = $this->getState();
            if ($c == 0) {
                return false;
            }
            $nextc = $c - 1;
            if ($this->compareAndSetState($c, $nextc)) {
                return ($nextc == 0);
            }
        }
    }
}
?>