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
namespace Slf4p\Simple;

use KM\Lang\Object;
use KM\Util\HashMap;
use Slf4p\Logger;
use Slf4p\LoggerFactory;

/**
 * An implementation of LoggerFactory which always returns SimpleLogger
 * instances.
 *
 * @author Blair
 */
class SimpleLoggerFactory extends Object implements LoggerFactory
{

    /**
     * A collection of loggers mapped by their name.
     *
     * @var HashMap
     */
    protected $loggerMap;

    /**
     * Constructs an instance of this class.
     */
    public function __construct()
    {
        $this->loggerMap = new HashMap('<string, \Slf4p\Logger>');
    }

    /**
     * Return an appropriate instance by name.
     *
     * @param string $name
     * @return \Slf4p\Logger
     * @see \Slf4p\LoggerFactory::getLogger()
     */
    public function getLogger($name)
    {
        /* @var $logger Logger */
		/* @var $newInstance Logger */
		/* @var $oldInstance Logger */
		$simpleLogger = $this->loggerMap->get($name);
        if ($simpleLogger != null) {
            return $simpleLogger;
        } else {
            $newInstance = new SimpleLogger($name);
            $oldInstance = $this->loggerMap->putIfAbsent($name, $newInstance);
            return ($oldInstance == null) ? $newInstance : $oldInstance;
        }
    }

    /**
     * Clears the internal logger cache. This method is intended to be called by
     * classes (in the same package) for testing purposes. This method is
     * internal. It can be modified, renamed or removed at any time without
     * notice. YOu are strongly discourages from calling this method in
     * production code.
     */
    public function reset()
    {
        $this->loggerMap->clear();
    }
}
?>