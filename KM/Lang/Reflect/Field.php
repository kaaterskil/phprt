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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Annotation;
use KM\Lang\Clazz;
use KM\Lang\Enum;
use KM\Lang\IllegalArgumentException;
use KM\Lang\InstantiationException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;

/**
 * A <code>Field<code> provides information about and dynamic access to a
 * single field of a class or interface.
 * The reflected field may be a static or instance field, or primitive type.
 *
 * @author Blair
 */
final class Field extends Object implements AccessibleObject, Member
{

    /**
     * The declaring class
     *
     * @var Clazz
     */
    private $clazz;

    /**
     * The field name
     *
     * @var string
     */
    private $name;

    /**
     * The field type
     *
     * @var Type
     */
    private $type;

    /**
     * The backing reflector
     *
     * @var \ReflectionProperty
     */
    private $root;

    /**
     * The annotation reader
     *
     * @var AnnotationReader
     */
    private $reader;

    /**
     * Constructs a new Field with the specified declaring class object and
     * field name.
     *
     * @param Clazz $declaringClass The <code>Clazz</code> object that
     *        represents the class that declares the specified field.
     * @param string $name The name of the field.
     * @throws InstantiationException if the <code>Field</code> object could not
     *         be created for any reason.
     */
    public function __construct(Clazz $declaringClass, $name)
    {
        $name = (string) $name;
        try {
            $this->clazz = $declaringClass;
            $this->name = $name;
            $this->root = $declaringClass->getReflector()->getProperty($name);
            $this->type = $this->getType0();
        } catch (\ReflectionException $e) {
            throw new InstantiationException($name);
        }
    }

    /**
     * Returns the <code>Clazz</code> object representing the class or interface
     * that declares the field represented by this <code>Field</code> object.
     *
     * @return \KM\Lang\Clazz The <code>Clazz</code> object representing the
     *         class or interface that declares the field represented by this
     *         <code>Field</code> object.
     * @see \KM\Lang\Reflect\Member::getDeclaringClass()
     */
    public function getDeclaringClass()
    {
        return $this->clazz;
    }

    /**
     * Returns the name of the field represented by this <code>Field</code>
     * object.
     *
     * @return string The name of the field represented by this
     *         <code>Field</code> object.
     * @see \KM\Lang\Reflect\Member::getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the modifiers for the field represented by this
     * <code>Field</code> object as an integer.
     * The bitmask includes the PHP constants IS_STATIC, IS_PUBLIC, IS_PROTECTED
     * and IS_PRIVATE.
     *
     * @return int The bitmask of the modifiers for this field.
     * @see \KM\Lang\Reflect\Member::getModifiers()
     */
    public function getModifiers()
    {
        return $this->root->getModifiers();
    }

    /**
     * Returns <code>true</code> if this field represents an element of an
     * enumerated type, <code>false</code> otherwise.
     *
     * @return boolean <code>True</code> if and only if this field represents an
     *         element of an enumerated type.
     */
    public function isEnumConstant()
    {
        /* @var $cl Clazz */
        if ($this->getType() instanceof Clazz) {
            $cl = $this->type;
            return $cl->isSubclassOf('\KM\Lang\Enum');
        }
        return false;
    }

    /**
     * Returns a <code>Type</code> object that identifies the declared type for
     * the field represented by this <code>Field</code> object.
     *
     * @return \KM\Lang\Reflect\Type A <code>Type</code> object identifying the
     *         declared type of the field represented by this object.
     */
    public function getType()
    {
        if ($this->type === null) {
            $this->type = $this->getType0();
        }
        return $this->type;
    }

    /**
     * Returns the <code>Type</code> object representing the type of this field.
     * The type is computed by introspection of the <code>var</code> annotation
     * on the field declaration. If <code>var</code> is not defined or is null,
     * a primitive string type is assigned to this field. Otherwise, the method
     * will attempt to return a class object or primitive type object for the
     * declared type name.
     *
     * @return \KM\Lang\Reflect\Type A <code>Type</code> object representing the
     *         type of this field.
     * @throws InstantiationException if the <code>Type</code> object could not
     *         be created for any reason.
     */
    private function getType0()
    {
        $typePart = strstr($this->root->getDocComment(), '@var');
        if ($typePart === false) {
            return PrimitiveType::STRING();
        } else {
            $parts = explode(' ', trim(str_replace('@var', '', $typePart)));
            return ReflectionUtility::typeFor(trim($parts[0]));
        }
    }

    /**
     * Compares this <code>Field</code> against the specified object.
     * Returns true of the objects are the same. The <code>Field</code> objects
     * are the same if they were declared by the same class and have the same
     * name and type.
     *
     * @param Object $obj The object to compare.
     * @return boolean <code>True</code> if the the two objects are the same.
     * @see \KM\Lang\Object::equals()
     */
    public function equals(Object $obj = null)
    {
        /* @var $other Field */
        if ($obj !== null && $obj instanceof Field) {
            $other = $obj;
            return ($this->getDeclaringClass() == $other->getDeclaringClass()) &&
                 ($this->getName() == $other->getName()) && ($this->getType() == $other->getType());
        }
        return false;
    }

