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
namespace KMX\Persistence;

use KM\Lang\Clazz;
use KM\Lang\Object;
use KM\Util\Map;
use KMX\Persistence\FlushModeType;
use KMX\Persistence\LockModeType;

interface EntityManager
{

    /**
     * Make an instance managed and persistent.
     *
     * @param entity entity instance
     * @throws EntityExistsException if the entity already exists. (If the
     *         entity already exists, the <code>EntityExistsException</code> may
     *         be thrown when the persist operation is invoked, or the
     *         <code>EntityExistsException</code> or another
     *         <code>PersistenceException</code> may be thrown at flush or
     *         commit time.)
     * @throws IllegalArgumentException if the instance is not an entity
     * @throws TransactionRequiredException if there is no transaction when
     *         invoked on a container-managed entity manager of that is of type
     *         <code>PersistenceContextType.TRANSACTION</code>
     */
    public function persist(Object $entity);

    /**
     * Merge the state of the given entity into the current persistence context.
     *
     * @param entity entity instance
     * @return the managed instance that the state was merged to
     * @throws IllegalArgumentException if instance is not an entity or is a
     *         removed entity
     * @throws TransactionRequiredException if there is no transaction when
     *         invoked on a container-managed entity manager of that is of type
     *         <code>PersistenceContextType.TRANSACTION</code>
     */
    public function merge(Object $entity);

    /**
     * Remove the entity instance.
     *
     * @param entity entity instance
     * @throws IllegalArgumentException if the instance is not an entity or is a
     *         detached entity
     * @throws TransactionRequiredException if invoked on a container-managed
     *         entity manager of type
     *         <code>PersistenceContextType.TRANSACTION</code> and there is no
     *         transaction
     */
    public function remove(Object $entity);

    /**
     * Find by primary key, using the specified properties. Search for an entity
     * of the specified class and primary key.If the entity instance is
     * contained in the persistence context, it is returned from there. If a
     * vendor-specific property or hint is not recognized,it is silently
     * ignored.
     *
     * @param entityClazz $entity class
     * @param primaryKey primary key
     * @param properties standard and vendor-specific properties and hints
     * @return the found entity instance or null if the entity does not exist
     * @throws IllegalArgumentException if the first argument does not denote an
     *         entity type or the second argument is is not a valid type for
     *         that entity's primary key or is null
     */
    public function findByProperties(Clazz $entityClass, Object $primaryKey, Map $properties = null);

    /**
     * Find by primary key and lock. Search for an entity of the specified class
     * and primary key and lock it with respect to the specified lock type. If
     * the entity instance is contained in the persistence context, it is
     * returned from there, and the effect of this method is the same as if the
     * lock method had been called on the entity. <p> If the entity is found
     * within the persistence context and the lock mode type is pessimistic and
     * the entity has a version attribute, the persistence provider must perform
     * optimistic version checks when obtaining the database lock. If these
     * checks fail, the <code>OptimisticLockException</code> will be thrown.
     * <p>If the lock mode type is pessimistic and the entity instance is found
     * but cannot be locked: <ul> <li> the <code>PessimisticLockException</code>
     * will be thrown if the database locking failure causes transaction-level
     * rollback <li> the <code>LockTimeoutException</code> will be thrown if the
     * database locking failure causes only statement-level rollback </ul>
     *
     * @param $entityClassz $entity class
     * @param $primaryKey primary key
     * @param $lockMode lock mode
     * @param $properties standard and vendor-specific properties and hints
     * @return the found entity instance or null if the entity does not exist
     * @throws IllegalArgumentException if the first argument does not denote an
     *         entity type or the second argument is not a valid type for that
     *         entity's primary key or is null
     * @throws TransactionRequiredException if there is no transaction and a
     *         lock mode other than <code>NONE</code> is specified or if invoked
     *         on an entity manager which has not been joined to the current
     *         transaction and a lock mode other than <code>NONE</code> is
     *         specified
     * @throws OptimisticLockException if the optimistic version check fails
     * @throws PessimisticLockException if pessimistic locking fails and the
     *         transaction is rolled back
     * @throws LockTimeoutException if pessimistic locking fails and only the
     *         statement is rolled back
     * @throws PersistenceException if an unsupported lock call is made
     */
    public function find(Clazz $entityClass, $primaryKey, LockModeType $lockMode = null, Map $properties = null);

