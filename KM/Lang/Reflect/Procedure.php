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

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use KM\Lang\Clazz;
use KM\Lang\IllegalStateException;
use KM\Lang\InstantiationException;
use KM\Lang\NullPointerException;
use KM\Lang\Reflect\Type;
use KM\Lang\Object;

/**
 * Procedure Class
 *
 * @author blair_000
 */
class Procedure extends Executable
{

    /**
     * The annotation reader.
     *
     * @var AnnotationReader
     */
    private $reader;

    /**
     * The underlying reflection method
     *
     * @var \ReflectionFunction
     */
    private $root;

    /**
     * The return type
     *
     * @var Type
     */
    private $returnType;

    /**
     * An array of parameter types.
     *
     * @var \KM\Lang\Reflect\Type[]
     */
    private $parameterTypes;

    /**
     * An array of exception types.
     *
     * @var \KM\Lang\Clazz[]
     */
    private $exceptionTypes;

    public function __construct($name)
    {
        parent::__construct();
        $name = (string) $name;
        $this->name = $name;
        try {
            $this->root = new \ReflectionFunction($name);
        } catch (\ReflectionException $e) {
            throw new InstantiationException($name);
        }
        $this->returnType = $this->getReturnType();
        $this->parameters = $this->getParameters0();
        $this->parameterTypes = $this->getParameterTypes0();
        $this->exceptionTypes = $this->getExceptionTypes0();
    }

    /**
     * Returns the underlying reflector.
     *
     * @return \ReflectionFunction
     * @see \KM\Lang\Reflect\Executable::getReflector()
     */
    public function getReflector()
    {
        return $this->root;
    }

    /**
     * Returns a dynamically created closure for the function represented by
     * this <code>Procedure</code>.
     *
     * @return Closure A <code>Closure</code> object representing this function
     *         expressed as a closure.
     * @throws InstantiationException if an error occurred in the creation of
     *         the closure.
     */
    public function getClosure()
    {
        $clo = $this->root->getClosure();
        if ($clo === null) {
            throw new InstantiationException($this->name);
        }
        return $clo;
    }

    /**
     * Checks if the function represented by this <code>Procedure</code> object
     * is disabled by the <code>disable_function</code> directive.
     *
     * @return boolean <code>True</code> if the <code>Procedure</code> has been
     *         disabled; <code>false</code> otherwise.
     */
    public function isDisabled()
    {
        return $this->root->isDisabled();
    }

    /**
     * /** Returns a <code>Type</code> object that represents the formal return
     * type of the method represented by this <code>Method</code> object.
     *
     * @throws NullPointerException if the type name is not defined in the
     *         method doc comments.
     * @throws InstantiationException if the return type could not be created
     *         for any reason.
     * @return \KM\Lang\Reflect\Type The return type for the method this object
     *         represents.
     * @see \KM\Lang\Reflect\Executable::getReturnType()
     */
    public function getReturnType()
    {
        if ($this->returnType === null) {
            $this->returnType = $this->getReturnType0();
        }
        return $this->returnType;
    }

    /**
     * Returns a formatted string of the return type name.
     *
     * @return string
     */
    public function getFormattedReturnType()
    {
        /* @var $type Type */
        /* @var $cl Clazz */
        $type = $this->getReturnType();
        if ($type instanceof Clazz) {
            $cl = $type;
            return $cl->getShortName();
        }
        return $type->getTypeName();
    }

    /**
     * Returns a <code>Type</code> object that represents the formal return type
     * of the method represented by this <code>Method</code> object. The type is
     * computed by introspection of the <code>return</code> annotation on the
     * method declaration. If <code>return</code> is not defined or is null, a
     * null value will be returned. Otherwise, the method will attempt to return
     * a class object or primitive type object for the declared type name.
     *
     * @throws NullPointerException if the type name is not defined in the
     *         method doc comments.
     * @throws InstantiationException if the return type could not be created
     *         for any reason.
     * @return \KM\Lang\Reflect\Type The return type for the method this object
     *         represents.
     */
    private function getReturnType0()
    {
        $part = strstr($this->root->getDocComment(), '@return');
        if ($part === false) {
            return null;
        }
        $parts = explode(' ', trim(str_replace('@return', '', $part)));
        $typeName = trim($parts[0]);
        if (strtolower($typeName) == 'void') {
            return null;
        }
        return ReflectionUtility::typeFor($typeName);
    }

