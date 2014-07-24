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

use KM\Lang\Object;

/**
 * An <code>Expression</code> object represents a primitive expression in which a single method is
 * applied to a target and a set of arguments to return a result - as in <code>"a.getFoo()"</code>.
 * <p>
 * In addition to the properties of the super class, the <code>Expression</code> object provides a
 * <em>value</em> which is the object returned when this expression is evaluated. The return value
 * is typically not provided by the caller and is instead computed by dynamically finding the method
 * and invoking it when the first call to <code>getValue</code> is made.
 *
 * @package KM\Beans
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Expression extends Statement {
	private static $unbound;

	private static function getUnbound() {
		if (self::$unbound === null) {
			self::$unbound = new Object();
		}
		return self::$unbound;
	}
	private $value;

	/**
	 * Creates a new <code>Expression</code> object with the specified value for the specified
	 * target object to invoke the method specified by the name and by the array of arguments.
	 * The <code>value</code> value is used as the value of the <code>value</code> property, so the
	 * <code>#getValue</code> method will return it without executing this <code>Expression</code>.
	 * <p>
	 * The <code>target</code> and the <code>methodName</code> values should not be
	 * <code>null</code>. Otherwise an attempt to execute this <code>Expression</code> will result
	 * in a <code>NullPointerException</code>. If the <code>arguments</code> value is
	 * <code>null</code>, an empty array is used as the value of the <code>arguments</code>
	 * property.
	 * @param mixed $target The target object of this expression.
	 * @param string $methodName The name of the method to invoke on the specified target.
	 * @param array $arguments The array of arguments to invoke the specified method.
	 * @param mixed $value The value of this expression.
	 */
	public function __construct($target, string $methodName, array $arguments = null, $value = null) {
		parent::__construct( $target, $methodName, $arguments );
		if ($value == null) {
			$this->value = self::$unbound;
		} else {
			$this->setValue( $value );
		}
	}

	/**
	 * If the invoked method completes normally,
	 * the value it returns is copied in the <code>value</code> property.
	 * Note that the <code>value</code> property is set to <code>null</code>, if the return type of
	 * the underlying method is <code>void</code>.
	 * @see \KM\Beans\Statement::execute()
	 */
	public function execute() {
		$this->setValue( $this->invoke() );
	}

	/**
	 * If the value property of this instance is not already set, this method dynamically finds the
	 * method with the specified methodName on this target with these arguments and calls it.
	 * The result of the method invocation is first copied into the value property of this
	 * expression and then returned as the result of <code>getValue</code>. If the value property
	 * was already set, either by a call to <code>setValue</code> or a previous call to
	 * <code>getValue</code> then the value property is returned without either looking up or
	 * calling the method.
	 * <p>
	 * The value property of an <code>Expression</code> is set to a unique private
	 * (non-<code>null</code>) value by default and this value is used as an internal indication
	 * that the method has not yet been called. A return value of <code>null</code> replaces this
	 * default value in the same way that any other value would, ensuring that expressions are never
	 * evaluated more than once.
	 * <p>
	 * See the <code>execute</code> method for details on how methods are chosen using the dynamic
	 * types of the target and arguments.
	 * @return mixed The result of applying this method to these arguments.
	 * @throws Exception if the method with the specified methodName throws an exception
	 */
	public function getValue() {
		if ($this->value == self::$unbound) {
			$this->setValue( $this->invoke() );
		}
		return $this->value;
	}

	/**
	 * Sets the value of this expression to <code>value</code>.
	 * This value will be returned by the getValue method without calling the method associated with
	 * this expression.
	 * @param mixed $value The value of this expression.
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	public function instanceName($instance = null) {
		return $instance == self::$unbound ? '<unbound>' : parent::instanceName( $instance );
	}

	/**
	 * Prints the value of this expression.
	 * @return string
	 * @see \KM\Beans\Statement::__toString()
	 */
	public function __toString() {
		return $this->instanceName( $this->value ) . '=' . parent::__toString();
	}
}
?>