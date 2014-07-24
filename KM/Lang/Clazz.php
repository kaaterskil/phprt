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
namespace KM\Lang;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\ClassNotFoundException;
use KM\Lang\IllegalAccessException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\InstantiationException;
use KM\Lang\NoSuchFieldException;
use KM\Lang\NoSuchMethodException;
use KM\Lang\NullPointerException;
use KM\Lang\Reflect\AnnotatedElement;
use KM\Lang\Reflect\Constructor;
use KM\Lang\Reflect\Extension;
use KM\Lang\Reflect\Field;
use KM\Lang\Reflect\InvocationTargetException;
use KM\Lang\Reflect\Method;
use KM\Lang\Reflect\Member;
use KM\Lang\Reflect\MixedType;
use KM\Lang\Reflect\Parameter;
use KM\Lang\Reflect\PrimitiveType;
use KM\Lang\Reflect\Type;

/**
 * Instances of the class <code>Clazz</code> represent classes and interfaces in
 * a running PHP application.
 * An enum is a kind of class and an annotation is a
 * kind of class. <p> <code>Clazz</code> has no public constructor. Instead,
 * <code>Clazz</code> objects are loaded lazily by calls to the
 * <code>getClass</code> method of instances, or the static
 * <code>::clazz()</code> method of objects implementing KM\Lang\Object.
 *
 * @author Blair
 */
final class Clazz extends Object implements Serializable, Type, AnnotatedElement
{

    /**
     * Cache the name @Transient
     *
     * @var string
     */
    private $name;

    /**
     * The underlying reflection class @Transient
     *
     * @var \ReflectionClass
     */
    private $root;

    /**
     * Annotation reader
     *
     * @var AnnotationReader
     */
    private $reader;

    /**
     * The class constructor @Transient
     *
     * @var Constructor
     */
    private $cachedConstructor;

    /**
     * Returns the <code>Clazz</code> object associated with the class or
     * interface with the given string name.
     * Given the fully qualified name for
     * a class or interface (in the same format returned by
     * <code>getName</code>, this method attempts to locate, load and link the
     * class or interface.
     *
     * @param string $className Fully qualified name of the desired class.
     * @throws ClassNotFoundException if the initialization provoked by this
     *         method fails.
     * @return \KM\Lang\Clazz The <code>Clazz</code> object representing the
     *         desired class.
     */
    public static function forName($className)
    {
        try {
            return new self($className);
        } catch (\ReflectionException $e) {
            throw new InitializerException($e->getMessage(), $e);
        }
    }

    /**
     * Constructs a new Class object with the specified class name.
     * Private
     * method.
     *
     * @param string $className The class name to reflect.
     * @throws ClassNotFoundException if the clazz object could not be created
     *         for any reason.
     */
    private function __construct($className)
    {
        try {
            $this->root = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new ClassNotFoundException($e->getMessage(), $e);
        }
    }

    /**
     * Returns the underlying ReflectionClass object.
     *
     * @return \ReflectionClass
     */
    public function getReflector()
    {
        return $this->root;
    }

    /**
     * Converts the object to a string, The string representation if the string
     * "class" or "interface" followed by a space and then the fully qualified
     * name of the class in the format returned by <code>getName</code>.
     *
     * @return string A string representation of this Clazz object.
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        return ($this->isInterface() ? 'interface ' : ($this->isPrimitive() ? '' : 'class ')) .
             $this->getName();
    }

    /**
     * Creates a new instance of the class represented by this
     * <code>Clazz</code> object.
     * The class is instantiated as if by a
     * <code>new</code> expression with an empty argument list. The class is
     * initialized if it has not already been initialized.
     * Note that this method propagates any exception thrown by the constructor.
     * Use of this method effectively bypasses the exception checking that would
     * otherwise be performed. The <code>Constructor.newInstance(args[])</code>
     * method avoids this problem by wrapping any exception thrown by the
     * constructor in an <code>InvocationTargetException</code>.
     *
     * @return \KM\Lang\Object A newly allocated instance of the class
     *         represented by this object.
     * @throws InstantiationException if this <code>Clazz</code> represents an
     *         abstract class, an interface, or if the class has no nullary
     *         constructor, or if the instantiation fails for any reason.
     * @throws IllegalAccessException if the nullary constructor is not
     *         accessible.
     */
    public function newInstance()
    {
        // Constructor lookup
        if ($this == Clazz::clazz()) {
            throw new IllegalAccessException(
                'cannot call newInstance() on the class for KM\Lang\Clazz');
        }
        if ($this->cachedConstructor === null) {
            try {
                $empty = [];
                $c = $this->getConstructor0($empty, Member::DECLARED_MEMBER);
                $c->setAccessible(true);
                $this->cachedConstructor = $c;
            } catch (NoSuchMethodException $e) {
                throw new InstantiationException($this->getName());
            }
        }
        $tmpConstructor = $this->cachedConstructor;
        // Security check
        if (!$tmpConstructor->isPublic()) {
            throw new IllegalAccessException();
        }
        // Run constructor
        try {
            return $tmpConstructor->newInstance();
        } catch (InvocationTargetException $e) {
            throw $e->getPrevious();
        }
    }

