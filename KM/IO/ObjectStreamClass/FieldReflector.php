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
namespace KM\IO\ObjectStreamClass;

use KM\IO\Bits;
use KM\IO\ObjectStreamClass;
use KM\IO\ObjectStreamField;
use KM\Lang\Clazz;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\Reflect\Field;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Lang\Reflect\Type;
use KM\Util\ArrayList;
use Sun\Misc\Unsafe;

/**
 * Class for setting and retrieving serializable field values in batch.
 *
 * @author Blair
 */
class FieldReflector extends Object
{

    /**
     * Handle for performing unsafe operations.
     *
     * @var Unsafe
     */
    private static $unsafe;

    /**
     * Static constructor.
     */
    public static function clinit()
    {
        self::$unsafe = Unsafe::getUnsafe();
    }

    /**
     * Fields upon which to operate.
     *
     * @var ObjectStreamField[]
     */
    private $fields;

    /**
     * The number of primitive fields
     *
     * @var int
     */
    private $numPrimFields;

    /**
     * Unsafe field keys for reading and writing fields
     *
     * @var string[]
     */
    private $keys;

    /**
     * Field data offsets
     *
     * @var int[]
     */
    private $offsets;

    /**
     * Field type codes
     *
     * @var string[]
     */
    private $typeCodes;

    /**
     * Field types
     *
     * @var Type[]
     */
    private $types;

    /**
     * Constructs a FieldReflector capable of setting/getting values from the
     * subset of fields whose ObjectStreamFields contain non-null reflection
     * field objects.
     * ObjectStreamFields with null fields are treated as filler
     * for which get operations return default values and set operations discard
     * given values.
     *
     * @param array $fields An array of ObjectStreamFields.
     */
    public function __construct(array $fields)
    {
        /* @var $f ObjectStreamField */
        $this->fields = $fields;
        
        $typeList = new ArrayList('\KM\Lang\Reflect\Type');
        
        $nFields = count($fields);
        for ($i = 0; $i < $nFields; $i++) {
            $f = $fields[$i];
            $rf = $f->getField();
            
            $key = $rf->getName();
            $this->keys[$i] = $key;
            
            $this->offsets[$i] = $f->getOffset();
            $this->typeCodes[$i] = $f->getTypeCode();
            if (!$f->isPrimitive()) {
                $typeList->add(($rf != null) ? $rf->getType() : null);
            }
        }
        $this->types = $typeList->toArray();
        $this->numPrimFields = $nFields - count($this->types);
    }

    /**
     * Returns list of ObjectStreamFields representing fields operated on by
     * this reflector.
     *
     * @return \KM\IO\ObjectStreamField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Fetches the serializable primitive field values of object
     * <code>obj</code> and marshals them into byte array <code>buf</code>
     * starting at offset 0.
     * The caller is responsible for ensuring that
     * <code>obj</code> is of the proper type.
     *
     * @param Object $obj
     * @param array $buf
     * @throws NullPointerException
     */
    public function getPrimFieldValues(Object $obj, array &$buf)
    {
        if ($obj == null) {
            throw new NullPointerException();
        }
        for ($i = 0; $i < $this->numPrimFields; $i++) {
            $key = $this->keys[$i];
            $off = $this->offsets[$i];
            switch ($this->typeCodes[$i]) {
                case 'Z':
                    Bits::putBoolean($buf, $off,
                        self::$unsafe->getBoolean($obj, $key));
                    break;
                
                case 'I':
                    Bits::putInt($buf, $off, self::$unsafe->getInt($obj, $key));
                    break;
                
                case 'F':
                    Bits::putFloat($buf, $off,
                        self::$unsafe->getFloat($obj, $key));
                    break;
                
                case 'D':
                    Bits::putFloat($buf, $off,
                        self::$unsafe->getDouble($obj, $key));
                    break;
                
                default:
                    trigger_error('invalid type');
            }
        }
    }

    /**
     * Sets the serializable primitive fields of object <code>obj</code> using
     * values unmarshalled from byte array <code>buf</code> starting at offset
     * 0.
     * The caller is responsible for ensuring that <code>obj</code> is of the
     * proper type.
     *
     * @param Object $obj
     * @param array $buf
     * @throws NullPointerException
     */
    public function setPrimFieldValues(Object $obj, array &$buf)
    {
        if ($obj == null) {
            throw new NullPointerException();
        }
        for ($i = 0; $i < $this->numPrimFields; $i++) {
            $key = $this->keys[$i];
            $off = $this->offsets[$i];
            switch ($this->typeCodes[$i]) {
                case 'Z':
                    self::$unsafe->putBoolean($obj, $key,
                        Bits::getBoolean($buf, $off));
                    break;
                
                case 'I':
                    self::$unsafe->putInt($obj, $key, Bits::getInt($buf, $off));
                    break;
                
                case 'F':
                    self::$unsafe->putFloat($obj, $key,
                        Bits::getFloat($buf, $off));
                    break;
                
                case 'D':
                    self::$unsafe->putDouble($obj, $key,
                        Bits::getFloat($buf, $off));
                    break;
                
                default:
                    trigger_error('invalid type');
            }
        }
    }