    protected function getParameters0()
    {
        /* @var $rp \ReflectionParameter */
        $res = [];
        foreach ($this->root->getParameters() as $i => $rp) {
            $res[$i] = new Parameter($rp->getName(), $i, $this);
        }
        return $res;
    }

    /**
     * Returns an array of <code>Type</code> objects that represent formal
     * parameter types, in declaration order, of the method represented by this
     * object. Returns an array of length 0 if the underlying method takes no
     * parameters. Parameter elements that have not been type-hinted or are
     * primitives will return null.
     *
     * @return \KM\Lang\Reflect\Type[] The parameter types for the executable
     *         this object represents.
     * @see \KM\Lang\Reflect\Executable::getParameterTypes()
     */
    public function getParameterTypes()
    {
        if ($this->parameterTypes === null) {
            $this->parameterTypes = $this->getParameterTypes0();
        }
        return $this->parameterTypes;
    }

    private function getParameterTypes0()
    {
        /* @var $p Parameter */
        if ($this->parameters === null) {
            throw new IllegalStateException();
        }
        $types = [];
        foreach ($this->parameters as $p) {
            $types[] = $p->getType();
        }
        return $types;
    }

    /**
     * The Doctrine implementation of Annotations currently does not support
     * parameter annotations. This method returns an array of empty arrays, that
     * is, an empty array for each of this method's parameters.
     *
     * @return array
     */
    public function getParameterAnnotations()
    {
        $annotations = [];
        $numParams = $this->root->getNumberOfParameters();
        for ($i = 0; $i < $numParams; $i ++) {
            $annotations[$i] = [];
        }
        return $annotations;
    }

    /**
     * Returns an array of <code>Clazz</code> objects representing the declared
     * (checked) exception classes from this <code>Method</code>.
     *
     * @return \KM\Lang\Clazz[] An array of <code>Clazz</code> objects of the
     *         declared exceptions for this <code>Method</code>, or null if none
     *         were declared or could be transformed into qualified class names.
     */
    public function getExceptionTypes()
    {
        if ($this->exceptionTypes === null) {
            $this->exceptionTypes = $this->getExceptionTypes0();
        }
        return $this->exceptionTypes;
    }

    private function getExceptionTypes0()
    {
        $docComment = $this->getDocComment();
        $returnValue = array();
        while (($str = strstr($docComment, '@throws')) !== false) {
            $parts = explode(' ', trim(str_replace('@throws', '', $str)));
            $cname = trim($parts[0]);
            if ($cname[0] !== '\\') {
                // Find the FQCN of the exception class name.
                $cname = $this->qualifyName($cname);
            }
            if ($cname !== null) {
                // Only return Clazz objects
                $returnValue[] = Clazz::forName($cname);
            }
            $docComment = substr($str, 8);
        }
        return $returnValue;
    }

