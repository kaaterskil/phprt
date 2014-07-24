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
namespace KM\IO;

use KM\Lang\ClassCastException;
use KM\Lang\Clazz;
use KM\Lang\Comparable;
use KM\Lang\IllegalArgumentException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\Reflect\Field;
use KM\Lang\Reflect\MixedType;
use KM\Lang\Reflect\PrimitiveType;
use KM\Lang\Reflect\Type;
use KM\Lang\Void;
use KM\Lang\Reflect\ArrayType;

/**
 * A description of a Serializable field from a Serializable class.
 * An array of ObjectStreamFields is used to declare the Serializable fields of
 * a class.
 *
 * @author Blair
 */
class ObjectStreamField extends Object implements Comparable
{

    /**
     * The field name
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $signature;

    /**
     * Field type (Clazz if not a primitive type)
     *
     * @var Type
     */
    private $type;

    /**
     * Whether or not to deserialize field values as unshared.
     *
     * @var boolean
     */
    private $unshared;

    /**
     * The reflective field object
     *
     * @var Field
     */
    private $field;

    /**
     * Offset of field value in enclosing field group.
     *
     * @var int
     */
    private $offset = 0;

    /**
     * Creates an ObjectStreamField representing a serializable field with the
     * given name and type.
     * If unshared is false, values of the represented field are serialized and
     * deserialized in the default manner - if the field is non-primitive,
     * object values are serializes and deserialized as if they had been written
     * and read by calls to writeObject and readObject. If unshared is true,
     * values of the represented field are serialized and deserialized as if
     * they had been written and read by calls to writeUnshared and
     * readUnshared.
     *
     * @param name $name The field name.
     * @param Type $type The field type.
     * @param string $unshared If false, write/read field values in the same
     *        manner as writeObject/readObject; if true, write/read in the
     *        same manner as writeUnshared/readUnshared.
     * @return \KM\IO\ObjectStreamField
     */
    public static function forType($name, Type $type, $unshared = false)
    {
        /* @var $instance ObjectStreamField */
        $cl = Clazz::forName('\KM\IO\ObjectStreamField');
        $instance = $cl->newInstanceWithoutConstructor();
        $instance->name = (string) $name;
        $instance->type = $type;
        $instance->unshared = (boolean) $unshared;
        $instance->signature = self::getClassSignature($type);
        $instance->field = null;
        return $instance;
    }

    /**
     * Returns an ObjectStreamField representing a field with the given name,
     * signature and unshared setting.
     *
     * @param string $name
     * @param string $signature
     * @param boolean $unshared
     * @throws IllegalArgumentException
     * @return \KM\IO\ObjectStreamField
     */
    public static function forName($name, $signature, $unshared = false)
    {
        /* @var $instance ObjectStreamField */
        if ($name == null) {
            throw new NullPointerException();
        }
        $clazz = Clazz::forName('\KM\IO\ObjectStreamField');
        $instance = $clazz->newInstanceWithoutConstructor();
        $instance->name = (string) $name;
        $instance->signature = (string) $signature;
        $instance->unshared = (boolean) $unshared;
        $instance->field = null;
        
        switch ($signature[0]) {
            case 'Z':
                $instance->type = PrimitiveType::BOOLEAN();
                break;
            case 'S':
                $instance->type = PrimitiveType::SHORT();
                break;
            case 'I':
                $instance->type = PrimitiveType::INTEGER();
                break;
            case 'J':
                $instance->type = PrimitiveType::LONG();
                break;
            case 'F':
                $instance->type = PrimitiveType::FLOAT();
                break;
            case 'D':
                $instance->type = PrimitiveType::DOUBLE();
                break;
            case 'C':
                $instance->type = PrimitiveType::STRING();
                break;
            case 'M':
                $instance->type = MixedType::MIXED();
                break;
            case 'N':
                $instance->type = MixedType::NUMBER();
                break;
            case '[':
                $instance->type = PrimitiveType::ARRAY_TYPE();
                break;
            case 'L':
                $instance->type = Object::clazz();
                break;
            default:
                throw new IllegalArgumentException('Illegal signature');
        }
        return $instance;
    }

    /**
     * Creates an ObjectStreamField representing a serializable field with the
     * given name and type.
     *
     * @param Field $field The reflected field object.
     * @param boolean $unshared If false, write/read field values in the same
     *        manner as writeObject/readObject. If true, write/read in the
     *        same manner as writeUnshared/readUnshared.
     */
    public function __construct(Field $field, $unshared)
    {
        $this->field = $field;
        $this->name = $field->getName();
        $this->type = $field->getType();
        $this->unshared = (boolean) $unshared;
        $this->signature = self::getClassSignature($field->getType());
    }