    /**
     * Creates a new instance of the class represented by this
     * <code>Clazz</code> object without invoking its constructor.
     * WARNING:
     * Instantiation without initialization leaves the newly created object in
     * an indeterminate state. It is up to the caller to complete the
     * initialization process to ensure the legal state of the created object.
     *
     * @throws InstantiationException if the class is an internal class (i.e.
     *         not a user-defined class) that cannot be instantiated without
     *         invoking its constructor, or if instantiation fails for any
     *         reason.
     * @return \KM\Lang\Object A new empty instance of the class represented by
     *         this object.
     */
    public function newInstanceWithoutConstructor()
    {
        try {
            return $this->root->newInstanceWithoutConstructor();
        } catch (\ReflectionException $e) {
            throw new InstantiationException($this->getName());
        }
    }

    /**
     * Determines if the specified <code>Object</code> is assignment-compatible
     * with the object represented by this <code>Clazz</code>.
     * This method is the dynamic equivalent to the <code>instanceOf</code>
     * operator. The method returns <code>true</code> if the specified
     * <code>Object</code> argument is non-null and can be cast to the reference
     * type represented by this <code>Clazz</code> object without raising a
     * <code>ClassCastException</code>, It returns <code>false</code> otherwise.
     *
     * @param Object $obj The object to check.
     * @return boolean True if <code>obj</code> is an instance of this class.
     */
    public function isInstance($obj)
    {
        if ($obj === null) {
            return false;
        }
        return $this->root->isInstance($obj);
    }

    /**
     * Determines if the class or interface represented by this
     * <code>Clazz</code> object is either the same as, or is a super class or
     * super interface of the class or interface represented by the specified
     * <code>Clazz</code> parameter.
     * It returns <code>true</code> if so;
     * otherwise it returns <code>false</code>.
     *
     * @param Clazz $cls The <code>Clazz</code> object to be checked.
     * @return boolean The value indicating whether objects of the type
     *         <code>cls</code> can be assigned to objects of this class.
     */
    public function isAssignableFrom(Clazz $cls)
    {
        if ($cls->getName() == $this->getName()) {
            return true;
        }
        if ($cls->isInterface()) {
            return $this->isInterface() ? $cls->root->isSubclassOf(
                $this->getName()) : false;
        }
        return $this->isInterface() ? $cls->root->implementsInterface(
            $this->getName()) : $cls->root->isSubclassOf($this->getName());
    }

    /**
     * Checks whether the class is a subclass, or implements the interface, of
     * the <code>name</code> defining a class or interface
     *
     * @param string $name The name of a class or interface to check.
     * @return boolean <code>True</code> if the class is a subclass, or
     *         implements the interface, of the <code<name</code> defining a
     *         class or interface, <code>false</code> otherwise.
     */
    public function isSubclassOf($name)
    {
        return $this->root->isSubclassOf($name);
    }

    /**
     * Checks whether this class implements the specified interface.
     *
     * @param string $interface THe name of the interface to check.
     * @return boolean <code>True</code> if this class implements the specified
     *         interface, <code>false</code> otherwise.
     */
    public function implementsInterface($interface)
    {
        return $this->root->implementsInterface($interface);
    }

    /**
     * Determines if the specified <code>Clazz</code> object represents an
     * interface type.
     *
     * @return boolean <code>True</code> of this object represents an interface;
     *         <code>false</code> otherwise.
     */
    public function isInterface()
    {
        return $this->root->isInterface();
    }

    /**
     * Determines if this <code>Clazz</code> object represents an Array class.
     * More specifically, this method returns <code>true> if this
     * <code>Clazz</code> object implements the core /ArrayAccess interface
     * enabling it to be accessed as an array.
     *
     * @return boolean <code>True</code> if this object provides array access
     *         operations; <code>false</code> otherwise.
     * @see \KM\Lang\Reflect\Type::isArray()
     */
    public function isArray()
    {
        return false;
    }

