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
namespace KM\Lang\Reflect\Proxy;

use KM\Lang\Clazz;
use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\Util\HashMap;
use Sun\Misc\ProxyGenerator;

/**
 * ProxyClassFactory Class
 *
 * @author Blair
 */
class ProxyClassFactory extends Object {
	const PROXY_PACKAGE = '\KM\Proxy';
	
	/**
	 * Prefix for all proxy class names.
	 * @var string
	 */
	private static $proxyClassNamePrefix = 'Proxy_';
	
	/**
	 * The next number to use for generation of unique proxy class names.
	 * @var int
	 */
	private static $nextUniqueNumber = 0;

	/**
	 * Applies this function to the given argument.
	 * @param array $interfaces The array of interfaces with which to create the proxy.
	 * @throws IllegalArgumentException
	 * @return \KM\Lang\Clazz The <code>Clazz</code> object representing the proxy class.
	 */
	public function apply(array $interfaces) {
		/* @var $intf Clazz */
		/* @var $cl Clazz */
		
		// Validate the interfaces with which to create the proxy class.
		$interfaceSet = array();
		foreach ( $interfaces as $intf ) {
			// Verify that the class loader resolves the name of this interface to the same Clazz
			// object.
			$interfaceClazz = null;
			try {
				$interfaceClazz = Clazz::forName( $intf->getName() );
			} catch ( Exception $e ) {
				// Drop through.
			}
			if ($interfaceClazz !== $intf) {
				throw new IllegalArgumentException( $intf->getName() . ' is not visible from the class loader' );
			}
			
			// Verify that the Clazz object is an interface.
			if (!$interfaceClazz->isInterface()) {
				throw new IllegalArgumentException( $interfaceClazz->getName() . ' is not an interface' );
			}
			
			// Verify that this interface is not a duplicate.
			if (in_array( $interfaceClazz->getName(), $interfaceSet )) {
				throw new IllegalArgumentException( 'repeated interface: ' . $interfaceClazz->getName() );
			} else {
				$interfaceSet[] = $interfaceClazz->getName();
			}
		}
		
		// Choose a name for the proxy class to generate.
		$proxyPkg = self::PROXY_PACKAGE . '\\';
		$num = self::$nextUniqueNumber++;
		$proxyName = $proxyPkg . self::$proxyClassNamePrefix . $num;
		
		// Generate the specified proxy class
		$proxyClassFile = ProxyGenerator::generateProxyClass( $proxyName, $interfaces );
		try {
			include $proxyClassFile;
			return Clazz::forName( $proxyName );
		} catch ( \ReflectionException $re ) {
			throw new IllegalArgumentException( $re->getMessage(), null, $re );
		}
	}
}
?>