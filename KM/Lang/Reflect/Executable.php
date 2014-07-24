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

use KM\IO\Transient;
use KM\Lang\Object;
use KM\Lang\Clazz;
use KM\Lang\InstantiationException;

/**
 * A shared superclass for the common functionality of <code>Method</code> and
 * <code>Procedure</code>.
 *
 * @author Blair
 */
abstract class Executable extends Object
{

    /**
     * An array of parameters @Transient
     *
     * @var \KM\Lang\Reflect\Parameter[]
     */
    protected $parameters;

    /**
     * The name of this executable.
     *
     * @var string
     */
    protected $name;

    /**
     * Constructor
     */
    protected function __construct()
    {}

    /**
     * Returns the underlying reflector
     *
     * @return \ReflectionFunctionAbstract
     */
    public abstract function getReflector();

    /**
     * Returns the name of the executable represented by this object.
     *
     * @return string The name of the executable represented by this object.
     */
    public function getName()
    {
        if ($this->name === null) {
            $this->name = $this->getReflector()->getName();
        }
        return $this->name;
    }

    /**
     * Returns an array of <code>Parameter</code> objects that represent all the
     * parameters to the underlying executable represented by this object.
     * Returns an array of length 0 if the executable has not parameters.
     *
     * @return \KM\Lang\Reflect\Parameter[] An array of Parameter objects
     *         representing all the parameters to the executable this object
     *         represents.
     */
    public function getParameters()
    {
        return $this->privateGetParameters();
    }

    private function privateGetParameters()
    {
        $tmp = $this->parameters;
        if ($tmp === null) {
            $tmp = $this->getParameters0();
            $this->parameters = $tmp;
        }
        return $tmp;
    }

    protected abstract function getParameters0();

    /**
     * Returns the number of formal parameters (whether explicitly declared or
     * implicitly declared or neither) for the executable represented by this
     * object.
     *
     * @return int The number of formal parameters for the executable this
     *         object represents
     */
    public function getParameterCount()
    {
        return $this->getReflector()->getNumberOfParameters();
    }

    public function getRequiredParameterCount()
    {
        return $this->getReflector()->getNumberOfRequiredParameters();
    }

    /**
     * Returns an array of <code>Type</code> objects that represent the formal
     * parameter types, in declaration order, of the executable represented by
     * this object. Returns an array of length 0 if the underlying executable
     * takes no parameters.
     *
     * @return \KM\Lang\Reflect\Type[] the parameter types for the executable this
     *         object represents.
     */
    public abstract function getParameterTypes();

    /**
     * Returns information about the return type of a Executable. If the return
     * type is a class, the return value is an instance of KM\Lang\Clazz. If it
     * is a scalar type the return value is an instance of
     * KM\Lang\Reflect\Primitive; for mixed and object types the return value is
     * an instance of KM\Lang\Reflect\MixedType. If the Executable has no return
     * value, this method will return null.
     *
     * @return \KM\Lang\Reflect\Type
     */
    public abstract function getReturnType();

    /**
     * Returns the scope associated to the closure.
     *
     * @return \KM\Lang\Clazz
     */
    public function getClosureScopeClass()
    {
        try {
            $rc = $this->getReflector()->getClosureScopeClass();
            return Clazz::forName($rc->getName());
        } catch (\Exception $e) {
            throw new InstantiationException($rc->getName());
        }
    }

    /**
     * Returns a <code>$this</code> pointer bound to closure.
     *
     * @return object Returns <code>$this</code> pointer or null in case of
     *         error.
     */
    public function getClosureThis()
    {
        $pointer = $this->getReflector()->getClosureThis();
        if ($pointer === null) {
            return null;
        }
        return $pointer;
    }

    /**
     * Returns a Doc Comment for this Executable, or null is none exists.
     *
     * @return string The Doc Comment for this Executable, or null if none
     *         exists.
     */
    public function getDocComment()
    {
        return $this->getReflector()->getDocComment();
    }

    /**
     * Returns the ending line number of this Executable.
     *
     * @return int The ending line number.
     */
    public function getEndLine()
    {
        return $this->getReflector()->getEndLine();
    }

    /**
     * Returns the extension information of this Executable.
     *
     * @return \KM\Lang\Reflect\Extension
     */
    public function getExtension()
    {
        return $this->getReflector()->getExtension();
    }

    /**
     * Returns the extension name.
     *
     * @return string The extension name.
     */
    public function getExtensionName()
    {
        return $this->getReflector()->getExtensionName();
    }

    /**
     * Returns the filename from a user-defined Executable.
     *
     * @return string The filename of this Executable, if it is user-defined.
     */
    public function getFileName()
    {
        return $this->getReflector()->getFileName();
    }

    /**
     * Returns the name of the namespace where this class is defined.
     *
     * @return string The namespace name where this class is defined.
     */
    public function getNamespaceName()
    {
        return $this->getReflector()->getNamespaceName();
    }

    /**
     * Returns the short name of this Executable (without the namespace).
     *
     * @return string The short name of this Executable.
     */
    public function getShortName()
    {
        return $this->getReflector()->getShortName();
    }

    /**
     * Returns the starting line number of this Executable.
     *
     * @return int The starting line number of this Executable.
     */
    public function getStartLine()
    {
        return $this->getReflector()->getStartLine();
    }

    /**
     * Returns the static variables.
     *
     * @return array An array of any static variables defined in this
     *         Executable.
     */
    public function getStaticVariables()
    {
        return $this->getReflector()->getStaticVariables();
    }

    /**
     * Checks is this Executable is defined in a namespace.
     *
     * @return boolean True if this Executable is defined in a namespace, false
     *         otherwise.
     */
    public function inNamespace()
    {
        return $this->getReflector()->inNamespace();
    }

    /**
     * Checks if this Executable is a closure.
     *
     * @return boolean True if this Executable is a closure, false otherwise.
     */
    public function isClosure()
    {
        return $this->getReflector()->isClosure();
    }

    /**
     * Returns true if this Executable is deprecated, false otherwise.
     *
     * @return boolean True if this Executable is deprecated, false otherwise.
     */
    public function isDeprecated()
    {
        return $this->getReflector()->isDeprecated();
    }

    /**
     * Returns true if this Executable is a generator, false otherwise.
     *
     * @return boolean True if this Executable is a generator, false otherwise.
     */
    public function isGenerator()
    {
        return $this->getReflector()->isGenerator();
    }

    /**
     * Returns true if this Executable is internal, as opposed to user-defined.
     *
     * @return boolean True if this Executable is internal, false if
     *         use-defined.
     */
    public function isInternal()
    {
        return $this->getReflector()->isInternal();
    }

    /**
     * Returns true if this Executable is user defined, as opposed to internal.
     *
     * @return boolean True if this Executable is user-defined, false if it is
     *         internal.
     */
    public function isUserDefined()
    {
        return $this->getReflector()->isUserDefined();
    }

    /**
     * Return true if this Executable returns a reference.
     *
     * @return boolean True if this Executable returns a reference.
     */
    public function returnsReference()
    {
        return $this->getReflector()->returnsReference();
    }
}
?>