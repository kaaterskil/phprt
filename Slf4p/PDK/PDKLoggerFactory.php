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

use KM\Lang\Object;
use KM\Util\HashMap;
use KM\Util\Logging\Logger as phpLogger;
use Slf4p\Logger;
use Slf4p\LoggerFactory;

/**
 * PDKLoggerFactory is an implementation of LoggerFactory returning
 * PDKLoggerAdapter instances. These instances wrap normal PHP logging objects.
 *
 * @author Blair
 */
class PDKLoggerFactory extends Object implements LoggerFactory
{

    /**
     * The collection of loggers mapped by name.
     *
     * @var HashMap
     */
    protected $loggerMap;

    /**
     * Creates an instance of this class.
     */
    public function __construct()
    {
        $this->loggerMap = new HashMap('<string, \Slf4p\Logger>');
    }

    /**
     * Return a logger adapter wrapping a PHP logger instance.
     *
     * @param string $name
     * @return \Slf4p\Logger
     * @see \Slf4p\LoggerFactory::getLogger()
     */
    public function getLogger($name)
    {
        /* @var $slf4pLogger Logger */
		/* @var $newInstance Logger */
		/* @var $oldInstance Logger */
		/* #var $phpLogger \KM\Util\Logging\Logger */
		$name = (string) $name;
        
        // The root logger in KM\Util\Logging is ""
        if (strtolower($name) === strtolower(Logger::ROOT_LOGGER_NAME)) {
            $name = '';
        }
        
        $slf4pLogger = $this->loggerMap->get($name);
        if ($slf4pLogger != null) {
            return $slf4pLogger;
        } else {
            $phpLogger = \KM\Util\Logging\Logger::getLogger($name);
            $newInstance = new PDKLoggerAdapter($phpLogger);
            $oldInstance = $this->loggerMap->putIfAbsent($name, $newInstance);
            return ($oldInstance == null) ? $newInstance : $oldInstance;
        }
    }
}
?>