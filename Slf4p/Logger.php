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
namespace Slf4p;

use Exception;

/**
 * The slf4p.Logger interface is the main user entry point of SLF4P API. It is
 * expected that logging takes place through concrete implementations of this
 * interface. <p/> <h3>Typical usage pattern:</h3> <pre> use
 * Slf4p\LoggerFactory;
 * public class Wombat {
 * <span style="color:green">final static Logger logger =
 * LoggerFactory.getLogger(Wombat.class);</span> Integer t; Integer oldT;
 * public void setTemperature(Integer temperature) { oldT = t; t = temperature;
 * <span style="color:green">logger.debug("Temperature set to {}. Old
 * temperature was {}.", t, oldT);</span> if(temperature.intValue() > 50) {
 * <span style="color:green">logger.info("Temperature has risen above 50
 * degrees.");</span> } } } </pre>
 * Be sure to read the FAQ entry relating to <a
 * href="../../../faq.html#logging_performance">parameterized logging</a>. Note
 * that logging statements can be parameterized in <a
 * href="../../../faq.html#paramException">presence of an
 * exception/throwable</a>.
 * <p>Once you are comfortable using loggers, i.e. instances of this interface,
 * consider using <a href="MDC.html">MDC</a> as well as <a
 * href="Marker.html">Markers</a>.</p>
 *
 * @package Slf4p
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface Logger
{

    /**
     * Constant used to retrieve the name of the root logger.
     *
     * @var string
     */
    const ROOT_LOGGER_NAME = 'ROOT';

    /**
     * Returns the name of this Logger instance.
     *
     * @return string Name of this Logger instance.
     */
    public function getName();

    /**
     * Is the Logger instance enabled for the TRACE level?
     *
     * @return boolean True if this Logger is enabled for the TRACE level, false
     *         otherwise.
     */
    public function isTraceEnabled();

    /**
     * Log a message object at level TRACE This method will process parameters
     * in the following combinations: <ol> <li>When $msgOrFormat represents the
     * message string with no other parameters; <li>When $msgOrFormat represents
     * the format pattern and a) $arg1 represents a single argument of either
     * object, scalar or array type, or b) both $arg1 and $arg2 are arguments of
     * scalar or object types; or <li>When $msgOrFormat represents the message
     * string and $throwable represents an exception. $arg1 and $arg2 should be
     * null. </ol>
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function trace($msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Similar to isTraceEnabled() method except that the marker data is also
     * taken into account.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the TRACE level, false
     *         otherwise.
     */
    public function isTraceWithMarkerEnabled(Marker $marker);

    /**
     * Similar to trace() method except that the marker data is also taken into
     * account.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function traceWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Is the Logger instance enabled for the DEBUG level?
     *
     * @return boolean True if this Logger is enabled for the DEBUG level, false
     *         otherwise.
     */
    public function isDebugEnabled();

    /**
     * Log a message object at level DEBUG This method will process parameters
     * in the following combinations: <ol> <li>When $msgOrFormat represents the
     * message string with no other parameters; <li>When $msgOrFormat represents
     * the format pattern and a) $arg1 represents a single argument of either
     * object, scalar or array type, or b) both $arg1 and $arg2 are arguments of
     * scalar or object types; or <li>When $msgOrFormat represents the message
     * string and $throwable represents an exception. $arg1 and $arg2 should be
     * null. </ol>
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function debug($msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Similar to isDebugEnabled() method except that the marker data is also
     * taken into account.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the DEBUG level, false
     *         otherwise.
     */
    public function isDebugWithMarkerEnabled(Marker $marker);

    /**
     * Similar to debug() method except that the marker data is also taken into
     * account.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function debugWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Is the Logger instance enabled for the INFO level?
     *
     * @return boolean True if this Logger is enabled for the INFO level, false
     *         otherwise.
     */
    public function isInfoEnabled();

    /**
     * Log a message object at level INFO This method will process parameters in
     * the following combinations: <ol> <li>When $msgOrFormat represents the
     * message string with no other parameters; <li>When $msgOrFormat represents
     * the format pattern and a) $arg1 represents a single argument of either
     * object, scalar or array type, or b) both $arg1 and $arg2 are arguments of
     * scalar or object types; or <li>When $msgOrFormat represents the message
     * string and $throwable represents an exception. $arg1 and $arg2 should be
     * null. </ol>
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function info($msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Similar to isInfoEnabled() method except that the marker data is also
     * taken into account.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the INFO level, false
     *         otherwise.
     */
    public function isInfoWithMarkerEnabled(Marker $marker);

    /**
     * Similar to info() method except that the marker data is also taken into
     * account.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function infoWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Is the Logger instance enabled for the WARN level?
     *
     * @return boolean True if this Logger is enabled for the WARN level, false
     *         otherwise.
     */
    public function isWarnEnabled();

    /**
     * Log a message object at level WARN This method will process parameters in
     * the following combinations: <ol> <li>When $msgOrFormat represents the
     * message string with no other parameters; <li>When $msgOrFormat represents
     * the format pattern and a) $arg1 represents a single argument of either
     * object, scalar or array type, or b) both $arg1 and $arg2 are arguments of
     * scalar or object types; or <li>When $msgOrFormat represents the message
     * string and $throwable represents an exception. $arg1 and $arg2 should be
     * null. </ol>
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function warn($msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Similar to isWarnEnabled() method except that the marker data is also
     * taken into account.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the INFO level, false
     *         otherwise.
     */
    public function isWarnWithMarkerEnabled(Marker $marker);

    /**
     * Similar to warn() method except that the marker data is also taken into
     * account.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function warnWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Is the Logger instance enabled for the ERROR level?
     *
     * @return boolean True if this Logger is enabled for the ERROR level, false
     *         otherwise.
     */
    public function isErrorEnabled();

    /**
     * Log a message object at level ERROR This method will process parameters
     * in the following combinations: <ol> <li>When $msgOrFormat represents the
     * message string with no other parameters; <li>When $msgOrFormat represents
     * the format pattern and a) $arg1 represents a single argument of either
     * object, scalar or array type, or b) both $arg1 and $arg2 are arguments of
     * scalar or object types; or <li>When $msgOrFormat represents the message
     * string and $throwable represents an exception. $arg1 and $arg2 should be
     * null. </ol>
     *
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function error($msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);

    /**
     * Similar to isErrorEnabled() method except that the marker data is also
     * taken into account.
     *
     * @param Marker $marker The marker data to take into consideration.
     * @return boolean True if this Logger is enabled for the INFO level, false
     *         otherwise.
     */
    public function isErrorWithMarkerEnabled(Marker $marker);

    /**
     * Similar to error() method except that the marker data is also taken into
     * account.
     *
     * @param $marker The marker data specific to this log statement.
     * @param string $msgOrFormat The format or message string.
     * @param mixed $arg1 The first argument.
     * @param mixed $arg2 The second argument.
     * @param Exception $throwable The exception to log.
     */
    public function errorWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null);
}
?>