    /**
     * Fetches the serializable object field values of object <code>obj</code>
     * and stores them in array <code>vals</code> starting at offset 0.
     * The
     * caller is responsible for ensuring that <code>obj</code> is of the proper
     * type.
     *
     * @param Object $obj
     * @param array $vals
     * @throws NullPointerException
     */
    public function getObjFieldValues(Object $obj, array &$vals)
    {
        if ($obj == null) {
            throw new NullPointerException();
        }
        for ($i = $this->numPrimFields; $i < count($this->fields); $i++) {
            $key = $this->keys[$i];
            switch ($this->typeCodes[$i]) {
                case 'L':
                    $vals[$this->offsets[$i]] = self::$unsafe->getObject($obj,
                        $key);
                    break;
                case '[':
                    $vals[$this->offsets[$i]] = self::$unsafe->getArray($obj,
                        $key);
                    break;
                case 'C':
                    $vals[$this->offsets[$i]] = self::$unsafe->getString($obj,
                        $key);
                    break;
                case 'M':
                    $vals[$this->offsets[$i]] = self::$unsafe->getMixed($obj,
                        $key);
                    break;
                default:
                    trigger_error('invalid type');
            }
        }
    }

    /**
     * Sets the serializable object fields of object <code>obj</code> using
     * values from array <code>vals</code> starting at offset 0.
     * The caller is
     * responsible for ensuring that <code>obj</code> is of the proper type;
     * however, attempts to set a field with a value of the wrong type will
     * trigger an appropriate ClassCastException.
     *
     * @param Object $obj
     * @param array $vals
     * @throws NullPointerException
     * @throws ClassCastException
     */
    public function setObjFieldValues(Object $obj, array &$vals)
    {
        /* @var $type Type */
		/* @var $clazz Clazz */
		/* @var $f Field */
		if ($obj == null) {
            throw new NullPointerException();
        }
        for ($i = $this->numPrimFields; $i < count($this->fields); $i++) {
            $key = $this->keys[$i];
            $val = $vals[$this->offsets[$i]];
            switch ($this->typeCodes[$i]) {
                case 'L':
                    $valType = ($val != null) ? ReflectionUtility::typeForValue($val) : null;
                    $clazz = $this->types[$i - $this->numPrimFields];
                    if ($val != null && !$clazz->isInstance($val)) {
                        $f = $this->fields[$i]->getField();
                        $format = 'cannot assign instance of %s to field %s.%s of type %s in instance of %s';
                        throw new ClassCastException(
                            sprintf($format, $valType->getTypeName(),
                                $f->getDeclaringClass()->getName(),
                                $f->getName(), $f->getType()->getTypeName(),
                                $obj->getClass()->getName()));
                    }
                    self::$unsafe->putObject($obj, $key, $val);
                    break;
                case '[':
                    $type = $this->types[$i - $this->numPrimFields];
                    $valType = ($val != null) ? ReflectionUtility::typeForValue($val) : null;
                    if ($val != null && $type != $valType) {
                        $f = $this->fields[$i]->getField();
                        $format = 'cannot assign instance of %s to field %s.%s of type %s in instance of %s';
                        throw new ClassCastException(
                            sprintf($format, $valType->getTypeName(),
                                $f->getDeclaringClass()->getName(),
                                $f->getName(), $f->getType()->getTypeName(),
                                $obj->getClass()->getName()));
                    }
                    self::$unsafe->putArray($obj, $key, $val);
                    break;
                case 'C':
                    $type = $this->types[$i - $this->numPrimFields];
                    $valType = ($val != null) ? ReflectionUtility::typeForValue($val) : null;
                    if ($val != null &&
                         $type->getTypeName() != $valType->getTypeName()) {
                        $f = $this->fields[$i]->getField();
                        $format = 'cannot assign instance of %s to field %s.%s of type %s in instance of %s';
                        throw new ClassCastException(
                            sprintf($format, $valType->getTypeName(),
                                $f->getDeclaringClass()->getName(),
                                $f->getName(), $f->getType()->getTypeName(),
                                $obj->getClass()->getName()));
                    }
                    self::$unsafe->putString($obj, $key, $val);
                    break;
                case 'M':
                    $type = $this->types[$i - $this->numPrimFields];
                    $valType = ($val != null) ? ReflectionUtility::typeForValue($val) : null;
                    if ($val != null &&
                         $type->getTypeName() != $valType->getTypeName()) {
                        $f = $this->fields[$i]->getField();
                        $format = 'cannot assign instance of %s to field %s.%s of type %s in instance of %s';
                        throw new ClassCastException(
                            sprintf($format, $valType->getTypeName(),
                                $f->getDeclaringClass()->getName(),
                                $f->getName(), $f->getType()->getTypeName(),
                                $obj->getClass()->getName()));
                    }
                    self::$unsafe->putMixed($obj, $key, $val);
                    break;
                default:
                    trigger_error('invalid type');
            }
        }
    }
}
?>