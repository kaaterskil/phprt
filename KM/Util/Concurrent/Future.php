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

use KM\Lang\InterruptedException;
use KM\Util\Concurrent\CancellationException;
use KM\Util\Concurrent\ExecutionException;
use KM\Util\Concurrent\TimeUnit;

/**
 * Represents the result of an asynchronous computation. Methods are provided to
 * check of the computation is complete, to wait for its completion, and to
 * retrieve the result of the computation.
 *
 * @author Blair
 */
interface Future
{

    /**
     * Attempts to cancel execution of this task. This attempt will fail if the
     * task has already completed, has already been cancelled, or could not be
     * cancelled for some other reason. If successful, and this task has not
     * started when cancel was called, this task should never run. If the task
     * has already started, then the $mayInterruptIfRunning parameter determined
     * whether the thread executing this task should be interrupted in an
     * attempt to stop the task.
     * After this method returns, subsequent calls to #isDone() will always
     * return true. Subsequent calls to #isCancelled() will always return true
     * if this method returned true.
     *
     * @param boolean $mayInterruptIfRunning
     * @return boolean
     */
    public function cancel($mayInterruptIfRunning);

    /**
     * Returns true if this task was cancelled before it completed normally.
     *
     * @return boolean
     */
    public function isCancelled();

    /**
     * Returns true if this task completed. Completion may be due to normal
     * termination, an exception, or cancellation. In all of these cases the
     * method will return true.
     *
     * @return boolean
     */
    public function isDone();

    /**
     * Waits if necessary for the computation to complete, and retrieves its
     * result.
     *
     * @param int $timeout The maximum time to wait
     * @param TimeUnit $unit The time unit of the $timeout argument.
     * @throws CancellationException if the computation was cancelled
     * @throws ExecutionException if the computation threw an exception
     * @throws InterruptedException if the current thread was interrupted while
     *         waiting
     */
    public function get($timeout = null, TimeUnit $unit = null);
}
?>