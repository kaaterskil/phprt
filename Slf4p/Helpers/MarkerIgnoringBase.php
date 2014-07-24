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

use Slf4p\Marker;

/**
 * This class serves as base for adapters or native implementations of logging
 * systems lacking Marker support. In this implementation, methods taking marker
 * data simply invoke the corresponding method without the Marker argument,
 * discarding any marker data passed as argument.
 *
 * @author Blair
 */
abstract class MarkerIgnoringBase extends NamedLoggerBase
{

    public function isTraceWithMarkerEnabled(Marker $marker)
    {
        return $this->isTraceEnabled();
    }

    public function traceWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->trace($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isDebugWithMarkerEnabled(Marker $marker)
    {
        return $this->isDebugEnabled();
    }

    public function debugWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->debug($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isInfoWithMarkerEnabled(Marker $marker)
    {
        return $this->isInfoEnabled();
    }

    public function infoWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->info($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isWarnWithMarkerEnabled(Marker $marker)
    {
        return $this->isWarnEnabled();
    }

    public function warnWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->warn($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function isErrorWithMarkerEnabled(Marker $marker)
    {
        return $this->isErrorEnabled();
    }

    public function errorWithMarker(Marker $marker, $msgOrFormat, $arg1 = null, $arg2 = null, Exception $throwable = null)
    {
        $this->error($msgOrFormat, $arg1, $arg2, $throwable);
    }

    public function __toString()
    {
        return $this->getClass()->getName() . '(' . $this->getName() . ')';
    }
}
?>