    /**
     * Returns a string describing this <code>Field</code>.
     * The format is the access modifiers for the field, if any, followed by the
     * field type, followed by a space, followed by the fully-qualified name of
     * the class declaring the field, followed by a period, followed by the name
     * of the field.
     *
     * @return string A string describing this <code>Field</code>.
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        $sb = ReflectionUtility::printModifiersIfNonZero($this->getModifiers());
        $sb .= (($type = $this->getType()) != null) ? $type->getName() . ' ' : '';
        $sb .= $this->getDeclaringClass()->getName() . '.' . $this->getName();
        return $sb;
    }

    /**
     * Returns the value of the field represented by this <code>Field</code> on
     * the specified object.
     *
     * @param Object $obj The object from which the represented field's value is
     *        to be extracted.
     * @throws IllegalArgumentException if the specified object is not an
     *         instance of the class or interface declaring the underlying
     *         field.
     * @return mixed The value of the represented field in object
     *         <code>obj</code>.
     */
    public function get(Object $obj)
    {
        $cname = $this->clazz->getName();
        if (!($obj instanceof $cname)) {
            throw new IllegalArgumentException();
        }
        $this->root->setAccessible(true);
        return $this->root->getValue($obj);
    }

    /**
     * Sets the field represented by this <code>Field</code> object on the
     * specified object argument to the specified new value.
     *
     * @param Object $obj The object whose field should be modified.
     * @param mixed $value The new value for the field of <code>obj</code> being
     *        modified.
     * @throws IllegalArgumentException if the specified object is not an
     *         instance of the class or interface declaring the underlying
     *         field.
     */
    public function set(Object $obj, $value)
    {
        $cname = $this->clazz->getName();
        if (!($obj instanceof $cname)) {
            throw new IllegalArgumentException();
        }
        $this->root->setAccessible(true);
        $this->root->setValue($obj, $value);
    }

    /**
     * Sets the accessibility of the field represented by this
     * <code>Field</code> object.
     *
     * @param boolean $flag <code>True</code> to allow accessibility, or
     *        <code>false</code> otherwise.
     * @see \KM\Lang\Reflect\AccessibleObject::setAccessible()
     */
    public function setAccessible($flag)
    {
        $this->root->setAccessible($flag);
    }

    /**
     * Checks if the field represented by this <code>Field</code> object is
     * private.
     *
     * @return boolean <code>True</code> if the field represented by this object
     *         is private, <code>false</code> otherwise.
     * @see \KM\Lang\Reflect\AccessibleObject::isPrivate()
     */
    public function isPrivate()
    {
        return $this->root->isPrivate();
    }

    /**
     * Checks if the field represented by this <code>Field</code> object is
     * protected.
     *
     * @return boolean <code>True</code> if the field represented by this object
     *         is protected, <code>false</code> otherwise.
     * @see \KM\Lang\Reflect\AccessibleObject::isProtected()
     */
    public function isProtected()
    {
        return $this->root->isProtected();
    }

    /**
     * Checks if the field represented by this <code>Field</code> object is
     * public.
     *
     * @return boolean <code>True</code> if the field represented by this object
     *         is public, <code>false</code> otherwise.
     * @see \KM\Lang\Reflect\AccessibleObject::isPublic()
     */
    public function isPublic()
    {
        return $this->root->isPublic();
    }

    /**
     * Checks if the field represented by this <code>Field</code> object is
     * static.
     *
     * @return boolean <code>True</code> if the field represented by this object
     *         is static, <code>false</code> otherwise.
     */
    public function isStatic()
    {
        return $this->root->isStatic();
    }

    /**
     * Returns true if the field represented by this <code>Field</code> object
     * was declared at compile time.
     *
     * @return boolean <code>True</code> if this field was declared at compile
     *         time, <code>false</code> if it was declared during run-time.
     */
    public function isDefault()
    {
        return $this->root->isDefault();
    }

    /**
     * Checks whether the field represented by this <code>Field</code> object is
     * final.
     * PHP fields cannot be declared final so this method always returns
     * <code>false</code>.
     *
     * @return boolean <code>True</code> if this field is declared final,
     *         <code>false</code> otherwise.
     * @see \KM\Lang\Reflect\Member::isFinal()
     */
    public function isFinal()
    {
        return false;
    }

    /**
     * checks whether the field represented by this <code>Field</code> object is
     * transient and should not be persisted.
     *
     * @return boolean <code>True</code> if the field represented by this object
     *         is transient, <code>false</code> otherwise.
     */
    public function isTransient()
    {
        return $this->isAnnotationPresent('\KM\IO\Transient');
    }

    public function isAnnotationPresent($annotationName)
    {
        if ($annotationName === null) {
            throw new NullPointerException();
        }
        return $this->getAnnotationReader()->getPropertyAnnotation($this->root, $annotationName) !=
             null;
    }

    public function getAnnotation($annotationName)
    {
        if ($annotationName === null) {
            throw new NullPointerException();
        }
        return $this->getAnnotationReader()->getPropertyAnnotation($this->root, $annotationName);
    }

    public function getAnnotations()
    {
        return $this->getAnnotationReader()->getPropertyAnnotations($this->root);
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

    /**
     * Returns the doc comment from the declaring class for the field
     * represented by this <code>Field</code> object.
     *
     * @return string The doc comment for this field, or null if none exists.
     */
    public function getDocComment()
    {
        return $this->root->getDocComment();
    }
}
?>