    /**
     * Get an instance, whose state may be lazily fetched. If the requested
     * instance does not exist in the database, the
     * <code>EntityNotFoundException</code> is thrown when the instance state is
     * first accessed. (The persistence provider runtime is permitted to throw
     * the <code>EntityNotFoundException</code> when <code>getReference</code>
     * is called.) The application should not expect that the instance state
     * will be available upon detachment, unless it was accessed by the
     * application while the entity manager was open.
     *
     * @param entityClazz $entity class
     * @param primaryKey primary key
     * @return the found entity instance
     * @throws IllegalArgumentException if the first argument does not denote an
     *         entity type or the second argument is not a valid type for that
     *         entity's primary key or is null
     * @throws EntityNotFoundException if the entity state cannot be accessed
     */
    public function getReference(Clazz $entityClass, $primaryKey);

    /**
     * Synchronize the persistence context to the underlying database.
     *
     * @throws TransactionRequiredException if there is no transaction or if the
     *         entity manager has not been joined to the current transaction
     * @throws PersistenceException if the flush fails
     */
    public function flush();

    /**
     * Set the flush mode that applies to all objects contained in the
     * persistence context.
     *
     * @param flushMode flush mode
     */
    public function setFlushMode(FlushModeType $flushMode);

    /**
     * Get the flush mode that applies to all objects contained in the
     * persistence context.
     *
     * @return flushMode
     */
    public function getFlushMode();

    /**
     * Lock an entity instance that is contained in the persistence context with
     * the specified lock mode type and with specified properties. <p>If a
     * pessimistic lock mode type is specified and the entity contains a version
     * attribute, the persistence provider must also perform optimistic version
     * checks when obtaining the database lock. If these checks fail, the
     * <code>OptimisticLockException</code> will be thrown. <p>If the lock mode
     * type is pessimistic and the entity instance is found but cannot be
     * locked: <ul> <li> the <code>PessimisticLockException</code> will be
     * thrown if the database locking failure causes transaction-level rollback
     * <li> the <code>LockTimeoutException</code> will be thrown if the database
     * locking failure causes only statement-level rollback </ul> <p>If a
     * vendor-specific property or hint is not recognized, it is silently
     * ignored. <p>Portable applications should not rely on the standard timeout
     * hint. Depending on the database in use and the locking mechanisms used by
     * the provider, the hint may or may not be observed.
     *
     * @param $entity entity instance
     * @param $lockMode lock mode
     * @param $properties standard and vendor-specific properties and hints
     * @throws IllegalArgumentException if the instance is not an entity or is a
     *         detached entity
     * @throws TransactionRequiredException if there is no transaction or if
     *         invoked on an entity manager which has not been joined to the
     *         current transaction
     * @throws EntityNotFoundException if the entity does not exist in the
     *         database when pessimistic locking is performed
     * @throws OptimisticLockException if the optimistic version check fails
     * @throws PessimisticLockException if pessimistic locking fails and the
     *         transaction is rolled back
     * @throws LockTimeoutException if pessimistic locking fails and only the
     *         statement is rolled back
     * @throws PersistenceException if an unsupported lock call is made
     * @since Java Persistence 2.0
     */
    public function lock(Object $entity, LockModeType $lockMode, Map $properties = null);

    /**
     * Refresh the state of the instance from the database, overwriting changes
     * made to the entity, if any, and lock it with respect to given lock mode
     * type and with specified properties. <p>If the lock mode type is
     * pessimistic and the entity instance is found but cannot be locked: <ul>
     * <li> the <code>PessimisticLockException</code> will be thrown if the
     * database locking failure causes transaction-level rollback <li> the
     * <code>LockTimeoutException</code> will be thrown if the database locking
     * failure causes only statement-level rollback </ul> <p>If a
     * vendor-specific property or hint is not recognized, it is silently
     * ignored. <p>Portable applications should not rely on the standard timeout
     * hint. Depending on the database in use and the locking mechanisms used by
     * the provider, the hint may or may not be observed.
     *
     * @param $entity entity instance
     * @param $lockMode lock mode
     * @param $properties standard and vendor-specific properties and hints
     * @throws IllegalArgumentException if the instance is not an entity or the
     *         entity is not managed
     * @throws TransactionRequiredException if invoked on a container-managed
     *         entity manager of type
     *         <code>PersistenceContextType.TRANSACTION</code> when there is no
     *         transaction; if invoked on an extended entity manager when there
     *         is no transaction and a lock mode other than <code>NONE</code>
     *         has been specified; or if invoked on an extended entity manager
     *         that has not been joined to the current transaction and a lock
     *         mode other than <code>NONE</code> has been specified
     * @throws EntityNotFoundException if the entity no longer exists in the
     *         database
     * @throws PessimisticLockException if pessimistic locking fails and the
     *         transaction is rolled back
     * @throws LockTimeoutException if pessimistic locking fails and only the
     *         statement is rolled back
     * @throws PersistenceException if an unsupported lock call is made
     */
    public function refresh(Object $entity, LockModeType $lockMode = null, Map $properties = null);

