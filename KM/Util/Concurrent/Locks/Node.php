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
use KM\Lang\NullPointerException;

/**
 * Wait queue node class.
 * The wait queue is a variant of a "CLH" (Craig, Landin, and Hagersten) lock
 * queue. CLH locks are normally used for spinlocks. We instead use them for
 * blocking synchronizers, but use the same basic tactic of holding some of the
 * control information about a thread in the predecessor of its node. A "status"
 * field in each node keeps track of whether a thread should block. A node is
 * signaled when its predecessor releases. Each node of the queue otherwise
 * serves as a specific-notification-style monitor holding a single waiting
 * thread. The status field does NOT control whether threads are granted locks
 * etc though. A thread may try to acquire if it is first in the queue. But
 * being first does not guarantee success; it only gives the right to contend.
 * So the currently released contender thread may need to re-wait.
 * To enqueue into a CLH lock, you atomically splice it in as new tail. To
 * dequeue, you just set the head field.
 *
 *      +------+  prev +-----+       +-----+
 * head |      | <---- |     | <---- |     | tail
 *      +------+       +-----+       +-----+
 *
 * Insertion into a CLH queue requires only a single atomic operation on "tail",
 * so there is a simple atomic point of demarcation from unqueued to queued.
 * Similarly, dequeing involves only updating the "head". However, it takes a
 * bit more work for nodes to determine who their successors are, in part to
 * deal with possible cancellation due to timeouts and interrupts.
 * The "prev" links (not used in original CLH locks), are mainly needed to
 * handle cancellation. If a node is cancelled, its successor is (normally)
 * re-linked to a non-cancelled predecessor. For explanation of similar
 * mechanics in the case of spin locks, see the papers by Scott and Scherer at
 * http://www.cs.rochester.edu/u/scott/synchronization/
 * We also use "next" links to implement blocking mechanics. The thread id for
 * each node is kept in its own node, so a predecessor signals the next node to
 * wake up by traversing next link to determine which thread it is.
 * Determination of successor must avoid races with newly queued nodes to set
 * the "next" fields of their predecessors. This is solved when necessary by
 * checking backwards from the atomically updated "tail" when a node's successor
 * appears to be null. (Or, said differently, the next-links are an optimization
 * so that we don't usually need a backward scan.)
 * Cancellation introduces some conservatism to the basic algorithms. Since we
 * must poll for cancellation of other nodes, we can miss noticing whether a
 * cancelled node is ahead or behind us. This is dealt with by always unparking
 * successors upon cancellation, allowing them to stabilize on a new
 * predecessor.
 * CLH queues need a dummy header node to get started. But we don't create them
 * on construction, because it would be wasted effort if there is never
 * contention. Instead, the node is constructed and head and tail pointers are
 * set upon first contention.
 * Threads waiting on Conditions use the same nodes, but use an additional link.
 * Conditions only need to link nodes in simple (non-concurrent) linked queues
 * because they are only accessed when exclusively held. Upon await, a node is
 * inserted into a condition queue. Upon signal, the node is transferred to the
 * main queue. A special value of status field is used to mark which queue a
 * node is on
 *
 * @package KM\Util\Concurrent\Locks
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Node extends Object
{

    const CANCELLED = 1;

    const SIGNAL = - 1;

    const CONDITION = - 2;

    const PROPAGATE = - 3;

    /**
     * A singleton.
     *
     * @var Node
     */
    private static $shared;

    /**
     * Link to predecessor node that current node/thread relies on for checking
     * waitStatus. Assigned during enqueing, and nulled out (for sake of GC)
     * only upon dequeuing. Also, upon cancellation of a predecessor, we
     * short-circuit while finding a non-cancelled one, which will always exist
     * because the head node is never cancelled: A node becomes head only as a
     * result of successful acquire. A cancelled thread never succeeds in
     * acquiring, and a thread only cancels itself, not any other node.
     *
     * @var Node
     */
    public $prev;

    /**
     * Link to the successor node that the current node/thread un-parks upon
     * release. Assigned during enqueuing, adjusted when bypassing cancelled
     * predecessors, and nulled out (for sake of GC) when dequeued. The enqueue
     * operation does not assign next field of a predecessor until after
     * attachment, so seeing a null next field does not necessarily mean that
     * node is at end of queue. However, if a next field appears to be null, we
     * can scan prev's from the tail to double-check. The next field of
     * cancelled nodes is set to point to the node itself instead of null, to
     * make life easier for isOnSyncQueue.
     *
     * @var Node
     */
    public $next;

    /**
     * Link to next node waiting on condition, or the special value SHARED.
     * Because condition queues are accessed only when holding in exclusive
     * mode, we just need a simple linked queue to hold nodes while they are
     * waiting on conditions. They are then transferred to the queue to
     * re-acquire. And because conditions can only be exclusive, we save a field
     * by using special value to indicate shared mode.
     *
     * @var Node
     */
    public $nextWaiter;

    /**
     * The thread that enqueued this node. Initialized on construction and
     * nulled out after use.
     *
     * @var \Thread
     */
    public $thread;

    /**
     * Status field, taking on only the values:
     * SIGNAL: The successor of this node is (or will soon be) blocked (via
     * park), so the current node must unpark its successor when it releases or
     * cancels. To avoid races, acquire methods must first indicate they need a
     * signal, then retry the atomic acquire, and then, on failure, block.
     * CANCELLED: This node is cancelled due to timeout or interrupt. Nodes
     * never leave this state. In particular, a thread with cancelled node never
     * again blocks.
     * CONDITION: This node is currently on a condition queue. It will not be
     * used as a sync queue node until transferred, at which time the status
     * will be set to 0. (Use of this value here has nothing to do with the
     * other uses of the field, but simplifies mechanics.)
     * PROPAGATE: A releaseShared should be propagated to other nodes. This is
     * set (for head node only) in doReleaseShared to ensure propagation
     * continues, even if other operations have since intervened.
     * 0: None of the above
     * The values are arranged numerically to simplify use. Non-negative values
     * mean that a node doesn't need to signal. So, most code doesn't need to
     * check for particular values, just for sign.
     * The field is initialized to 0 for normal sync nodes, and CONDITION for
     * condition nodes. It is modified using CAS (or when possible,
     * unconditional volatile writes).
     * @var int
     */
    public $waitStatus = 0;

    /**
     * Constructs a new node
     *
     * @param \Thread $thread
     * @param Node $mode
     * @param int $waitStatus
     */
    public function __construct(\Thread $thread = null, Node $mode = null, $waitStatus = 9999)
    {
        $this->thread = $thread;
        if ($mode != null) {
            $this->nextWaiter = $mode;
        }
        if ($waitStatus != 9999) {
            $this->waitStatus = (int) $waitStatus;
        }
    }

    /**
     * Marker to indicate a node is waiting in shared mode.
     *
     * @return Node
     */
    public static function SHARED()
    {
        if (self::$shared == null) {
            self::$shared = new self();
        }
        return self::$shared;
    }

    /**
     * Marker to indicate a node is waiting in exclusive mode.
     *
     * @return null
     */
    public static function EXCLUSIVE()
    {
        return null;
    }

    /**
     * Returns true if node is waiting in shared mode.
     *
     * @return boolean
     */
    public function isShared()
    {
        return $this->waitStatus == self::SHARED();
    }

    /**
     * Returns previous node, or throws NullPointerException if null. Use when
     * predecessor cannot be null. The null check could be elided, but is
     * present to help the VM.
     *
     * @throws NullPointerException
     * @return Node
     */
    public function predecessor()
    {
        $p = $this->prev;
        if ($p == null) {
            throw new NullPointerException();
        }
        return $p;
    }
}
?>