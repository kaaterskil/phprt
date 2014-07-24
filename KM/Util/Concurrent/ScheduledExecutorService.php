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

use KM\Lang\Runnable;

/**
 * An ExecutorService that can schedule commands to run after a given delay, or
 * to execute periodically.
 *
 * @author Blair
 */
interface ScheduledExecutorService extends ExecutorService
{

    /**
     * Creates and executes a one-shot action that becomes enabled after the
     * given delay.
     *
     * @param Runnable $command
     * @param int $delay
     */
    public function schedule(Runnable $command, $delay);

    /**
     * Creates and executes a periodic action that becomes enabled first after
     * the given initial delay, and subsequently with the given period; that is
     * executions will commence after initialDelay< then
     * <tt>initialDelay+period</tt>, then initialDelay + 2 * period, and so on.
     * If any execution of the task encounters an exception, subsequent
     * executions are suppressed. Otherwise, the task will only terminate via
     * cancellation or termination of the executor. If any execution of this
     * task takes longer than its period, then subsequent executions may start
     * late, but will not concurrently execute.
     *
     * @param Runnable $command
     * @param int $initalDelay
     * @param int $period
     */
    public function scheduleAtFixedRate(Runnable $command, $initalDelay, $period);

    /**
     * Creates and executes a periodic action that becomes enabled first after
     * the given initial delay, and subsequently with the given delay between
     * the termination of one execution and the commencement of the next.
     * If any execution of the task encounters an exception, subsequent
     * executions are suppressed. Otherwise, the task will only terminate via
     * cancellation or termination of the executor.
     *
     * @param Runnable $command
     * @param int $initalDelay
     * @param int $delay
     */
    public function scheduleWithFixedDelay(Runnable $command, $initalDelay, $delay);
}
?>