    /**
     * Clear the persistence context, causing all managed entities to become
     * detached. Changes made to entities that have not been flushed to the
     * database will not be persisted.
     */
    public function clear();

    /**
     * Remove the given entity from the persistence context, causing a managed
     * entity to become detached. Unflushed changes made to the entity if any
     * (including removal of the entity), will not be synchronized to the
     * database. Entities which previously referenced the detached entity will
     * continue to reference it.
     *
     * @param entity entity instance
     * @throws IllegalArgumentException if the instance is not an entity
     * @since Java Persistence 2.0
     */
    public function detach(Object $entity);

    /**
     * Check if the instance is a managed entity instance belonging to the
     * current persistence context.
     *
     * @param entity entity instance
     * @return boolean indicating if entity is in persistence context
     * @throws IllegalArgumentException if not an entity
     */
    public function contains(Object $entity);

    /**
     * Get the current lock mode for the entity instance.
     *
     * @param $entity entity instance
     * @return lock mode
     * @throws TransactionRequiredException if there is no transaction or if the
     *         entity manager has not been joined to the current transaction
     * @throws IllegalArgumentException if the instance is not a managed entity
     *         and a transaction is active
     */
    public function getLockMode(Object $entity);

    /**
     * Set an entity manager property or hint. If a vendor-specific property or
     * hint is not recognized, it is silently ignored.
     *
     * @param $propertyName name of property or hint
     * @param $value value for property or hint
     * @throws IllegalArgumentException if the second argument is not valid for
     *         the implementation
     */
    public function setProperty($propertyName, $value);

    /**
     * Get the properties and hints and associated values that are in effect for
     * the entity manager. Changing the contents of the map does not change the
     * configuration in effect.
     *
     * @return map of properties and hints in effect for entity manager
     */
    public function getProperties();

    /**
     * Create an instance of <code>TypedQuery</code> for executing a criteria
     * query.
     *
     * @param criteriaQuery a criteria query object
     * @return the new query instance
     * @throws IllegalArgumentException if the criteria query is found to be
     *         invalid
     */
    public function createQuery(CriteriaQuery $criteriaQuery);

    /**
     * Create an instance of <code>Query</code> for executing a criteria update
     * query.
     *
     * @param updateQuery a criteria update query object
     * @return the new query instance
     * @throws IllegalArgumentException if the update query is found to be
     *         invalid
     */
    public function createUpdateQuery(CriteriaUpdate $updateQuery);

    /**
     * Create an instance of <code>Query</code> for executing a criteria delete
     * query.
     *
     * @param deleteQuery a criteria delete query object
     * @return the new query instance
     * @throws IllegalArgumentException if the delete query is found to be
     *         invalid
     */
    public function createDeleteQuery(CriteriaDelete $deleteQuery);

    /**
     * Create an instance of <code>TypedQuery</code> for executing a Java
     * Persistence query language statement. The select list of the query must
     * contain only a single item, which must be assignable to the type
     * specified by the <code>resultClass</code> argument.
     *
     * @param ql$a Java Persistence query string
     * @param resultClazz $the type of the query result
     * @return the new query instance
     * @throws IllegalArgumentException if the query string is found to be
     *         invalid or if the query result is found to not be assignable to
     *         the specified type
     */
    public function createSQLQuery($qlString, Clazz $resultClass);

    /**
     * Create an instance of <code>TypedQuery</code> for executing a Java
     * Persistence query language named query. The select list of the query must
     * contain only a single item, which must be assignable to the type
     * specified by the <code>resultClass</code> argument.
     *
     * @param name the name of a query defined in metadata
     * @param resultClazz $the type of the query result
     * @return the new query instance
     * @throws IllegalArgumentException if a query has not been defined with the
     *         given name or if the query string is found to be invalid or if
     *         the query result is found to not be assignable to the specified
     *         type
     */
    public function createNamedQuery($name, Clazz $resultClass = null);

