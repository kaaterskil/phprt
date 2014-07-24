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

use KM\Lang\Object;

/**
 * An Executor that provides methods to manage termination and methods that can
 * produce a Future for tracking progress of on or more asynchronous tasks.
 *
 * @author Blair
 */
interface ExecutorService extends Executor
{

    /**
     * Initiates an orderly shutdown in which previously submitted tasks are
     * executed, but no new tasks will be accepted.
     * Invocation has no additional effect if already shut down. This method
     * does not wait for previously submitted tasks to complete execution. Use
     * #awaitTermination() to do that.
     * @return void
     */
    public function shutdown();

    /**
     * Returns true if this Executor has shut down.
     *
     * @return boolean
     */
    public function isShutdown();

    /**
     * Returns true if all tasks have been complete following shut down. Note
     * that #isTerminated() never returns true unless #shutdown is called first.
     *
     * @return boolean
     */
    public function isTerminated();

    /**
     * Blocks until all tasks have completed execution after a shutdown request
     * or the timeout occurs, or the current thread is interrupted, whichever
     * happens first.
     *
     * @param int $timeout
     */
    pUblic function awaitTermination($timeout);

    /**
     * Submits a Stackable task for execution and returns a Future representing
     * that task. The Future's #get() method will return the given result upon
     * successful completion.
     *
     * @param Runnable $task
     * @param Future $result
     */
    public function submit(Runnable $task, Object $result = null);

    /**
     * Executes the given tasks, returning a list of Futures holding their
     * status and results when all complete or the timeout expires, whichever
     * happens first. Future#isDone() is true for each element of the returned
     * list. Upon return, tasks that have not completed are cancelled. Note that
     * a completed task could have terminated either normally or by throwing an
     * exception. The results of this method are undefined if the given
     * collection is modified while this operation is in progress.
     *
     * @param array $tasks
     * @param int $timeout
     * @return array
     */
    public function invokeAll(array $tasks, $timeout);

    /**
     * Executes the given tasks, returning the result of one that has completed
     * successfully (i.e., without throwing an exception), if any do before the
     * given timeout elapses.
     * Upon normal or exceptional return, tasks that have not completed are
     * cancelled. The results of this method are undefined if the given
     * collection is modified while this operation is in progress.
     * @param array $tasks
     * @param int $timeout
     * @return Object
     */
    public function invokeAny(array $tasks, $timeout);
}
?>