    /**
     * Returns the name of this field.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the type of this field.
     * If the type is non-primitive, the
     * <code>Clazz</code> object for this type of field is returned.
     *
     * @return \KM\Lang\Reflect\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the character encoding of the field type.
     * The encoding is as follows:
     * <blockquote><pre>
     * C string
     * D double (float in PHP)
     * F float
     * J long (integer in PHP)
     * I integer
     * S short (integer in PHP)
     * L class or interface
     * Z boolean
     * M mixed (pseudo code)
     * N number (pseudo code)
     * [ array
     * </pre></blockquote>
     *
     * @return string The type code of the serializable field.
     */
    public function getTypeCode()
    {
        return $this->signature[0];
    }

    /**
     * Returns the type signature for non-primitive types.
     *
     * @return string <code>null</code> if this field is a primitive type.
     */
    public function getTypeString()
    {
        return $this->isPrimitive() ? null : $this->signature;
    }

    /**
     * Offset of field within instance data.
     *
     * @return int The offset of this field.
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Offset within instance data.
     *
     * @param int $offset The offset of the field within its class.
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
    }

    /**
     * Returns <code>true</code> if this field has a primitive type.
     *
     * @return boolean <code>True</code> if and only if this field has a
     *         primitive type.
     */
    public function isPrimitive()
    {
        return $this->type->isPrimitive();
    }

    /**
     * Returns <code>true</code> if this field has a array type.
     *
     * @return boolean <code>True</code> if and only if this field has a
     *         array type.
     */
    public function isArray()
    {
        return $this->type->isArray();
    }

    /**
     * Returns <code>true</code> if this field has a mixed type.
     *
     * @return boolean <code>True</code> if and only if this field has a
     *         mixed type.
     */
    public function isMixed()
    {
        return $this->type->isMixed();
    }

    /**
     * Returns boolean value indicating whether or not the serializable field
     * represented by this ObjectStreamField instance is unshared.
     *
     * @return boolean <code>True</code> if this field is unshared.
     */
    public function isUnshared()
    {
        return $this->unshared;
    }

    /**
     * Compares this field with another <code>ObjectStreamField</code>.
     * Returns -1 if this is small, 0 if equal, 1 if greater. Types that are
     * primitive are "smaller" than object types. If equal, the field names are
     * compared.
     *
     * @param Object $obj
     * @throws ClassCastException
     * @return int
     * @see \KM\Lang\Comparable::compareTo()
     */
    public function compareTo(Object $obj = null)
    {
        /* @var $other ObjectStreamField */
        if ($obj == null || !$obj instanceof ObjectStreamField) {
            throw new ClassCastException();
        }
        $other = $obj;
        $isPrim = $this->isPrimitive();
        if ($isPrim != $other->isPrimitive()) {
            return $isPrim ? -1 : 1;
        }
        if ($this->name < $other->name) {
            return -1;
        }
        return ($this->name > $other->name) ? 1 : 0;
    }

    /**
     * Returns a string that describes this field.
     *
     * @return string
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        return $this->signature . ' ' . $this->name;
    }

    /**
     * Returns the field represented by this ObjectStreamField, or null if
     * ObjectStreamField is not associated with an actual field.
     *
     * @return \KM\Lang\Reflect\Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Returns the type signature of the field (similar to getTypeString, except
     * that signature strings are returned for primitive fields as well).
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Returns the type signature for the given type.
     *
     * @param Type $type
     * @return string
     */
    private static function getClassSignature(Type $type)
    {
        $sb = '';
        
        while ($type instanceof ArrayType) {
            $sb .= '[';
            $type = $type->getComponentType();
        }
        
        if ($type->isPrimitive()) {
            if ($type == PrimitiveType::BOOLEAN()) {
                $sb .= 'Z';
            } elseif ($type == PrimitiveType::SHORT()) {
                $sb .= 'S';
            } elseif ($type == PrimitiveType::INTEGER()) {
                $sb .= 'I';
            } elseif ($type == PrimitiveType::LONG()) {
                $sb .= 'J';
            } elseif ($type == PrimitiveType::FLOAT()) {
                $sb .= 'F';
            } elseif ($type == PrimitiveType::DOUBLE()) {
                $sb .= 'D';
            } elseif ($type == PrimitiveType::STRING()) {
                $sb .= 'C';
            } elseif ($type == Void::TYPE()) {
                $sb .= 'V';
            } else {
                trigger_error('internal error');
            }
        } elseif ($type->isMixed()) {
            if ($type == MixedType::MIXED()) {
                $sb .= 'M';
            } elseif ($type == MixedType::NUMBER()) {
                $sb .= 'N';
            }
        } else {
            $sb .= 'L' . $type->getTypeName() . ';';
        }
        return $sb;
    }
}
?>