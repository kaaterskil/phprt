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

use KM\Lang\Object;
use Slf4p\Logger;
use Slf4p\Marker;

/**
 * A logger implementation which logs via a delegate logger. By default, the
 * delegate is a NOPLogger. However, a different delegate can be set at any
 * time.
 *
 * @author Blair
 */
class SubstituteLogger extends Object implements Logger
{

    /**
     * The logger name.
     *
     * @var string
     */
    private $name;

    /**
     * The backing logger.
     *
     * @var Logger
     */
    private $delegate;

    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isTraceEnabled()
    {
        return $this->getDelegate()->isTraceEnabled();
    }

    public function trace($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        $this->getDelegate()->trace($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isTraceWithMarkerEnabled(Marker $marker)
    {
        return $this->getDelegate()->isTraceWithMarkerEnabled($marker);
    }

    public function traceWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->getDelegate()->traceWithMarker($marker, $msgOrFormat . $arg1, $arg2, $throwable);
    }

    public function isDebugEnabled()
    {
        return $this->getDelegate()->isDebugEnabled();
    }

    public function debug($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        $this->getDelegate()->debug($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isDebugWithMarkerEnabled(Marker $marker)
    {
        return $this->getDelegate()->isDebugWithMarkerEnabled($marker);
    }

    public function debugWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        $this->getDelegate()->debugWithMarker($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isInfoEnabled()
    {
        return $this->getDelegate()->isInfoEnabled();
    }

    public function info($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        $this->getDelegate()->info($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isInfoWithMarkerEnabled(Marker $marker)
    {
        return $this->getDelegate()->isInfoWithMarkerEnabled($marker);
    }

    public function infoWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->getDelegate()->infoWithMarker($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isWarnEnabled()
    {
        return $this->getDelegate()->isWarnEnabled();
    }

    public function warn($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        $this->getDelegate()->warn($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isWarnWithMarkerEnabled(Marker $marker)
    {
        return $this->getDelegate()->isWarnWithMarkerEnabled($marker);
    }

    public function warnWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->getDelegate()->debugWithMarker($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isErrorEnabled()
    {
        return $this->getDelegate()->isErrorEnabled();
    }

    public function error($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        $this->getDelegate()->error($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isErrorWithMarkerEnabled(Marker $marker)
    {
        return $this->getDelegate()->isErrorWithMarkerEnabled($marker);
    }

    public function errorWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->getDelegate()->errorWithMarker($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function equals(Object $obj = null)
    {
        /* @var $that SubstituteLogger */
        if ($obj === $this) {
            return true;
        }
        if ($obj == null || $this->getClass()->getName() != $obj->getClass()->getName()) {
            return false;
        }
        $that = $obj;
        if ($this->name != $that->name) {
            return false;
        }
        return true;
    }

    /**
     * Returns the delegate logger instance if set, otherwise returns a
     * NOPLogger instance.
     *
     * @return \Slf4p\Logger
     */
    public function getDelegate()
    {
        return $this->delegate != null ? $this->delegate : NOPLogger::getInstance();
    }

    /**
     * Typically called after the LoggerFactory initialization phase is
     * completed.
     *
     * @param Logger $delegate
     */
    public function setDelegate(Logger $delegate)
    {
        $this->delegate = $delegate;
    }
}
?>