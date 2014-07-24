<?php

/**
 * Kaaterskil Library
 *
 * PHP version 5.5
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KAATERSKIL MANAGEMENT, LLC BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Kaaterskil
 * @copyright   Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version     SVN $Id$
 */
namespace KMX\Persistence;

use KM\Lang\Enum;

/**
 * Lock modes can be specified by means of passing a <code>LockModeType</code>
 * argument to one of the <code>javax.persistence.EntityManager</code> methods that take locks
 * (<code>lock</code>, <code>find</code>, or <code>refresh</code>) or
 * to the <code>Query#setLockMode Query.setLockMode()</code> or
 * <code>TypedQuery#setLockMode TypedQuery.setLockMode()</code> method.
 *
 * <p> Lock modes can be used to specify either optimistic or pessimistic locks.
 *
 * <p> Optimistic locks are specified using {@link
 * LockModeType#OPTIMISTIC LockModeType.OPTIMISTIC</code> and {@link
 * LockModeType#OPTIMISTIC_FORCE_INCREMENT
 * LockModeType.OPTIMISTIC_FORCE_INCREMENT</code>.  The lock mode type
 * values <code>LockModeType#READ LockModeType.READ</code> and
 * <code>LockModeType#WRITE LockModeType.WRITE</code> are
 * synonyms of <code>OPTIMISTIC</code> and
 * <code>OPTIMISTIC_FORCE_INCREMENT</code> respectively.  The latter
 * are to be preferred for new applications.
 *
 * <p> The semantics of requesting locks of type
 * <code>LockModeType.OPTIMISTIC</code> and
 * <code>LockModeType.OPTIMISTIC_FORCE_INCREMENT</code> are the
 * following.
 *
 * <p> If transaction T1 calls for a lock of type
 * <code>LockModeType.OPTIMISTIC</code> on a versioned object,
 * the entity manager must ensure that neither of the following
 * phenomena can occur:
 * <ul>
 *   <li> P1 (Dirty read): Transaction T1 modifies a row.
 * Another transaction T2 then reads that row and obtains
 * the modified value, before T1 has committed or rolled back.
 * Transaction T2 eventually commits successfully; it does not
 * matter whether T1 commits or rolls back and whether it does
 * so before or after T2 commits.
 *   <li>
 *   </li> P2 (Non-repeatable read): Transaction T1 reads a row.
 * Another transaction T2 then modifies or deletes that row,
 * before T1 has committed. Both transactions eventually commit
 * successfully.
 *   </li>
 * </ul>
 *
 * <p> Lock modes must always prevent the phenomena P1 and P2.
 *
 * <p> In addition, calling a lock of type
 * <code>LockModeType.OPTIMISTIC_FORCE_INCREMENT</code> on a versioned object,
 * will also force an update (increment) to the entity's version
 * column.
 *
 * <p> The persistence implementation is not required to support
 * the use of optimistic lock modes on non-versioned objects. When it
 * cannot support a such lock call, it must throw the <code>PersistenceException</code>.
 *
 * <p>The lock modes <code>LockModeType#PESSIMISTIC_READ
 * LockModeType.PESSIMISTIC_READ</code>, <code>LockModeType#PESSIMISTIC_WRITE
 * LockModeType.PESSIMISTIC_WRITE</code>, and
 * <code>LockModeType#PESSIMISTIC_FORCE_INCREMENT
 * LockModeType.PESSIMISTIC_FORCE_INCREMENT</code> are used to immediately
 * obtain long-term database locks.
 *
 * <p> The semantics of requesting locks of type
 * <code>LockModeType.PESSIMISTIC_READ</code>, <code>LockModeType.PESSIMISTIC_WRITE</code>, and
 * <code>LockModeType.PESSIMISTIC_FORCE_INCREMENT</code> are the following.
 *
 * <p> If transaction T1 calls for a lock of type
 * <code>LockModeType.PESSIMISTIC_READ</code> or
 * <code>LockModeType.PESSIMISTIC_WRITE</code> on an object, the entity
 * manager must ensure that neither of the following phenomena can
 * occur:
 * <ul>
 * <li> P1 (Dirty read): Transaction T1 modifies a
 * row. Another transaction T2 then reads that row and obtains the
 * modified value, before T1 has committed or rolled back.
 *
 * <li> P2 (Non-repeatable read): Transaction T1 reads a row. Another
 * transaction T2 then modifies or deletes that row, before T1 has
 * committed or rolled back.
 * </ul>
 *
 * <p> A lock with <code>LockModeType.PESSIMISTIC_WRITE</code> can be obtained on
 * an entity instance to force serialization among transactions
 * attempting to update the entity data. A lock with
 * <code>LockModeType.PESSIMISTIC_READ</code> can be used to query data using
 * repeatable-read semantics without the need to reread the data at
 * the end of the transaction to obtain a lock, and without blocking
 * other transactions reading the data. A lock with
 * <code>LockModeType.PESSIMISTIC_WRITE</code> can be used when querying data and
 * there is a high likelihood of deadlock or update failure among
 * concurrent updating transactions.
 *
 * <p> The persistence implementation must support use of locks of type
 * <code>LockModeType.PESSIMISTIC_READ</code>
 * <code>LockModeType.PESSIMISTIC_WRITE</code> on a non-versioned entity as well as
 * on a versioned entity.
 *
 * <p> When the lock cannot be obtained, and the database locking
 * failure results in transaction-level rollback, the provider must
 * throw the <code>PessimisticLockException</code> and ensure that the JTA
 * transaction or <code>EntityTransaction</code> has been marked for rollback.
 *
 * <p> When the lock cannot be obtained, and the database locking
 * failure results in only statement-level rollback, the provider must
 * throw the <code>LockTimeoutException</code> (and must not mark the transaction
 * for rollback).
 *
 * @author Blair
 */
class LockModeType extends Enum
{

    /**
     * Synonymous with <code>OPTIMISTIC</code>. <code>OPTIMISTIC</code> is to be
     * preferred for new applications.
     */
    const READ = 'read';

    /**
     * Synonymous with <code>OPTIMISTIC_FORCE_INCREMENT</code>.
     * <code>OPTIMISTIC_FORCE_IMCREMENT</code> is to be preferred for new
     * applications.
     */
    const WRITE = 'write';

    /**
     * Optimistic lock.
     */
    const OPTIMISTIC = 'optimistic';

    /**
     * Optimistic lock, with version update.
     */
    const OPTIMISTIC_FORCE_INCREMENT = 'optimisticForceIncrment';

    /**
     * Pessimistic read lock.
     */
    const PESSIMISTIC_READ = 'pessimisticRead';

    /**
     * Pessimistic write lock.
     */
    const PESSIMISTIC_WRITE = 'pessimisticWrite';

    /**
     * Pessimistic write lock, with version update.
     */
    const PESSIMISTIC_FORCE_INCREMENT = 'pessimisticForceIncrement';

    /**
     * No lock.
     */
    const NONE = 'none';
}
?>