    /**
     * Returns the fully qualified class name of the given class name, or null
     * if one cannot be found, The method inspects the filename of the declaring
     * class, looking for use statements. If the given class name is already
     * fully qualified, it is returned unmodified.
     *
     * @param string $name The class name to qualify.
     * @return string The fully qualified class name of the given class name, or
     *         null if not found.
     */
    private function qualifyName($name)
    {
        if ($name[0] === '\\') {
            return $name;
        }
        $text = file_get_contents($this->root->getFileName());
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            if (preg_match('/^use (.*' . $name . ')/', $line, $matches)) {
                return '\\' . $matches[1];
            }
        }
        return null;
    }

    /**
     * Invokes the underlying function represented by this
     * <code>Procedure</code> object with the specified parameters. If the
     * number of formal parameters required by the underlying method is 0, the
     * supplied <code>args</code> array may be of length 0 or null.
     *
     * @param array $args The arguments used for the method call as an array.
     * @return mixed The result of dispatching the function represented by this
     *         object with arguments <code>args</code>.
     * @throws IllegalArgumentException if the <code>arg</code> parameter does
     *         not match the signature of this function.
     * @throws InvocationTargetException if the underlying function fails for
     *         any reason.
     */
    public function invoke(array $args = null)
    {
        try {
            return $this->root->invokeArgs($args);
        } catch (\ReflectionException $ex) {
            throw new IllegalArgumentException($ex->getMessage(), null, $ex);
        } catch (\Exception $e) {
            throw new InvocationTargetException($e, $e->getMessage() . $e);
        }
    }

    /**
     * Compares this <code>Procedure</code> object against the specified object.
     * Returns true if the objects are the same. Two <code>Procedure</code>
     * objects are the same if they have the same name and formal parameter
     * types and return type.
     *
     * @param Object $obj The object to compare.
     * @return boolean <code>True</code> if the objects are the same,
     *         <code>false</code> otherwise.
     * @see \KM\Lang\Object::equals()
     */
    public function equals(Object $obj = null)
    {
        /* @other Procedure */
        if ($obj === $this) {
            return true;
        }
        if ($obj === null || ! $obj instanceof Procedure) {
            return false;
        }
        $other = $obj;
        return ($other->getName() == $this->getName()) && ($other->getReturnType() == $this->getReturnType());
    }

    /**
     * Returns a string describing this <code>Procedure</code>. The string is
     * formatted as the function return type, followed by a space followed by
     * the function name, followed by a parenthesized, comma-separated list of
     * the function's formal parameter types. If the function throws checked
     * exceptions. the parameter list is followed by a space followed by the
     * word throws followed by a comma-separated list of the thrown exception
     * types.
     *
     * @return string A string describing this <code>Procedure</code>.
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        $sb = (($returnType = $this->getReturnType()) != null) ? $this->getFormattedReturnType() . ' ' : '';
        $sb .= $this->getName();
        $sb .= ReflectionUtility::getParameterNames($this->getParameters());
        return $sb;
    }

    /**
     * Returns true if an annotation for the specified type is present on this
     * element, else false. This method is designed primarily for convenient
     * access to marker annotations. The truth value returned by this method is
     * equivalent to <code>getAnnoptation(annotationType) != null</code>.
     *
     * @param string $annotationName The name of the annotation.
     * @return boolean True if an annotation for the specified annotation type
     *         is present on this element, else false.
     * @see \KM\Lang\Reflect\AnnotatedElement::isAnnotationPresent()
     */
    public function isAnnotationPresent($annotationName)
    {
        return $this->getAnnotationReader()->getMethodAnnotation($this->root, $annotationName) != null;
    }

    /**
     * Returns this element's annotation for the specified type if such an
     * annotation is present, else null.
     *
     * @param string $annotationName The type of the annotation to query for and
     *            return if present.
     * @return \Doctrine\Common\Annotations\Annotation This element's annotation
     *         for the specified annotation type if present on this element,
     *         else null.
     * @see \KM\Lang\Reflect\AnnotatedElement::getAnnotation()
     */
    public function getAnnotation($annotationName)
    {
        return $this->getAnnotationReader()->getMethodAnnotation($this->root, $annotationName);
    }

    /**
     * Returns annotations that are directly present on this element. This
     * method ignores inherited annotations. If there are no annotations
     * directly present on this element, the return value is an array of length
     * 0.
     *
     * @return \Doctrine\Common\Annotations\Annotation[] Annotations directly
     *         present on this element.
     * @see \KM\Lang\Reflect\AnnotatedElement::getAnnotations()
     */
    public function getAnnotations()
    {
        return $this->getAnnotationReader()->getMethodAnnotations($this->root);
    }

    /**
     * Lazy loads and returns the annotation reader.
     *
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    private function getAnnotationReader()
    {
        if ($this->reader === null) {
            $this->reader = new AnnotationReader();
        }
        return $this->reader;
    }
}
?>