    /**
     * Determines of this <code>Type</code> object represents a mixed type.
     *
     * @return boolean Always returns <code>false</code>
     * @see \KM\Lang\Reflect\Type::isMixed()
     */
    public function isMixed()
    {
        return false;
    }

    /**
     * Determines if the specified <code>Clazz</code> object represents a
     * primitive type.
     * This method always returns <code>false</code>.
     *
     * @return boolean Whether this class represents a primitive type. Always
     *         returns false.
     * @see \KM\Lang\Reflect\Type::isPrimitive()
     */
    public function isPrimitive()
    {
        return false;
    }

    /**
     * Determines if the specified <code>Clazz</code> object represents an
     * Object.
     * This method always returns <code>true</code>.
     *
     * @return boolean Whether this class represents an Object.
     * @see \KM\Lang\Reflect\Type::isObject()
     */
    public function isObject()
    {
        return !$this->root->isInterface();
    }

    /**
     * Returns true if this <code>Clazz</code> object represents an annotation
     * type.
     *
     * @return boolean <code>True</code> if this class object represents an
     *         annotation type, <code>false</code> otherwise.
     */
    public function isAnnotation()
    {
        return $this->root->implementsInterface(
            '\KM\Lang\Annotation\Annotation');
    }

    /**
     * Checks whether this class is defined in a namespace.
     *
     * @return boolean <code>True</code> if this class is defined in a
     *         namespace, <code>false</code> otherwise.
     */
    public function inNamespace()
    {
        return $this->root->inNamespace();
    }

    /**
     * Checks if this class defines an abstract class.
     *
     * @return boolean <code>True</code> if the defined class is abstract,
     *         <code>false</code> otherwise.
     */
    public function isAbstract()
    {
        return $this->root->isAbstract();
    }

    /**
     * Checks whether instances of this class can be cloned.
     *
     * @return boolean <code>True</code> if this class can be cloned,
     *         <code>false</code> otherwise.
     */
    public function isCloneable()
    {
        return $this->root->isCloneable();
    }

    /**
     * Checks whether this class is defined as final.
     *
     * @return boolean <code>True</code> if this class is defined as final,
     *         <code>false</code> otherwise.
     */
    public function isFinal()
    {
        return $this->root->isFinal();
    }

    /**
     * Checks whether this class can be instantiated.
     * This method will return
     * false if the class object defines an interface, an abstract class or a
     * public class with a non-public constructor.
     *
     * @return boolean <code>True</code> if this class can be instantiated,
     *         <code>false</code> if this class object defines an interface, an
     *         abstract class, or a public class with a non-public constructor.
     */
    public function isInstantiable()
    {
        return $this->root->isInstantiable();
    }

    /**
     * Checks whether this class is defined internally by an extension or the
     * core.
     *
     * @return boolean <code>True</code> if this class is defined internally by
     *         an extension or the core, <code>false</code> if user-defined.
     */
    public function isInternal()
    {
        return $this->root->isInternal();
    }

    /**
     * Checks whether the class is iterable.
     *
     * @return boolean <code>True</code> if this class implements or extends a
     *         traversable interface, such as \ArrayAccess or \Iterator.
     */
    public function isIterable()
    {
        return ($this->root->isIterateable()) ||
             $this->root->implementsInterface('\KM\Util\Iterator');
    }

    /**
     * Returns the name of the entity (class, interface, array class, primitive
     * type, or void) represented by this <code>Class</code> object, as a
     * <code>String</code>.
     *
     * @return string The name of the class or interface represented by this
     *         object.
     */
    public function getName()
    {
        $name = $this->name;
        if ($name === null) {
            $name = $this->getName0();
            $this->name = $name;
        }
        return $name;
    }

    private function getName0()
    {
        return $this->root->name;
    }

    /**
     * Returns the <code>Clazz</code> representing the super class of the entity
     * (class or interface) represented by this <code>Clazz</code>.
     * If this
     * <code>Clazz</code> represents either the <code>Object</code> class or an
     * interface, then null is returned.
     *
     * @return \KM\Lang\Clazz The super class of the class represented by this
     *         object.
     */
    public function getSuperclass()
    {
        /* @var $superClass \ReflectionClass */
        if ($this->root->isInterface() || $this->getName() == '\KM\Lang\Object') {
            return null;
        }
        $superClass = $this->root->getParentClass();
        if ($superClass == null) {
            return null;
        }
        return new self($superClass->getName());
    }

