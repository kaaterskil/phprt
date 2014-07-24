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
use KM\Lang\IllegalAccessException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\InstantiationException;
use KM\Lang\Object;
use KM\Lang\Reflect\InvocationTargetException;
use KM\Lang\Reflect\Method;
use KM\Lang\Reflect\Parameter;
use KM\Lang\Reflect\Type;

/**
 * <code>Constructor</code> provides information about, and access to, a single
 * constructor for a class.
 *
 * @author Blair
 */
class Constructor extends Executable implements Member, AccessibleObject
{

    /**
     * The declaring class
     *
     * @var Clazz
     */
    private $clazz;

    /**
     * The annotation reader.
     *
     * @var AnnotationReader
     */
    private $reader;

    /**
     * The underlying reflection method
     *
     * @var \ReflectionMethod
     */
    private $root;

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

    /**
     * Creates a new Constructor with the given declaring class.
     * Construction fails if the declaring class has no constructor.
     *
     * @param Clazz $declaringClazz The <code>Clazz</code> object representing
     *            the declaring class.
     * @param \ReflectionMethod the underlying constructor
     * @throws InstantiationException if the declaring class has no constructor.
     */
    public function __construct(Clazz $declaringClazz, \ReflectionMethod $root)
    {
        if($root === null) {
            throw new InstantiationException();
        }
        parent::__construct();
        $this->clazz = $declaringClazz;
        $this->root = $root;
        $this->name = $root->getName();
        $this->parameters = $this->getParameters0();
        $this->parameterTypes = $this->getParameterTypes0();
        $this->exceptionTypes = $this->getExceptionTypes0();
    }

    /**
     * Returns the underlying reflector.
     *
     * @return \ReflectionMethod
     * @see \KM\Lang\Reflect\Executable::getReflector()
     */
    public function getReflector()
    {
        return $this->root;
    }

    /**
     * Return the <code>Clazz</code> object representing the class that declared
     * this method.
     *
     * @return \KM\Lang\Clazz The class object representing the class that
     *         declared this method.
     * @see \KM\Lang\Reflect\Member::getDeclaringClass()
     */
    public function getDeclaringClass()
    {
        return $this->clazz;
    }

    /**
     * Returns the modifiers for the method represented by this
     * <code>Field</code> object as an integer.
     * The bitmask includes the PHP
     * constants IS_STATIC, IS_PUBLIC, IS_PROTECTED and IS_PRIVATE.
     *
     * @return int The bitmask of the modifiers for this method.
     * @see \KM\Lang\Reflect\Member::getModifiers()
     */
    public function getModifiers()
    {
        return $this->root->getModifiers();
    }

    protected function getParameters0()
    {
        /* @var $rp \ReflectionParameter */
        $res = [];
        $className = $this->clazz->getName();
        foreach ($this->root->getParameters() as $i => $rp) {
            $res[$i] = new Parameter($rp->getName(), $i, $this, $className);
        }
        return $res;
    }

    /**
     * Returns an array of <code>Type</code> objects that represent formal
     * parameter types, in declaration order, of the method represented by this
     * object.
     * Returns an array of length 0 if the underlying method takes no
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
        $types = [];
        foreach ($this->parameters as $p) {
            $types[] = $p->getType();
        }
        return $types;
    }

    /**
     * The Doctrine implementation of Annotations currently does not support
     * parameter annotations.
     * This method returns an array of empty arrays, that
     * is, an empty array for each of this method's parameters.
     *
     * @return array
     */
    public function getParameterAnnotations()
    {
        $annotations = [];
        $numParams = $this->root->getNumberOfParameters();
        for ($i = 0; $i < $numParams; $i++) {
            $annotations[$i] = [];
        }
        return $annotations;
    }