    /**
     * Create an instance of <code>Query</code> for executing a native SQL
     * query.
     *
     * @param sql$a native SQL query string
     * @param resultClazz $the class of the resulting instance(s)
     * @return the new query instance
     */
    public function createNativeQuery($sqlString, Clazz $resultClass = null);

    /**
     * Create an instance of <code>StoredProcedureQuery</code> for executing a
     * stored procedure in the database. <p>Parameters must be registered before
     * the stored procedure can be executed. <p>The
     * <code>resultSetMapping</code> arguments must be specified in the order in
     * which the result sets will be returned by the stored procedure
     * invocation.
     *
     * @param procedureName name of the stored procedure in the database
     * @param resultSetMappings the names of the result set mappings to be used
     *            in mapping result sets returned by the stored procedure
     * @return the new stored procedure query instance
     * @throws IllegalArgumentException if a stored procedure or result set
     *         mapping of the given name does not exist (or the query execution
     *         will fail)
     */
    public function createStoredProcedureQuery($procedureName, array $resultSetMappings = null);

    /**
     * Indicate to the entity manager that a JTA transaction is active and join
     * the persistence context to it. <p>This method should be called on a JTA
     * application managed entity manager that was created outside the scope of
     * the active transaction or on an entity manager of type
     * <code>SynchronizationType.UNSYNCHRONIZED</code> to associate it with the
     * current JTA transaction.
     *
     * @throws TransactionRequiredException if there is no transaction
     */
    public function joinTransaction();

    /**
     * Determine whether the entity manager is joined to the current
     * transaction. Returns false if the entity manager is not joined to the
     * current transaction or if no transaction is active
     *
     * @return boolean
     */
    public function isJoinedToTransaction();

    /**
     * Return an object of the specified type to allow access to the
     * provider-specific API. If the provider's <code>EntityManager</code>
     * implementation does not support the specified class, the
     * <code>PersistenceException</code> is thrown.
     *
     * @param cls the class of the object to be returned. This is normally
     *            either the underlying <code>EntityManager</code>
     *            implementation class or an interface that it implements.
     * @return an instance of the specified class
     * @throws PersistenceException if the provider does not support the call
     */
    public function unwrap(Clazz $cls);

    /**
     * Return the underlying provider object for the <code>EntityManager</code>,
     * if available. The result of this method is implementation specific.
     * <p>The <code>unwrap</code> method is to be preferred for new
     * applications.
     *
     * @return underlying provider object for EntityManager
     */
    public function getDelegate();

    /**
     * Close an application-managed entity manager. After the close method has
     * been invoked, all methods on the <code>EntityManager</code> instance and
     * any <code>Query</code>, <code>TypedQuery</code>, and
     * <code>StoredProcedureQuery</code> objects obtained from it will throw the
     * <code>IllegalStateException</code> except for <code>getProperties</code>,
     * <code>getTransaction</code>, and <code>isOpen</code> (which will return
     * false). If this method is called when the entity manager is joined to an
     * active transaction, the persistence context remains managed until the
     * transaction completes.
     *
     * @throws IllegalStateException if the entity manager is container-managed
     */
    public function close();

    /**
     * Determine whether the entity manager is open.
     *
     * @return true until the entity manager has been closed
     */
    public function isOpen();

    /**
     * Return the resource-level <code>EntityTransaction</code> object. The
     * <code>EntityTransaction</code> instance may be used serially to begin and
     * commit multiple transactions.
     *
     * @return EntityTransaction instance
     * @throws IllegalStateException if invoked on a JTA entity manager
     */
    public function getTransaction();

    /**
     * Return the entity manager factory for the entity manager.
     *
     * @return EntityManagerFactory instance
     * @throws IllegalStateException if the entity manager has been closed
     */
    public function getEntityManagerFactory();

    /**
     * Return an instance of <code>CriteriaBuilder</code> for the creation of
     * <code>CriteriaQuery</code> objects.
     *
     * @return CriteriaBuilder instance
     * @throws IllegalStateException if the entity manager has been closed
     * @since Java Persistence 2.0
     */
    public function getCriteriaBuilder();

    /**
     * Return an instance of <code>Metamodel</code> interface for access to the
     * metamodel of the persistence unit.
     *
     * @return Metamodel instance
     * @throws IllegalStateException if the entity manager has been closed
     */
    public function getMetamodel();
}
?>