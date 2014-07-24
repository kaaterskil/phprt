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
namespace Sun\Misc;

use KM\Lang\Clazz;
use KM\Lang\Reflect\Method;
use KM\Lang\Reflect\Parameter;
use KM\Util\HashMap;
use KM\Util\Map;
use KM\Util\ArrayList;
use Sun\Misc\ProxyGenerator\ProxyMethod;
use Sun\Misc\ProxyGenerator\ClassTemplate;

/**
 * ProxyGenerator Class
 *
 * @package Sun\Misc
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ProxyGenerator {
	/**
	 * Debugging flag for saving generated class files.
	 * @var boolean
	 */
	private static $saveGeneratedClassFile = true;
	
	/**
	 * Name of the superclass of proxy classes.
	 * @var string
	 */
	private static $superclassName = '\KM\Lang\Reflect\Proxy';
	
	/**
	 * Name of field for storing a proxy instance's invocation handler.
	 * @var string
	 */
	private static $handlerFieldName = 'h';
	
	/**
	 * Pre-loaded hashCode() method.
	 * @var Method
	 */
	private static $hashCodeMethod;
	
	/**
	 * Pre-loaded equals() method.
	 * @var Method
	 */
	private static $equalsMethod;
	
	/**
	 * Pre-loaded __toString() method.
	 * @var Method
	 */
	private static $toStringMethod;

	/**
	 * Static constructor
	 */
	public static function clinit() {
		$objectClazz = Clazz::forName( '\KM\Lang\Object' );
		self::$hashCodeMethod = $objectClazz->getMethod( 'hashCode' );
		self::$equalsMethod = $objectClazz->getMethod( 'equals' );
		self::$toStringMethod = $objectClazz->getMethod( '__toString' );
	}

	/**
	 * Generate a proxy class given a name and a list of proxy interfaces,
	 * @param string $name The fully qualified class name of the proxy class to generate.
	 * @param array $interfaces The array of interfaces to implement.
	 * @return string A byte array of the generated proxy class,
	 */
	public static function generateProxyClass($name, array $interfaces) {
		$gen = new self( $name, $interfaces );
		$classFile = $gen->generateClassFile();
		
		if (self::$saveGeneratedClassFile) {
			file_put_contents( $name . '.php', "<?php\n" . $classFile . "\n?>" );
		}
		
		return $classFile;
	}
	
	/**
	 * Name of the proxy class.
	 * @var string
	 */
	private $className;
	
	/**
	 * Proxy interfaces
	 * @var Clazz[]
	 */
	private $interfaces;
	
	/**
	 * Maps method signature string to list of ProxyMethod objects for proxy methods with that
	 * signature.
	 * @var Map
	 */
	private $proxyMethods;
	
	private $methods = [];

	/**
	 * Constructs a proxy generator to generate a proxy class with the specified name and for the
	 * given interfaces.
	 * A ProxyGeneratro object contains the state for the ongoing generation of a particular proxy
	 * class,
	 * @param string $className The fully qualified class name of the proxy to generate.
	 * @param Clazz[] $interfaces An array of <code>Clazz</code> objects representing the interfaces
	 *        the generated proxy must implement.
	 */
	private function __construct($className, array $interfaces) {
		$this->proxyMethods = new HashMap( 'string, Sun\Misc\ProxyGenerator\ProxyMethod' );
		$this->className = (string) $className;
		$this->interfaces = $interfaces;
	}

	/**
	 * Generate a class file for the proxy class, This method drives the class file generation
	 * process.
	 */
	private function generateClassFile() {
		/* @var $interface Clazz */
		/* @var $pm ProxyMethod */
		
		// Record that proxy methods are needed for the hashcode(), equals() and __toString()
		// methods of KM\Lang\Object. This is done before the methods from the proxy interfaces so
		// that the methods from KM\Lang\object take precedence over duplicate methods in the proxy
		// interfaces.
		$objectClazz = Clazz::forName( '\KM\Lang\Object' );
		$this->addProxyMethod( self::$equalsMethod, $objectClazz );
		$this->addProxyMethod( self::$hashCodeMethod, $objectClazz );
		$this->addProxyMethod( self::$toStringMethod, $objectClazz );
		
		// Now record all of the methods from the proxy interfaces, giving earlier interfaces
		// precedence over later ones with duplicate methods.
		for($i = 0; $i < count( $this->interfaces ); $i++) {
			$interface = $this->interfaces[$i];
			$methods = $interface->getMethods();
			for($j = 0; $j < count( $methods ); $j++) {
				$this->addProxyMethod( $methods[$j], $interface );
			}
		}
		
		// Assemble methods for the class we are generating.
		foreach ($this->proxyMethods->values() as $pm) {
			$this->methods[] = $pm->method;
		}
		
		$classTemplate = new ClassTemplate(self::$superclassName);
		return $classTemplate->render($this->className, $this->interfaces, $this->methods);
	}

	/**
	 * Adds a method to be proxied by creating a new ProxyMethod object.
	 * If the method name already exists in the map, the given method is ignored (assumes no
	 * overloading). "fromClass" indicates the proxy interface that the method was found through,
	 * which may be different from (a sub-interface of) the method's declaring class. The Method
	 * object passed for a given name identifies the Method object (and thus the declaring class)
	 * that will be passed to the invocation handler's "invoke" method.
	 * @param Method $m The method to invoke.
	 * @param Clazz $fromClazz The proxy interface that declared the method.
	 */
	private function addProxyMethod(Method $m, Clazz $fromClazz) {
		/* @var $p Parameter */
		$name = $m->getName();
		$sigMethod = $this->proxyMethods->get( $name );
		if ($sigMethod == null) {
			$this->proxyMethods->put( $name, new ProxyMethod( $m, $fromClazz ) );
		}
	}
}
?>