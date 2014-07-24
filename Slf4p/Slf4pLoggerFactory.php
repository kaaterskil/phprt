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

use KM\IO\File;
use KM\Lang\Clazz;
use KM\Lang\Object;
use KM\Lang\IllegalStateException;
use KM\Lang\System;
use KM\Util\Properties;
use Slf4p\Helpers\NOPLoggerFactory;
use Slf4p\Helpers\SubstituteLoggerFactory;
use Slf4p\Spi\LoggerFactoryBinder;

/**
 * The Slf4pLoggerFactory is a utility class producing Loggers for the PHP
 * logging system. Slf4pLoggerFactory is essentially a wrapper around a
 * LoggerFactory instance bound with SlfLoggerFactory at runtime using a
 * namespace provided by a 'slf4p.properties' file specifying the
 * 'slf4p.impl.staticLoggerBinderNS' namespace located in this package. All
 * methods in this class are static.
 *
 * @author Blair
 */
final class Slf4pLoggerFactory extends Object
{

    const UNINITIALIZED = 0;

    const ONGOING_INITIALIZATION = 1;

    const FAILED_INITIALIZATION = 2;

    const SUCCESSFUL_INITIALIZATION = 3;

    const NOP_FALLBACK_INITIALIZATION = 4;

    const UNSUCCESSFUL_INIT_MESSAGE = 'LoggerFactory could not be initialized';

    /**
     * The initialization state.
     *
     * @var int
     */
    protected static $initializationState = self::UNINITIALIZED;

    /**
     * The fall-back logger factory
     *
     * @var NOPLoggerFactory
     */
    protected static $nopFallbackFactory;

    /**
     * The substitute logger factory
     *
     * @var SubstituteLoggerFactory
     */
    protected static $substituteFactory;

    /**
     * The temporary logger factory
     *
     * @var NOPLoggerFactory
     */
    protected static $tempFactory;

    /**
     * The logger factory binder
     *
     * @var LoggerFactoryBinder
     */
    protected static $loggerFactoryBinder;

    /**
     * Static constructor called by class loader
     */
    public static function clinit()
    {
        self::$tempFactory = new SubstituteLoggerFactory();
        self::$nopFallbackFactory = new NOPLoggerFactory();
    }

    /**
     * Private constructor prevents instantiation
     */
    private function __construct()
    {}

    private final static function performInitialization()
    {
        self::bind();
        if (self::$initializationState == self::SUCCESSFUL_INITIALIZATION) {
            // Noop
        }
    }

    private final static function bind()
    {
        $ns = self::findStaticLoggerBinderNamespace();
        if (! empty($ns)) {
            try {
                $cname = $ns . '\StaticLoggerBinder';
                self::$loggerFactoryBinder = $cname::getSingleton();
                self::$initializationState = self::SUCCESSFUL_INITIALIZATION;
            } catch (\ReflectionException $e) {
                self::$initializationState = self::NOP_FALLBACK_INITIALIZATION;
                $data = "Failed to load class '" . $cname . "'\n";
                $data .= "Defaulting to no-operation (NOOP) logger implementation \n";
                $data .= $e->getTraceAsString();
                trigger_error($data);
            }
        } else {
            self::$initializationState = self::FAILED_INITIALIZATION;
            $data = "Failed to load static logger binder. No namespace found.";
            trigger_error($data);
        }
    }

    /**
     * Returns the namespace of the static logger binder.
     *
     * @throws IllegalStateException
     * @return string The FQCN of the static logger binder
     */
    private static function findStaticLoggerBinderNamespace()
    {
        $fname = System::getProperty('slf4p.impl.staticLoggerBinderNS');
        if ($fname == null) {
            $fname = System::getProperty('php.home');
            if ($fname == null) {
                throw new IllegalStateException("Can't find php.home");
            }
        }
        $ns = '';
        $f = new File($fname, 'Slf4p');
        $f = new File($f, 'slf4p.properties');
        if ($f->exists()) {
            $props = new Properties();
            $props->load($f->getPath());
            $ns = $props->getProperty('slf4p.impl.staticLoggerBinderNS');
        }
        return $ns;
    }

    /**
     * Return a logger names according to either the given name string or class
     * object passed as a parameter, using the statically bound LoggerFactory
     * instance.
     *
     * @param unknown $clazzOrName
     * @return \Slf4p\Logger
     */
    public static function getLogger($clazzOrName)
    {
        /* @var $clazz Clazz */
		/* @var $loggerFactory LoggerFactory */
		if ($clazzOrName instanceof Clazz) {
            $clazz = $clazzOrName;
            return self::getLogger($clazz->getName());
        }
        $name = (string) $clazzOrName;
        $loggerFactory = self::getLoggerFactory();
        return $loggerFactory->getLogger($name);
    }

    /**
     * Return the LoggerFactory instance in use.
     *
     * @throws IllegalStateException
     * @return \Slf4p\LoggerFactory
     */
    public static function getLoggerFactory()
    {
        if (self::$initializationState == self::UNINITIALIZED) {
            self::$initializationState = self::ONGOING_INITIALIZATION;
            self::performInitialization();
        }
        switch (self::$initializationState) {
            case self::SUCCESSFUL_INITIALIZATION:
                return self::$loggerFactoryBinder->getLoggerFactory();
            case self::NOP_FALLBACK_INITIALIZATION:
                return self::$nopFallbackFactory;
            case self::FAILED_INITIALIZATION:
                throw new IllegalStateException(self::UNSUCCESSFUL_INIT_MESSAGE);
            case self::ONGOING_INITIALIZATION:
                // Support re-entrant behavior.
                return self::$tempFactory;
        }
        throw new IllegalStateException('Unreachable code');
    }
}
?>