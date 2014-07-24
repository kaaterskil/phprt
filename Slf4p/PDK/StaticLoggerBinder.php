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
use Slf4p\LoggerFactory;
use Slf4p\Spi\LoggerFactoryBinder;

/**
 * The binding of LoggerFactory class with an actual instance of LoggerFactory
 * is performed using information returned by this class.
 *
 * @author Blair
 */
class StaticLoggerBinder extends Object implements LoggerFactoryBinder
{

    /**
     * The singleton instance
     *
     * @var \Slf4p\Impl\StaticLoggerBinder
     */
    private static $instance;

    /**
     * The default logger factory class name.
     *
     * @var string
     */
    private static $loggerFactoryClassStr = '\Slf4p\PDK\PDKLoggerFactory';

    /**
     * Return the singleton of this class.
     *
     * @return \Slf4p\Impl\StaticLoggerBinder The StaticLoggerBinder singleton.
     */
    public static final function getSingleton()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * The LoggerFactory instance returned by getLoggerFactory() method should
     * always be the same object.
     *
     * @var \Slf4p\LoggerFactory
     */
    private $loggerFactory;

    /**
     * Private constructor prevents direct instantiation.
     */
    private function __construct()
    {
        $this->loggerFactory = new PDKLoggerFactory();
    }

    /**
     * Returns the logger factory.
     *
     * @return \Slf4p\LoggerFactory
     * @see \Slf4p\Spi\LoggerFactoryBinder::getLoggerFactory()
     */
    public function getLoggerFactory()
    {
        return $this->loggerFactory;
    }

    /**
     * Returns the class name of the default logger factory.
     *
     * @return string
     * @see \Slf4p\Spi\LoggerFactoryBinder::getLoggerFactoryClassStr()
     */
    public function getLoggerFactoryClassStr()
    {
        return self::$loggerFactoryClassStr;
    }
}
?>