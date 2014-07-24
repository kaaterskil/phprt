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
namespace Slf4p\Helpers;

/**
 * A direct NOP (no operation) implementation of Logger.
 *
 * @author Blair
 */
class NOPLogger extends MarkerIgnoringBase
{

    /**
     * The unique instance of NOPLogger.
     *
     * @var NOPLogger
     */
    private static $instance;

    /**
     * Returns the singleton instance of NOPLogger.
     *
     * @return \Slf4p\Helpers\NOPLogger
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Protected constructor.
     */
    protected function __construct()
    {}

    /**
     * Always returns the string value 'NOP'.
     *
     * @return string
     * @see \Slf4p\Helpers\NamedLoggerBase::getName()
     */
    public function getName()
    {
        return 'NOP';
    }

    /**
     * Always returns false.
     *
     * @return boolean
     * @see \Slf4p\Logger::isTraceEnabled()
     */
    public function isTraceEnabled()
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function trace($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the TRACE level, false
     *         otherwise.
     */
    public function isTraceWithMarkerEnabled(Marker $marker)
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function traceWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @return boolean True if this Logger is enabled for the DEBUG level, false
     *         otherwise.
     */
    public function isDebugEnabled()
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function debug($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the DEBUG level, false
     *         otherwise.
     */
    public function isDebugWithMarkerEnabled(Marker $marker)
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function debugWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @return boolean True if this Logger is enabled for the INFO level, false
     *         otherwise.
     */
    public function isInfoEnabled()
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function info($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the INFO level, false
     *         otherwise.
     */
    public function isInfoWithMarkerEnabled(Marker $marker)
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function infoWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @return boolean True if this Logger is enabled for the WARN level, false
     *         otherwise.
     */
    public function isWarnEnabled()
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function warn($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the INFO level, false
     *         otherwise.
     */
    public function isWarnWithMarkerEnabled(Marker $marker)
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function warnWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @return boolean True if this Logger is enabled for the ERROR level, false
     *         otherwise.
     */
    public function isErrorEnabled()
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function error($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        // Noop
    }

    /**
     * Always returns false.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the INFO level, false
     *         otherwise.
     */
    public function isErrorWithMarkerEnabled(Marker $marker)
    {
        return false;
    }

    /**
     * A NOP implementation.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param \Exception $throwable The exception to log.
     */
    public function errorWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        // Noop
    }
}
?>