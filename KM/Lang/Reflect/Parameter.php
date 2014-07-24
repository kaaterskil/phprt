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
use KM\Lang\Clazz;
use KM\Lang\Object;
use KM\Lang\InstantiationException;
use KM\Lang\IllegalArgumentException;

/**
 * Information about method parameters. A <code>Parameter</code> provides
 * information about method parameters, including its name. It also provides an
 * alternate means of obtaining attributes for the parameter.
 *
 * @author Blair
 */
final class Parameter extends Object
{

    /**
     * The parameter name
     *
     * @var string
     */
    private $name;

    /**
     * The declaring executable.
     *
     * @var Executable
     */
    private $executable;

    /**
     * The index order of this parameter
     *
     * @var int
     */
    private $index;

    /**
     * The underlying reflector.
     *
     * @var \ReflectionParameter
     */
    private $root;

    /**
     * The parameter type @Transient
     *
     * @var Type
     */
    private $parameterType;

    /**
     * Constructs a new Parameter with the given parameter name, index declaring
     * executable and optional declaring class.
     *
     * @param string $name The parameter name.
     * @param int $index The parameter index
     * @param Executable $executable The executable which declares the parameter
     * @param string $declaringClassName The name of the class if the executable
     *            is a member method.
     * @throws InstantiationException if the reflector could not be instantiated
     *         for any reason.
     */
    public function __construct($name, $index, Executable $executable, $declaringClassName = null)
    {
        $this->name = (string) $name;
        $this->executable = $executable;
        $this->index = (int) $index;
        try {
            if (! empty($declaringClassName)) {
                $arg = [
                    $declaringClassName,
                    $executable->getName()
                ];
            } else {
                $arg = $executable->getName();
            }
            $this->root = new \ReflectionParameter($arg, $name);
        } catch (\ReflectionException $e) {
            throw new InstantiationException($name . ': ' . $e);
        }
    }

    /**
     * Compares based on the executable and the index.
     *
     * @param Object $obj The object to compare.
     * @return boolean Whether or not this is equal to the argument.
     * @see \KM\Lang\Object::equals()
     */
    public function equals(Object $obj = null)
    {
        /* @var $other Parameter */
        if ($obj != null && $obj instanceof Parameter) {
            $other = $obj;
            return ($other->executable->equals($this->executable)) && $other->index == $this->index;
        }
        return false;
    }

    /**
     * Returns true if the parameter has a name, returns false otherwise.
     *
     * @return boolean True if the parameter has a name.
     */
    public function isNamePresent()
    {
        return ! empty($this->name);
    }

    /**
     * Returns a string describing this parameter. THe format is the fully
     * qualified type of the parameter, followed by a space, followed by the
     * name of the parameter.
     *
     * @return string A string representation of the parameter.
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        $sb = '';
        $typeName = $this->getType()->getTypeName();
        
        $sb .= $typeName;
        $sb .= ' ';
        $sb .= $this->getName();
        return $sb;
    }

    /**
     * Returns the <code>Executable</code> which declares this parameter.
     *
     * @return \KM\Lang\Reflect\Executable The <code>Executable</code> declaring
     *         this parameter.
     */
    public function getDeclaringExecutable()
    {
        return $this->executable;
    }

    /**
     * Returns the name of the parameter.
     *
     * @return string The name of the parameter.
     */
    public function getName()
    {
        if (empty($this->name)) {
            return 'arg' . $this->index;
        }
        return $this->name;
    }

    /**
     * Returns the real name of the parameter.
     *
     * @return string The name of the parameter.
     */
    public function getRealName()
    {
        return $this->name;
    }

    /**
     * Returns a <code>Type</code> object that identifies the declared type for
     * the parameter represented by this <code>Parameter</code> object. The
     * method first interrogates the parameter for any type hinting and returns
     * a <code>Clazz</code> object representing the hint. If none is found, the
     * method parses the executable's doc comment. If a <code>param<code>
     * annotation is found matching the parameter's index in the executable, the
     * method will attempt to return a <code>Type</code> object representing the
     * parameter type.
     *
     * @return \KM\Lang\Reflect\Type A type object identifying the declared type
     *         of the parameter represented by this object.
     */
    public function getType()
    {
        $tmp = $this->parameterType;
        if ($tmp === null) {
            try {
                $cl = $this->root->getClass();
                if ($cl != null) {
                    $tmp = Clazz::forName($cl->getName());
                }
                if ($tmp === null) {
                    $tmp = $this->getType0();
                }
                $this->parameterType = ($tmp === null) ? null : $tmp;
            } catch (\Exception $e) {
                // Fail silently
                $tmp = null;
            }
        }
        return $tmp;
    }