    /**
     * Alias for <code>getSuperclass</code> to provide compatibility with PHP
     * method names.
     *
     * @return \KM\Lang\Clazz The super class of the class represented by this
     *         object.
     */
    public function getParentClass()
    {
        return $this->getSuperclass();
    }

    /**
     * Gets the package for this class.
     * THe class loader of this class is used
     * to find the package.
     *
     * @return \KM\Lang\Package The package of the class, or null if no package
     *         information is available from the class loader.
     */
    public function getPackage()
    {
        return Package::getPackageFromClazz($this);
    }

    /**
     * Returns an array of the names of interfaces that this class implements.
     * The array will list interfaces from super classes first, then from the
     * class itself. If the class implements no interfaces, the return value
     * will be an array of length 0.
     *
     * @return string[] An array of interface names implemented by this class.
     */
    public function getInterfaceNames()
    {
        return $this->root->getInterfaceNames();
    }

    /**
     * Determines the interfaces implemented by the class or interface
     * represented by this object.
     * If this object represents a class, the return
     * value is an array containing objects representing all interfaces
     * implemented by the class. If this object represents an interface, the
     * array contains objects representing all interfaces extended by this
     * interface, If this object represents a class or interface that implements
     * no interfaces the method returns an array of length 0.
     *
     * @return \KM\Lang\Clazz[] An array of interfaces implemented by this
     *         class.
     */
    public function getInterfaces()
    {
        /* @var $iface \ReflectionClass */
        $res = [];
        foreach ($this->root->getInterfaces() as $iface) {
            $res[] = new self($iface->getName());
        }
        return $res;
    }

    /**
     * Returns the bitmask modified for this class or interface encoded as an
     * integer.
     * The modifiers consist of PHP's constants for
     * IS_IMPLICIT_ABSTRACT, IS_EXPLICIT_ABSTRACT and IS_FINAL.
     *
     * @return int The number representing the modifiers for this class.
     */
    public function getModifiers()
    {
        return $this->root->getModifiers();
    }

    /**
     * If the class of interface represented by this <code>Clazz</code> object
     * is a member of another class, this method returns the <code>Clazz</code>
     * object representing the class in which it was declared.
     * This method
     * returns null if this class or interface is not a member of any other
     * class.
     *
     * @return \KM\Lang\Clazz The declaring class for this class.
     */
    public function getDeclaringClass()
    {
        $candidate = $this->getDeclaringClass0();
        return $candidate;
    }

    private function getDeclaringClass0()
    {
        $ns = $this->root->getNamespaceName();
        if (!empty($ns)) {
            $i = strrpos($ns, '\\');
            if ($i !== false) {
                $name = '\\' . $ns;
                try {
                    return new self($name);
                } catch (\Exception $e) {}
            }
        }
        return null;
    }

    /**
     * Returns the simple name of the underlying class as given in the source
     * code.
     *
     * @return string The simple name of the underlying class.
     */
    public function getShortName()
    {
        return $this->root->getShortName();
    }

    /**
     * Returns an informative string for the name of this type.
     *
     * @return string An informative string for the name of this type.
     * @see \KM\Lang\Reflect\Type::getTypeName()
     */
    public function getTypeName()
    {
        return $this->getName();
    }

    /**
     * Returns the <code>Type</code> representing the component type of an
     * array.
     * If this type does not represent an array this method returns null.
     *
     * @return Type The <code>Type</code> representing the component type of
     *         this type if this type represents an array.
     * @see \KM\Lang\Reflect\Type::getComponentType()
     */
    public function getComponentType()
    {
        return null;
    }

    /**
     * Returns the canonical name of the underlying class.
     *
     * @return string The canonical name of the underlying class.
     */
    public function getCanonicalName()
    {
        return $this->getName();
    }

    /**
     * Returns an array of defined constants for this class.
     *
     * @return array An array of defined constants from this class.
     */
    public function getConstants()
    {
        return $this->root->getConstants();
    }

