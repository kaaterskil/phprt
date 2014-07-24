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
namespace KM\Beans;

use KM\Lang\Clazz;
use KM\Lang\IllegalArgumentException;
use KM\Lang\NoSuchMethodException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\Reflect\AccessibleObject;
use KM\Lang\Reflect\Method;

/**
 * A <code>Statement</code> object represents a primitive statement in which a single method is
 * applied to a target and a set of arguments - as in <code>"a.setFoo(b)"</code>.
 * Note that where this example uses names to denote the target and its argument, a statement object
 * does not require a name space and is constructed with the values themselves. The statement object
 * associates the named method with its environment as a simple set of values: the target and an
 * array of argument values.
 *
 * @package KM\Beans
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Statement extends Object {
	private static $emptyArray = [];
	private static $defaultExceptionListener;

	public static function getExceptionListener() {
		if (self::$defaultExceptionListener == null) {
			self::$defaultExceptionListener = new DefaultExceptionListener();
		}
		return self::$defaultExceptionListener;
	}
	
	/**
	 * The target object of this statement.
	 * @var Object
	 */
	private $target;
	
	/**
	 * The name of the method to invoke.
	 * @var string
	 */
	private $methodName;
	
	/**
	 * The arguments for the method to invoke.
	 * @var array
	 */
	private $arguments = [];

	/**
	 * Creates a Statement object for the specified target to invoke the method specified by the
	 * name and by the array of arguments.
	 * The <code>target</code> and the <code>methodName</code> values should not be
	 * <code>null</code>. Otherwise, an attempt to execute this <code>Expression</code> will result
	 * in a <code>NullPointerException</code>. If the <code>arguments</code> value is null, an empty
	 * array is used as the value of the <code>arguments</code> property.
	 * @param mixed $target The target object of this statement.
	 * @param string $methodName The name of the method to invoke on the specified target.
	 * @param array $arguments The array of arguments to invoke the specified method.
	 */
	public function __construct($target, $methodName, array $arguments = null) {
		$this->target = $target;
		$this->methodName = (string) $methodName;
		$this->arguments = ($arguments == null) ? self::$emptyArray : $arguments;
	}

	/**
	 * Returns the target object of this Statement.
	 * If this method returns <code>null</code>, the <code>execute</code> method throws a
	 * <code>NullPointerException</code>.
	 * @return mixed
	 */
	public function getTarget() {
		return $this->target;
	}

	/**
	 * Return the name of the method to invoke.
	 * If this method returns <code>null</code>, the <code>execute</code> method throws a
	 * <code>NullPointerException</code>.
	 * @return string
	 */
	public function getMethodName() {
		return $this->methodName;
	}

	/**
	 * Returns the arguments for the method to invoke.
	 * The number of arguments and their types must match the method being called. <code>Null</code>
	 * can be used as a synonym for an empty array.
	 * @return array
	 */
	public function getArguments() {
		$returnValue = [];
		foreach ( $this->arguments as $arg ) {
			$returnValue[] = $arg;
		}
		return $returnValue;
	}

	/**
	 * The <code>execute</code> method finds a method whose name is the same as the
	 * <code>methodName</code> property, and invokes the method on the target.
	 * <p>
	 * The following method types are handled as special cases:
	 * <ul>
	 * <li> Static methods may be called by using a class object as the target.
	 * <li> The reserved method name "new" may be used to call a class's constructor as if all
	 * classes defined static "new" methods. Constructor invocations are typically considered
	 * <code>Expression</code>s rather than <code>Statement</code>s as they return a value.
	 * <li> The method names "get" and "set" defined in the <code>km.util.List</code> interface
	 * may also be applied to array instances, mapping to the static methods of the same name in the
	 * <code>Array</code> class.
	 * </ul>
	 * @throws NullPointerException if the value of the <code>target</code> or
	 *         <code>methodName</code> property is <code>null</code>
	 * @throws NoSuchMethodException if a matching method is not found
	 * @throws Exception that is thrown by the invoked method
	 */
	public function execute() {
		$this->invoke();
	}

	public function invoke() {
		return $this->invokeInternal();
	}

	private function invokeInternal() {
		/* @var $m Method */
		$target = $this->getTarget();
		$methodName = $this->getMethodName();
		if ($target == null || $methodName == null) {
			throw new NullPointerException(
				($target == null ? 'target' : 'methodName') . ' should not be null' );
		}
		
		$arguments = $this->getArguments();
		if ($arguments == null) {
			$arguments = self::$emptyArray;
		}
		
		$argClasses = (($size = count( $arguments )) > 0) ? array_fill( 0, $size, null ) : [];
		for($i = 0; $i < $size; $i++) {
			$argClasses[$i] = ($arguments[$i] == null) ? null : $arguments[$i]->getClass();
		}
		
		$m = null;
		if ($target instanceof Clazz) {
			// For class methods, simulate the effect of a meta class by taking the union of the
			// static methods of the actual class, with the instance methods of "Class.class" and
			// the overloaded "newInstance" methods defined by the constructors. This way
			// "System.class", for example, will perform both the static method getProperties() and
			// the instance method getSuperclass() defined in "Class.class".
			if ($methodName == 'new') {
				$methodName = 'newInstance';
			}
			
			if (is_array( $target )) {
				$result = (($targetSize = count( $arguments )) == 0) ? [] : array_fill( 0, $targetSize, null );
				for($i = 0; $i < $targetSize; $i++) {
					$result[$i] = $arguments[$i];
				}
				return $result;
			}
			if ($methodName == 'newInstance' && count( $arguments ) != 0) {
				$m = $target->getConstructor();
			}
			
			if ($m == null && $target->getName() != 'KM\Lang\Clazz') {
				$m = self::getMethod( $target, $methodName, $argClasses );
			}
			if ($m == null) {
				$m = self::getMethod( Clazz::forName( '\KM\Lang\Clazz' ), $methodName, $argClasses );
			}
		} else {
			$m = self::getMethod( $target, $methodName, $argClasses );
		}
		if ($m != null) {
			try {
				$m->setAccessible( true );
				if ($methodName != '__construct') {
					return $m->invokeArgs( $target, $arguments );
				} else {
					return $m->getDeclaringClass()->newInstanceArgs( $arguments );
				}
			} catch ( \ReflectionException $e ) {
				$te = $e->getPrevious();
				if($te instanceof \Exception) {
					throw $te;
				} else {
					throw $e;
				}
			}
		}
		throw new NoSuchMethodException($this->__toString());
	}

	public function instanceName($instance = null) {
		if ($instance == null) {
			return 'null';
		} elseif (is_scalar( $instance )) {
			return '"' . $instance . '"';
		} elseif (is_object( $instance )) {
			return get_class( $instance );
		}
		throw new IllegalArgumentException();
	}

	/**
	 * Prints the value of this statement.
	 * @return string
	 * @see \KM\Lang\Object::__toString()
	 */
	public function __toString() {
		// Respect a subclass's implementation here.
		$target = $this->getTarget();
		$methodName = $this->getMethodName();
		$arguments = $this->getArguments();
		if($arguments == null) {
			$arguments = self::$emptyArray;
		}
		$sb = $this->instanceName($target) . '.' . $methodName . '(';
		$n = count($arguments);
		for($i = 0; $i < $n; $i++) {
			$sb .= $this->instanceName($arguments[$i]);
			if($i != $n - 1) {
				$sb .= ', ';
			}
		}
		$sb .= ');';
		return $sb;
	}

	/**
	 * Returns a method object for the specified name or null is one could not be found.
	 * @param Clazz $type
	 * @param string $name
	 * @param array $args
	 * @return Method
	 */
	public static function getMethod(Clazz $type, $name, array $args = null) {
		try {
			return $type->getMethod( $name );
		} catch ( \ReflectionException $e ) {
			return null;
		}
	}
}
?>