    /**
     * Returns the type object that represent this parameter type from the doc
     * comment.
     *
     * @throws InstantiationException
     * @return \KM\Lang\Reflect\Type
     */
    private function getType0()
    {
        $typeName = $this->parseParamType();
        if (empty($typeName)) {
            $format = 'Cannot parse type from doc comment for {%s} param {%s}';
            throw new InstantiationException(
                sprintf($format, $this->getDeclaringExecutable()->getName(), $this->getName()));
        }
        return ReflectionUtility::typeFor($typeName);
    }

    /**
     * Parses the raw parameter type from the doc comment.
     *
     * @return string THe raw parameter type.
     */
    private function parseParamType()
    {
        $doc = $this->getDeclaringExecutable()->getDocComment();
        if (empty($doc)) {
            return null;
        }
        $pos = 0;
        foreach (explode("\n", $doc) as $line) {
            $part = strstr($line, '@param');
            if ($part === false) {
                continue;
            }
            if ($pos != $this->index) {
                continue;
            }
            $parts = explode(' ', trim(str_replace('@param', '', $part)));
            return trim($parts[0]);
        }
        return null;
    }
    
    /* ---------- PHP Reflection Methods ---------- */
    
    /**
     * Checks if a null value is allowed.
     *
     * @return boolean <code>True</code> if null is allowed, <code>false</code>
     *         otherwise.
     */
    public function allowsNull()
    {
        return $this->root->allowsNull();
    }

    /**
     * Gets the default value of the parameter for a user-defined function or
     * method. If the parameter is not optional, an IllegalArgumentException is
     * thrown.
     *
     * @return mixed The parameter's default value.
     */
    public function getDefaultValue()
    {
        try {
            return $this->root->getDefaultValue();
        } catch (\ReflectionException $e) {
            throw new IllegalArgumentException();
        }
    }

    /**
     * Returns the position of the parameter.
     *
     * @return int The position of the parameter.
     */
    public function getPosition()
    {
        return $this->index;
    }

    /**
     * Checks if the parameter expects an array.
     *
     * @return boolean <code>True</code> is an array is expected,
     *         <code>false</code> otherwise.
     */
    public function isArray()
    {
        return $this->root->isArray();
    }

    /**
     * Returns whether the parameter must be callable.
     *
     * @return boolean <code>True</code> if the parameter is callable,
     *         <code>false</code> if it is not, or <code>null</code> on failure.
     */
    public function isCallable()
    {
        return $this->root->isCallable();
    }

    /**
     * Checks if a default value is available.
     *
     * @return boolean <code>True</code> if a default value is available,
     *         <code>false</code> otherwise.
     */
    public function isDefaultValueavailable()
    {
        return $this->root->isDefaultValueAvailable();
    }

    /**
     * Returns whether the default value of this parameter is constant.
     *
     * @return boolean <code>True</code> if the default value is constant,
     *         <code>false</code> if it is not, or <code>null</code> on failure.
     */
    public function isDefaultValueConstant()
    {
        return $this->root->isDefaultValueConstant();
    }

    /**
     * Checks if optional.
     *
     * @return boolean <code>True</code> if the parameter is optional,
     *         <code>false</code> otherwise.
     */
    public function isOptional()
    {
        return $this->root->isOptional();
    }

    /**
     * Checks if passed by reference.
     *
     * @return boolean <code>True</code> if the parameter is passed by
     *         reference, <code>false</code> otherwise.
     */
    public function isPassedByReference()
    {
        return $this->root->isPassedByReference();
    }

    /**
     * Returns whether this parameter can be passed by value.
     *
     * @return boolean <code>True</code> if this parameter can be passed by
     *         value, <code>false</code> otherwise.
     */
    public function canBePassedByValue()
    {
        return $this->root->canBePassedByValue();
    }
}
?>