    /**
     * Returns an array containing the <code>Field</code> objects reflecting all
     * the accessible public fields of the class or interface represented by
     * this <code>Clazz</code> object.
     * If this <code>Clazz</code> object
     * represents a class or interface with no accessible public fields, then
     * this method returns an array of length 0. If this <code>Clazz</code>
     * object represents a class, then this method returns the public fields of
     * the class and all of its super classes. The elements in the returned
     * array are not sorted and are not in any particular order.
     *
     * @return \KM\Lang\Reflect\Field[] The array of <code>Field</code> objects
     *         representing the public fields of this class.
     */
    public function getFields()
    {
        /* @var $p \ReflectionProperty */
        $props = $this->root->getProperties(\ReflectionProperty::IS_PUBLIC);
        $res = [];
        foreach ($props as $p) {
            $res[] = new Field($this, $p->getName());
        }
        return $res;
    }

    /**
     * Returns an array containing <code>Method</code> objects reflecting all
     * the public methods of the class or interface represented by this
     * <code>Clazz</code> object, including those declared by the class or
     * interface and those inherited from super classes and super interfaces.
     * The elements of the returned array are not sorted and are not in any
     * particular order. If this <code>Clazz</code> object represents a type
     * with a class initialization method <code>clinit</code> then the returned
     * array does <em>not</em> have a corresponding <code>Method</code> object.
     *
     * @return \KM\Lang\Reflect\Method[] The array of <code>Method</code>
     *         objects representing the public methods of this class.
     */
    public function getMethods()
    {
        /* @var $m \ReflectionMethod */
        $methods = $this->root->getMethods(\ReflectionMethod::IS_PUBLIC);
        $res = [];
        foreach ($methods as $m) {
            if ($m->getName() != 'clinit') {
                $res[] = new Method($this, $m->getName());
            }
        }
        return $res;
    }

    /**
     * Checks if the specified constant is defined in this class.
     *
     * @param string $name The name of the constant being checked for.
     * @return boolean <code>True</code> if the specified constant is defined in
     *         this class, <code>false</code> otherwise.
     */
    public function hasConstant($name)
    {
        return $this->root->hasConstant($name);
    }

    /**
     * Checks if the specified field is defined in this class.
     *
     * @param string $name The name of the field being checked for.
     * @return boolean <code>True</code> if the specified field is defined in
     *         this class, <code>false</code> otherwise.
     */
    public function hasField($name)
    {
        return $this->root->hasProperty($name);
    }

    /**
     * Checks if the specified method is defined in this class.
     *
     * @param string $name The name of the method being checked for.
     * @return boolean <code>True</code> if the specified method is defined in
     *         this class, <code>false</code> otherwise.
     */
    public function hasMethod($name)
    {
        return $this->root->hasMethod($name);
    }

    /**
     * Returns the defined constant.
     *
     * @param string $name The name of the constant to return.
     * @throws NullPointerException if the name is null.
     * @throws NoSuchFieldException if the constant with the specified name is
     *         not found.
     * @return mixed The constant value.
     */
    public function getConstant($name)
    {
        if ($name === null) {
            throw new NullPointerException();
        }
        $res = $this->root->getConstant($name);
        if ($res === null) {
            throw new NoSuchFieldException();
        }
    }

    /**
     * Returns a <code>Field</code> object that reflects the specified public
     * member field of the class or interface represented by this
     * <code>Clazz</code> object.
     * The <code>name</code> parameter is a string
     * specifying the simple name of the desired field.
     *
     * @param string $name The field name.
     * @throws NullPointerException if <code>name</code> is <code>null</code>.
     * @throws NoSuchFieldException if a field with the specified name is not
     *         found.
     * @return \KM\Lang\Reflect\Field The <code>Field</code> object of this
     *         class specified by <code>name</code>.
     */
    public function getField($name)
    {
        if (empty($name)) {
            throw new NullPointerException();
        }
        if ($this->root->hasProperty($name)) {
            $field = $this->root->getProperty($name);
            if ($field !== null && $field->isPublic()) {
                return new Field($this, $field->getName());
            }
        }
        throw new NoSuchFieldException();
    }

    /**
     * Returns a <code>Method</code> object that reflects the specified public
     * member method of the class or interface represented by this
     * <code>Clazz</code> object.
     * The <code>name</code> parameter is a string specifying the simple name of
     * the desired method. If the <code>name</code> is <code>init</code> or
     * <code>clinit</code>, a NoSuchMethod exception is raised.
     *
     * @param string $name The name of the method.
     * @throws NullPointerException if <code>name</code> is <code>null</code>.
     * @throws NoSuchMethodException if a matching method is not found.
     * @return \KM\Lang\Reflect\Method
     */
    public function getMethod($name)
    {
        if (empty($name)) {
            throw new NullPointerException();
        }
        if ($name == 'init' || $name == 'clinit') {
            throw new NoSuchMethodException();
        }
        if ($this->root->hasMethod($name)) {
            $method = $this->root->getMethod($name);
            if ($method != null && $method->isPublic()) {
                return new Method($this, $method->getName());
            }
        }
        throw new NoSuchMethodException();
    }

