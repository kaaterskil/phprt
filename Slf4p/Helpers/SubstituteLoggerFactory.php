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
use KM\Util\ArrayList;
use KM\Util\HashMap;
use Slf4p\Helpers\SubstituteLogger;
use Slf4p\LoggerFactory;

/**
 * SubstituteLoggerFactory manages instance of SubstituteLogger.
 *
 * @author Blair
 */
class SubstituteLoggerFactory extends Object implements LoggerFactory
{

    /**
     * A collection of substitute loggers mapped by their name.
     *
     * @var HashMap
     */
    protected $loggers;

    public function __construct()
    {
        $this->loggers = new HashMap('<string, \Slf4p\Helpers\SubstituteLogger>');
    }

    public function getLogger($name)
    {
        /* @var $logger SubstituteLogger */
		/* @var $oldLogger SubstituteLogger */
		$logger = $this->loggers->get($name);
        if ($logger == null) {
            $logger = new SubstituteLogger($name);
            $oldLogger = $this->loggers->putIfAbsent($name, $logger);
            if ($oldLogger != null) {
                $logger = $oldLogger;
            }
        }
        return $logger;
    }

    public function getLoggerNames()
    {
        return new ArrayList('string', $this->loggers->keySet());
    }

    public function getLoggers()
    {
        return new ArrayList('\Slf4p\Helpers\SubstituteLogger', $this->loggers->values());
    }

    public function clear()
    {
        $this->loggers->clear();
    }
}
?>