    /**
     * Returns an array of <code>CLazz</code> objects representing the declared
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
     * class, looking for use statements.
     * If the given class name is already
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
        $text = file_get_contents($this->getDeclaringClass()->getFileName());
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            if (preg_match('/^use (.*' . $name . ')/', $line, $matches)) {
                return '\\' . $matches[1];
            }
        }
        return null;
    }

    /**
     * Compares this <code>Method</code> against the specified object.
     * Returns
     * true if the objects are the same. Two <code>Methods</code> are the same
     * if they were declared by the same class and have the same name and formal
     * parameter types.
     *
     * @param Object $obj The object to compare.
     * @return boolean <code>True</code> if the objects are the same,
     *         <code>false</code> otherwise.
     * @see \KM\Lang\Object::equals()
     */
    public function equals(Object $obj = null)
    {
        /* @var $other Constructor */
        if ($obj != null && $obj instanceof Constructor) {
            $other = $obj;
            if (($this->getDeclaringClass() == $other->getDeclaringClass()) &&
                 ($this->getName() == $other->getName())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a string describing this {@code Method}.
     * The string is formatted
     * as the method access modifiers, if any, followed by the class declaring
     * the method,
     * followed by a period, followed by the method name, followed by a
     * parenthesized, comma-separated list of the method's formal parameter
     * types. If the method throws checked exceptions, the parameter list is
     * followed by a space, followed by the word throws followed by a
     * comma-separated list of the thrown exception types.
     *
     * @return string A string describing this <code>Method</code>.
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        $sb = ReflectionUtility::printModifiersIfNonZero($this->getModifiers());
        $sb .= $this->getDeclaringClass()->getName() . '.';
        $sb .= $this->getName();
        $sb .= ReflectionUtility::getParameterNames($this->getParameters());
        return $sb;
    }

    /**
     * Uses the constructor represented by this <code>Constructor</code> object
     * to create and initialize a new instance of the constructor's declaring
     * class, with the specified initialization parameters.
     *
     * <p>If the number of formal parameters required by the underlying
     * constructor is 0, the supplied <code>initArgs</code> array may be of
     * length 0 or null.
     *
     * <p>If the constructor completes normally, returns the newly created and
     * initialized instance.
     *
     * @param array $initaArgs Array of arguments to be passed to the
     *        constructor.
     * @throws IllegalArgumentException if the number of actual and formal
     *         parameters differ; a parameter cannot be converted to the
     *         corresponding formal parameter type; if this constructor pertains
     *         to an enum type.
     * @throws InstantiationException if the class that declares the underlying
     *         constructor represents an abstract class.
     * @throws InvocationTargetException if the underlying constructor throws an
     *         exception.
     * @return \KM\Lang\Object A new object created by calling the constructor
     *         this object represents.
     */
    public function newInstance(array $initArgs = null)
    {
        if ($this->clazz->isEnum()) {
            throw new IllegalArgumentException(
                'cannot reflectively create enum objects');
        }
        $numParams = $this->getParameterCount;
        if ($numParams != count($initArgs)) {
            throw new IllegalArgumentException();
        }
        if ($this->clazz->isAbstract()) {
            throw new InstantiationException();
        }
        try {
            $ca = $this->root->getDeclaringClass();
            $inst = $ca->newInstanceArgs($initArgs);
            return $inst;
        } catch (\ReflectionException $ex) {
            throw new InvocationTargetException($e, $e->getMessage() . $e);
        }
    }

    /**
     * Set the <code>accessible</code> flag for this object tp the indicated
     * boolean value.
     * A value of <code>true</code> indicates that the reflected
     * object should suppress access checking when it is used. A value of
     * <code>false</code> indicates that the reflected object should enforce
     * access checks.
     *
     * @param boolean $flag The new value for the <code>accessible</code> flag.
     * @see \KM\Lang\Reflect\AccessibleObject::setAccessible()
     */
    public function setAccessible($flag)
    {
        self::setAccessible0($this, $flag);
    }

    private static function setAccessible0(AccessibleObject $obj, $flag)
    {
        /* @var $c Constructor */
        if ($obj instanceof Constructor && $flag === true) {
            $c = $obj;
            if ($c->getDeclaringClass() == Clazz::clazz()) {
                throw new IllegalAccessException(
                    'cannot make a KM\Lang\Clazz constructor accessible');
            }
        }
        $obj->getReflector()->setAccessible($flag);
    }

    /**
     * Checks if the method is private.
     *
     * @return boolean code>true</code> if the member is private,
     *         <code>false</code> otherwise,
     * @see \KM\Lang\Reflect\AccessibleObject::isPrivate()
     */
    public function isPrivate()
    {
        return $this->root->isPrivate();
    }

    /**
     * Checks if the method is protected.
     *
     * @return boolean <code>true</code> if the member is protected,
     *         <code>false</code> otherwise,
     * @see \KM\Lang\Reflect\AccessibleObject::isProtected()
     */
    public function isProtected()
    {
        return $this->root->isProtected();
    }

    /**
     * Checks if the method is public.
     *
     * @return boolean <code>true</code> if the member is public,
     *         <code>false</code> otherwise,
     * @see \KM\Lang\Reflect\AccessibleObject::isPublic()
     */
    public function isPublic()
    {
        return $this->root->isPublic();
    }

    /**
     * Checks if the method is a static class method.
     *
     * @return boolean <code>True</code> if the method is a static class method,
     *         <code>false</code> otherwise.
     */
    public function isStatic()
    {
        return $this->root->isStatic();
    }

    /**
     * Checks if the methods is final.
     *
     * @return boolean <code>true</code> if the member is final,
     *         <code>false</code> otherwise,
     * @see \KM\Lang\Reflect\Member::isFinal()
     */
    public function isFinal()
    {
        return $this->root->isFinal();
    }

    /**
     * Returns true if an annotation for the specified type is present on this
     * element, else false.
     * This method is designed primarily for convenient
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
        return $this->getAnnotationReader()->getMethodAnnotation($this->root,
            $annotationName) != null;
    }

    /**
     * Returns this element's annotation for the specified type if such an
     * annotation is present, else null.
     *
     * @param string $annotationName The type of the annotation to query for and
     *        return if present.
     * @return \Doctrine\Common\Annotations\Annotation This element's annotation
     *         for the specified annotation type if present on this element,
     *         else null.
     * @see \KM\Lang\Reflect\AnnotatedElement::getAnnotation()
     */
    public function getAnnotation($annotationName)
    {
        return $this->getAnnotationReader()->getMethodAnnotation($this->root,
            $annotationName);
    }

    /**
     * Returns annotations that are directly present on this element.
     * This
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