    /**
     * Returns a <code>Constructor</code> object that reflects the specified
     * public constructor of the class represented by this <code>Clazz</code>
     * object.
     *
     * @param $parameterTypes The parameter array.
     * @throws NoSuchMethodException if a matching method is not found.
     * @return \KM\Lang\Reflect\Constructor The <code>Constructor</code> object
     *         of the public constructor of this class.
     */
    public function getConstructor(array $parameterTypes = [])
    {
        return $this->getConstructor0($parameterTypes, Member::PUBLIC_MEMBER);
    }

    /**
     * Returns an array o f<code>Field</code> objects reflecting all the fields
     * declared by the class or interface represented by this <code>Clazz</code>
     * object.
     * This includes public, protected and private fields, but excludes
     * inherited fields. If this <code>Clazz</code> object represents a class or
     * interface with no declared fields, then this method returns an array of
     * length 0. The elements in the returned array are not sorted and are not
     * in any particular order.
     *
     * @return \KM\Lang\Reflect\Field[] The array of <code>Field</code> objects
     *         representing all the declared fields of this class.
     */
    public function getDeclaredFields()
    {
        /* @var $p \ReflectionProperty */
        $res = [];
        foreach ($this->root->getProperties() as $p) {
            if ($p->getDeclaringClass()->getName() == $this->getName()) {
                $res[] = new Field($this, $p->getName());
            }
        }
        return $res;
    }

    /**
     * Returns an array containing <code>Method</code> objects reflecting all
     * the declared methods of the class or interface represented by this
     * <code>Clazz</code> object, including public, protected and private
     * methods, but excluding inherited methods.
     * If this <code>Clazz</code>
     * object represents a type that has a class initialization method
     * <code>clinit</code>, then the returned array does <em>not</em> have a
     * corresponding <code>Method</code> object.
     *
     * @return \KM\Lang\Reflect\Method[] The array of <code>Method</code>
     *         objects representing the declared methods of this class.
     */
    public function getDeclaredMethods()
    {
        /* @var $m \ReflectionMethod */
        $res = [];
        foreach ($this->root->getMethods() as $m) {
            if (($m->getDeclaringClass()->getName() == $this->getName()) &&
                 ($m->getName() != 'clinit')) {
                $res = new Method($this, $m->getName());
            }
        }
        return $res;
    }

    /**
     * Returns a <code>Field</code> object that reflects the specified declared
     * fields of the class or interface represented by this <code>Clazz</code>
     * object.
     * The <code>name</code> parameter is a string that specified the
     * simple name of the desired field.
     *
     * @param string $name The name of the field.
     * @throws NoSuchFieldException if a field with the specified name is not
     *         found.
     * @return \KM\Lang\Reflect\Field The <code>field</code> object for the
     *         specified field in this class.
     */
    public function getDeclaredField($name)
    {
        if ($this->root->hasProperty($name)) {
            $p = $this->root->getProperty($name);
            if ($p->getDeclaringClass()->getName() == $this->getName()) {
                return new Field($this, $name);
            }
        }
        throw new NoSuchFieldException();
    }

    /**
     * Returns a <code>Method</code> object that reflects the specified declared
     * method of the class or interface represented by this <code>Clazz</code>
     * object.
     * The <code>name</code> parameter is a string that specified the
     * simple name of the desired method.
     *
     * @param string $name The name of the method.
     * @throws NoSuchMethodException if a matching method is not found.
     * @return \KM\Lang\Reflect\Method The <code>MEthod</code> object for the
     *         method of this class matching the specified name.
     */
    public function getDeclaredMethod($name)
    {
        if ($this->root->hasMethod($name)) {
            $m = $this->root->getMethod($name);
            if ($m->getDeclaringClass()->getName() == $this->getName()) {
                return new Method($this, $name);
            }
        }
        throw new NoSuchMethodException($name);
    }
    
    /* ---------- Constructor Handling ---------- */
    
