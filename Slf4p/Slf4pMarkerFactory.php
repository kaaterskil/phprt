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

use KM\Lang\ClassNotFoundException;
use KM\Lang\Object;
use Slf4p\Helpers\BasicMarkerFactory;
use Slf4p\Marker;
use Slf4p\Spi\MarkerFactoryBinder;

/**
 * GenericMarkerFactory is a utility class producing Marker instance as
 * appropriate for the logging system currently in use. This class is
 * essentially implemented as a wrapper around a MarkerFactory instance bound at
 * runtime using a namespace provided by a 'slf4p.properties' file specifying
 * the 'slf4p.impl.staticLoggerBinderNS' namespace located in this package. All
 * methods in this class are static.
 *
 * @author Blair
 */
class Slf4pMarkerFactory extends Object
{

    /**
     * The singleton MarkerFactory.
     *
     * @var MarkerFactory
     */
    protected static $markerFactory;

    /**
     * Static constructor called by class loader.
     */
    public static function clinit()
    {
        /* @var $binder MarkerFactoryBinder */
        try {
            $binder = self::bind();
            self::$markerFactory = $binder->getMarkerFactory();
        } catch (ClassNotFoundException $cnfe) {
            self::$markerFactory = new BasicMarkerFactory();
        } catch (\Exception $e) {
            // We should never get here.
            trigger_error('Unexpected failure while binding MarkerFactory');
        }
    }

    private final static function bind()
    {
        $ns = self::findStaticMarkerBinderNamespace();
        if (! empty($ns)) {
            try {
                $cname = $ns . '\StaticMarkerBinder';
                return $cname::getSingleton();
            } catch (\ReflectionException $e) {
                throw new ClassNotFoundException();
            }
        } else {
            throw new ClassNotFoundException();
        }
    }

    /**
     * Returns the namespace of the static logger binder.
     *
     * @throws IllegalStateException
     * @return string The FQCN of the static logger binder
     */
    private static function findStaticMarkerBinderNamespace()
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
     * Return a marker instance as specified by the name parameter using the
     * previously bound MarkerFactory instance.
     *
     * @param string $name The name of the Marker object to return.
     * @return \Slf4p\Marker
     */
    public static function getMarker($name)
    {
        return self::$markerFactory->getMarker($name);
    }

    /**
     * Create and return a Marker which is detached (even at birth) from the
     * MarkerFactory.
     *
     * @param string $name The name of the marker.
     * @return \Slf4p\Marker A dangling marker.
     */
    public static function getDetachedMarker($name)
    {
        return self::$markerFactory->getDetachedMarker($name);
    }

    /**
     * Prevent instantiation.
     */
    private function __construct()
    {}
}

// Instantiate static members.
GenericMarkerFactory::construct();
?>