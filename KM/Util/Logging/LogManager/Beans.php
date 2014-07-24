<?php

/**
 * Kaaterskil Library
 *
 * PHP version 5.5
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KAATERSKIL MANAGEMENT, LLC BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Kaaterskil
 * @copyright   Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version     SVN $Id$
 */
namespace KM\Util\Logging\LogManager;

use KM\Lang\Clazz;
use KM\Lang\Object;
use KM\Lang\ClassNotFoundException;
use KM\Beans\PropertyChangeEvent;

/**
 * Beans Class
 *
 * @package KM\Util\Logging\LogManager
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Beans extends Object {
	private static $propertyChangeListenerClass;
	private static $propertyChangeEventClass;
	private static $propertyChangeMethod;

	/**
	 * Static constructor
	 */
	public static function clinit() {
		self::$propertyChangeListenerClass = self::getClass( '\KM\Beans\PropertyChangeListener' );
		self::$propertyChangeEventClass = self::getClass( '\KM\Beans\PropertyChangeEvent' );
		self::$propertyChangeMethod = self::getMethod( self::$propertyChangeListenerClass, 'propertyChange' );
	}

	private static function getClass($name) {
		try {
			$name = (string) $name;
			return Clazz::forName($name);
		} catch ( \ReflectionException $re ) {
			return null;
		} catch ( ClassNotFoundException $cnfe ) {
			return null;
		}
	}

	private static function getMethod(Clazz $c, $name) {
		try {
			return ($c == null) ? null : $c->getMethod( $name );
		} catch ( \ReflectionException $e ) {
			throw $e;
		}
	}

	/**
	 * Returns true if KM\Beans is present
	 * @return boolean
	 */
	public static function isBeansPresent() {
		return self::$propertyChangeListenerClass != null && self::$propertyChangeEventClass != null;
	}

	/**
	 * Returns a new PropertyChangeEvent with the given source, property name, old and new values.
	 * @param Object $source
	 * @param string $prop
	 * @param mixed $oldValue
	 * @param mixed $newValue
	 * @throws ReflectionException
	 * @return \KM\Beans\PropertyChangeEvent
	 */
	public static function newPropertyChangeEvent(Object $source, $prop, $oldValue, $newValue) {
		try {
			$clazz = self::$propertyChangeEventClass;
			return $clazz->newInstance( $source, $prop, $oldValue, $newValue );
		} catch ( \ReflectionException $e ) {
			throw $e;
		}
	}

	/**
	 * Invokes the given PropertyChangeListener's propertyChange() method with the given event.
	 * @param Object $listener
	 * @param Object $event
	 * @throws ReflectionException
	 */
	public static function invokePropertyChange(Object $listener, Object $event) {
		try {
			self::$propertyChangeMethod->invoke( $listener, $event );
		} catch ( \ReflectionException $e ) {
			throw $e;
		}
	}
}
?>