    /**
     * Returns a <code>Constructor</code> object that reflects the specified
     * constructor of the class represented by this <code>Clazz</code> object.
     * The <code>parameterTypes</code> parameter is an array of
     * <code>Type</code> objects that identify the constructor's formal
     * parameter types, in declared order.
     *
     * @param \KM\Lang\Reflect\Type[] $parameterTypes The parameter array.
     * @return \KM\Lang\Reflect\Constructor the <code>Constructor</code> object
     *         for the constructor with the specified parameter list.
     * @throws NoSuchMethodException if a matching method is not found.
     */
    public function getDeclaredConstructor(array $parameterTypes = null)
    {
        return $this->getConstructor0($parameterTypes, Member::DECLARED_MEMBER);
    }

    /**
     * Returns the class constructor.
     *
     * @param boolean $publicOnly Specifies whether only a public constructor
     *        should be returned.
     * @return \KM\Lang\Reflect\Constructor The <code>Constructor</code> object
     *         representing the class constructor.
     */
    private function privateDeclaredConstructor($publicOnly)
    {
        $res = null;
        if (!$this->isInterface()) {
            $res = $this->getDeclaredConstructor0($publicOnly);
        }
        return $res;
    }

    private function getConstructor0(array $parameterTypes = null, $which)
    {
        $ctor = $this->privateDeclaredConstructor(
            ($which == Member::PUBLIC_MEMBER));
        if ($ctor != null) {
            if (self::arrayContentsEq($parameterTypes, $ctor->getParameters())) {
                return $ctor;
            }
        }
        throw new NoSuchMethodException($this->getName() . '<init>');
    }

    private function getDeclaredConstructor0($publicOnly)
    {
        // Native Java code
        try {
            $ctor = $this->root->getConstructor();
            if ($ctor === null || ($publicOnly && !$ctor->isPublic()) ||
                 $this->isAbstract()) {
                return null;
            }
            return new Constructor($this, $ctor);
        } catch (InstantiationException $e) {
            return null;
        }
    }

    /**
     * Returns the <code>Clazz</code> object for the named primitive type.
     *
     * @param string $name The name of the primitive type for which to return a
     *        <code>Clazz</code> object.
     * @throws NullPointerException if the specified <code>name</code> is
     *         <code>null</code>.
     * @throws IllegalArgumentException if <code>name</code> does not specify is
     *         legal primitive type.
     * @return \KM\Lang\Clazz the <code>Clazz</code> object that defines the
     *         specified primitive type.
     */
    public static function getPrimitiveClass($name)
    {
        return PrimitiveType::value($name)->getClass();
    }

    /**
     * Returns the <code>Clazz</code> object for the named mixed type.
     *
     * @param string $name The name of the mixed type for which to return a
     *        <code>Clazz</code> object.
     * @throws NullPointerException if the specified <code>name</code> is
     *         <code>null</code>.
     * @throws IllegalArgumentException if <code>name</code> does not specify is
     *         legal mixed type.
     * @return \KM\Lang\Clazz the <code>Clazz</code> object that defines the
     *         specified mixed type.
     */
    public static function getMixedClass($name)
    {
        return MixedType::value($name)->getClass();
    }

    private static function arrayContentsEq(array $a1 = null, array $a2 = null)
    {
        /* @var $p Parameter */
        if ($a1 === null) {
            if ($a2 === null || count($a2) == 0) {
                return true;
            } else {
                // Check for optional parameters
                foreach ($a2 as $p) {
                    if (!$p->isOptional()) {
                        return false;
                    }
                }
                return true;
            }
        }
        if ($a2 === null) {
            return count($a1) == 0;
        }
        if (count($a1) != count($a2)) {
            return false;
        }
        for ($i = 0; $i < count($a1); $i++) {
            $p = $a2[$i];
            if (($p != null) && ($a1[$i] != $p->getType())) {
                return false;
            }
        }
        return true;
    }
    
    /* ---------- Enum Methods ---------- */
    
    /**
     * Returns true if and only if this class was declared as an enum in the
     * source code.
     *
     * @return boolean True if and only if this class was declared as an enum in
     *         the source code,
     */
    public function isEnum()
    {
        return $this->root->isSubclassOf('\KM\Lang\Enum');
    }

    /**
     * Returns the elements of this enum class or null if this Class object does
     * not represent an enum type.
     *
     * @return \KM\Lang\Enum[] An array containing the values comprising the
     *         enum class represented by this Class object in the order they are
     *         declared, or null if this Class object does not represent an enum
     *         type.
     */
    public function getEnumConstants()
    {
        if ($this->isEnum()) {
            $m = $this->root->getMethod('getEnumConstants');
            return $m->invoke(null);
        }
        return null;
    }

