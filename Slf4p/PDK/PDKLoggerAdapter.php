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
namespace Slf4p\PDK;

use KM\Util\Logging\Level;
use KM\Util\Logging\Logger as phpLogger;
use KM\Util\Logging\LogRecord;
use Slf4p\Helpers\MarkerIgnoringBase;

/**
 * PDKLoggerAdapter Class
 *
 * @package Slf4p\PDK
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class PDKLoggerAdapter extends MarkerIgnoringBase
{

    /**
     * This class name.
     *
     * @var string
     */
    protected static $SELF;

    /**
     * The parent class name of this class.
     *
     * @var string
     */
    protected static $SUPER;

    /**
     * Static constructor
     */
    public static function construct()
    {
        $clazz = new \ReflectionClass(get_called_class());
        self::$SELF = $clazz->getName();
        self::$SUPER = $clazz->getParentClass()->getName();
    }

    /**
     * The backing Logger.
     *
     * @var \KM\Util\Logging\Logger
     */
    private $logger;

    /**
     * Creates an instance of this adapter with the given underlying logger.
     *
     * @param phpLogger $logger The PHP logger.
     */
    public function __construct(phpLogger $logger)
    {
        $this->logger = $logger;
        $this->name = $logger->getName();
    }

    public function isTraceEnabled()
    {
        return $this->logger->isLoggable(Level::$FINEST);
    }

    public function trace($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        if ($this->logger->isLoggable(Level::$FINEST)) {
            $args = $this->setArgs($arg1, $arg2);
            $this->log(self::$SELF, Level::$FINEST, $msgOrFormat, $args, $throwable);
        }
    }

    public function isDebugEnabled()
    {
        return $this->logger->isLoggable(Level::$FINE);
    }

    public function debug($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        if ($this->logger->isLoggable(Level::$FINE)) {
            $args = $this->setArgs($arg1, $arg2);
            $this->log(self::$SELF, Level::$FINE, $msgOrFormat, $args, $throwable);
        }
    }

    public function isInfoEnabled()
    {
        return $this->logger->isLoggable(Level::$INFO);
    }

    public function info($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        if ($this->logger->isLoggable(Level::$INFO)) {
            $args = $this->setArgs($arg1, $arg2);
            $this->log(self::$SELF, Level::$INFO, $msgOrFormat, $args, $throwable);
        }
    }

    public function isWarnEnabled()
    {
        return $this->logger->isLoggable(Level::$WARNING);
    }

    public function warn($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        if ($this->logger->isLoggable(Level::$WARNING)) {
            $args = $this->setArgs($arg1, $arg2);
            $this->log(self::$SELF, Level::$WARNING, $msgOrFormat, $args, $throwable);
        }
    }

    public function isErrorEnabled()
    {
        return $this->logger->isLoggable(Level::$SEVERE);
    }

    public function error($msgOrFormat, $arg1 = null, $arg2 = null, \Exception $throwable = null)
    {
        if ($this->logger->isLoggable(Level::$SEVERE)) {
            $args = $this->setArgs($arg1, $arg2);
            $this->log(self::$SELF, Level::$SEVERE, $msgOrFormat, $args, $throwable);
        }
    }

    private function log($callerFQCN, Level $level, $msg, array $args = null, \Exception $t = null)
    {
        /* @var $record LogRecord */
        $record = new LogRecord($level, $msg);
        $record->setLoggerName($this->getName());
        $record->setParameters($args);
        $record->setThrown($t);
        $this->fillCallerData($callerFQCN, $record);
        $this->logger->logRecord($record);
    }

    private final function fillCallerData($callerFQCN, LogRecord $record)
    {
        $steArray = debug_backtrace();
        
        $selfIndex = - 1;
        for ($i = 0; $i < count($steArray); $i ++) {
            $cname = isset($steArray[$i]['class']) ? $steArray[$i]['class'] : '';
            if (($cname == $callerFQCN) || ($cname == self::$SUPER)) {
                $selfIndex = $i;
                break;
            }
        }
        
        $found = - 1;
        for ($i = $selfIndex + 1; $i < count($steArray); $i ++) {
            $cname = isset($steArray[$i]['class']) ? $steArray[$i]['class'] : '';
            if (! (($cname == $callerFQCN) || ($cname == self::$SUPER))) {
                $found = $i;
                break;
            }
        }
        
        if ($found != - 1) {
            $cname = isset($steArray[$found]['class']) ? $steArray[$found]['class'] : $steArray[$found]['file'];
            $mname = isset($steArray[$found]['function']) ? $steArray[$found]['function'] : 'unknown method';
            $record->setSourceClassName($cname);
            $record->setSourceMethodName($mname);
        }
    }

    private function setArgs($arg1, $arg2)
    {
        $a = array();
        if ($arg1 != null) {
            $a[0] = $arg1;
        }
        if ($arg2 != null) {
            $a[1] = $arg2;
        }
        return $a;
    }
}

// Initialize static members.
PDKLoggerAdapter::construct();
?>