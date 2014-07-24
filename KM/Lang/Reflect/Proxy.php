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
namespace KM\Lang\Reflect;

use KM\IO\Serializable;
use KM\Lang\Clazz;
use KM\Lang\IllegalArgumentException;
use KM\Lang\InstantiationException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\Reflect\Constructor;
use KM\Lang\Reflect\Method;
use KM\Lang\Reflect\Proxy\ProxyClassFactory;
use KM\Lang\RuntimeException;
use KM\Util\HashMap;
use KM\Util\Map;

/**
 * Proxy Class
 *
 * @author Blair
 */
class Proxy extends Object implements Serializable
{

    /**
     * A cache of proxy classes.
     *
     * @var Map
     */
    private static $proxyClassCacheInstance;

    /**
     * Returns the proxy cache.
     *
     * @return \KM\Util\Map
     */
    private static function proxyClassCache()
    {
        if (self::$proxyClassCacheInstance === null) {
            self::$proxyClassCacheInstance = new HashMap('array, \KM\Lang\Clazz');
        }
        return self::$proxyClassCacheInstance;
    }

    /**
     * Prohibits instantiation.
     */
    private function __construct()
    {}

    /**
     * Returns the <code>Clazz</code> object for a proxy class given an array of
     * interfaces. The proxy class will implement all of the supplied
     * interfaces.
     *
     * @param array $interfaces
     * @return \KM\Lang\Clazz
     */
    public static function getProxyClass(array $interfaces)
    {
        return self::getProxyClass0($interfaces);
    }

    /**
     * Generate a proxy class.
     *
     * @param array $interfaces An array of interfaces from which a proxy class
     *            will be generated. May not be null.
     * @throws IllegalArgumentException
     * @return \KM\Lang\Clazz The <code>Clazz</code> object representing the
     *         generated proxy class.
     */
    private static function getProxyClass0(array $interfaces)
    {
        /* @var $cl Clazz */
        if (count($interfaces) > 65535) {
            throw new IllegalArgumentException('Interface limit exceeded');
        }
        
        // If the proxy class defined by the given interfaces exists, this will
        // simply return the
        // cached copy. Otherwise, it will create the proxy class via the
        // ProxyClassFactory.
        $cacheKey = self::getKey($interfaces);
        $cl = self::$proxyClassCacheInstance->get($cacheKey);
        if ($cl === null) {
            // Create a new proxy
            $factory = new ProxyClassFactory();
            try {
                $cl = $factory->apply($interfaces);
                assert($cl !== null);
                
                self::$proxyClassCacheInstance->put($cacheKey, $cl);
            } catch (\ReflectionException $re) {
                throw new IllegalArgumentException($re->getMessage(), null, $e);
            }
        }
        return $cl;
    }

    /**
     * Returns a key for the proxy cache from the given array of
     * <code>Clazz</code> objects. The method extracts the classname from each
     * of the given objects, sorts them alphabetically and then computes and
     * returns an MD5 hash of the coalesced names.
     *
     * @param array $interfaces An array of interfaces from which to generate
     *            the proxy class.
     * @return string The MD5 hash of the sorted interface names.
     */
    private static function getKey(array $interfaces)
    {
        /* @var $clazz Clazz */
        $names = [];
        foreach ($interfaces as $clazz) {
            $names[] = $clazz->getName();
        }
        sort($names);
        return md5(implode(';', $names));
    }

    /**
     * Returns an instance of a proxy class for the specified interfaces that
     * dispatches method invocations to the specified invocation handler.
     *
     * @param array $interfaces THe list of interfaces for the proxy class to
     *            implement.
     * @param InvocationHandler $h The invocation handler to dispatch method
     *            invocations to.
     * @return \KM\Lang\Object A proxy instance with the specified invocation
     *         handler of a proxy class and that implements the specified
     *         interfaces.
     * @throws IllegalArgumentException if any of the restrictions on the
     *         parameters that may be passed to getProxyClass() are violated.
     * @throws NullPointerException if the <code>interfaces</code> array
     *         argument or any of its elements are <code>null</code>, or if the
     *         invocation handler <code>h</code> is <code>null</code>.
     */
    public static function newProxyInstance(array $interfaces, InvocationHandler $h)
    {
        /* @var $cl Clazz */
        /* @var $cons Constructor */
        
        // Look up or generate the designated proxy class.
        $cl = self::getProxyClass0($interfaces);
        
        // Invoke its constructor with the given invocation handler.
        try {
            $cons = $cl->getConstructor();
            if (! $cons->isPublic()) {
                $cons->setAccessible(true);
            }
            return $cons->newInstance([
                $h
            ]);
        } catch (InstantiationException $e) {
            trigger_error($e->getMessage());
        } catch (InvocationTargetException $e) {
            $t = $e->getPrevious();
            if ($t instanceof RuntimeException) {
                throw $t;
            } else {
                trigger_error($t->getMessage());
            }
        } catch (NoSuchMethodException $e) {
            trigger_error($e->getMessage());
        }
    }

    public function setMethodHooks(array $methodHooks)
    {
        // Noop
    }

    /**
     * Returns true if and only if the specified class was dynamically generated
     * to be a proxy class using the <code>getProxyClass</code> method or the
     * <code>newProxyInstance</code> method. <p>The reliability of this method
     * is important for the ability to use it to make security decisions, so its
     * implementation should not just test if the class in question extends
     * <code>Proxy</code>.
     *
     * @param Clazz $cl The class to test.
     * @return boolean <code>true</code> if the class is a proxy class and
     *         <code>false</code> otherwise.
     */
    public static function isProxyClass(Clazz $cl)
    {
        return Proxy::clazz()->isAssignableFrom($cl) && self::proxyClassCache()->containsValue($cl);
    }
}
?>