    /**
     * Returns the elements of this enum class or null if this Class object does
     * not represent an enum type; identical to getEnumConstants except that the
     * result is cached, and shared by all callers.
     *
     * @return \KM\Lang\Enum[] An array containing the values comprising the
     *         enum class represented by this Class object in the order they are
     *         declared, or null if this Class object does not represent an enum
     *         type.
     */
    public function getEnumConstantsShared()
    {
        if ($this->isEnum()) {
            $m = $this->root->getMethod('getEnumConstantsShared');
            $m->setAccessible(true);
            return $m->invoke(null);
        }
        return null;
    }

    /**
     * Returns a map from simple name to enum constant.
     * This package-private
     * method is used internally by Enum to implement {@code public static <T
     * extends Enum<T>> T valueOf(Class<T>, String)} efficiently. Note that the
     * map is returned by this method is created lazily on first use. Typically
     * it won't ever get created.
     *
     * @return \KM\Lang\Enum[] An array containing the values comprising the
     *         enum class represented by this Class object in the order they are
     *         declared, or null if this Class object does not represent an enum
     *         type.
     */
    public function getEnumConstantDirectory()
    {
        if ($this->isEnum()) {
            $m = $this->root->getMethod('getEnumConstantDirectory');
            $m->setAccessible(true);
            return $m->invoke(null);
        }
        return null;
    }
    
    /* ---------- Annotation Methods ---------- */
    
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
        return $this->getAnnotationReader()->getClassAnnotation($this->root,
            $annotationName) != null;
    }

    /**
     * Returns this element's annotation for the specified type if such an
     * annotation is present, else null.
     *
     * @param string $annotationName The type of the annotation to query for and
     *        return if present.
     * @return \Doctrine\Common\Annotations\Annotation The element's annotation
     *         for the specified annotation type if present on this element,
     *         else null.
     * @see \KM\Lang\Reflect\AnnotatedElement::getAnnotation()
     */
    public function getAnnotation($annotationName)
    {
        return $this->getAnnotationReader()->getClassAnnotation($this->root,
            $annotationName);
    }

    /**
     * Returns annotation that are applied to the entity represented by this
     * <code>Clazz</code> object.
     * If there are no annotations on this element,
     * the return value is an array of length 0.
     *
     * @return \Doctrine\Common\Annotations\Annotation[] Annotations present on
     *         this element.
     * @see \KM\Lang\Reflect\AnnotatedElement::getAnnotations()
     */
    public function getAnnotations()
    {
        return $this->getAnnotationReader()->getClassAnnotations($this->root);
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
    
    /* ---------- Miscellaneous PHP Reflection Methods ---------- */
    
    /**
     * Returns the doc comments for this class.
     *
     * @return string The doc comments for this class.
     */
    public function getDocComment()
    {
        return $this->root->getDocComment();
    }

    /**
     * Returns the filename of the file in which the class has been defined.
     *
     * @return string The filename of the file in which the class has been
     *         defined, or <code>null</code> if the class is defined in the PHP
     *         core or in a PHP extension.
     */
    public function getFileName()
    {
        $fname = $this->root->getFileName();
        if ($fname === false) {
            return null;
        }
        return $fname;
    }

    /**
     * Returns the starting line number of the file in which this class is
     * defined.
     *
     * @return int The starting line number of the file in which this class is
     *         defined.
     */
    public function getStartLine()
    {
        return $this->root->getStartLine();
    }

    /**
     * Returns the end line number from a user-defined class definition.
     *
     * @return int The ending line number of the user defined class, or -1 if
     *         unknown.
     */
    public function getEndLine()
    {
        $endline = $this->root->getEndLine();
        if ($endline === false) {
            $endline = -1;
        }
        return $endline;
    }

    /**
     * Returns an <code>Extension</code> wrapping a \ReflectionExtension that
     * represents the extension which defined this class.
     * This method returns null for user-defined classes.
     *
     * @return unknown \KM\Lang\Reflect\Extension An <code>Extension</code>
     *         object representing the extension which defined this class, or
     *         <code>null</code> for user-defined classes.
     */
    public function getExtension()
    {
        $ext = $this->root->getExtension();
        if ($ext === null) {
            return $null;
        }
        return new Extension($ext->getName());
    }

    /**
     * Returns the name of the extension which defined this class.
     *
     * @return string The name of the extension which defined the class, or
     *         <code>null</code> for user-defined classes.
     */
    public function getExtensionName()
    {
        $name = $this->root->getExtensionName();
        if ($name === false) {
            return null;
        }
        return $name;
    }
}
?>