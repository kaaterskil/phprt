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

use KM\IO\InvalidClassException;
use KM\IO\ObjectStreamClass\ClassDataSlot;
use KM\IO\ObjectStreamClass\ExceptionInfo;
use KM\IO\ObjectStreamClass\FieldReflector;
use KM\IO\ObjectStreamConstants;
use KM\IO\Serializable;
use KM\Lang\Clazz;
use KM\Lang\Enum;
use KM\Lang\InstantiationException;
use KM\Lang\NoSuchMethodException;
use KM\Lang\Object;
use KM\Lang\Reflect\Constructor;
use KM\Lang\Reflect\Field;
use KM\Lang\Reflect\InvocationTargetException;
use KM\Lang\Reflect\Method;
use KM\Lang\Reflect\MixedType;
use KM\Lang\Reflect\PrimitiveType;
use KM\Lang\Reflect\Proxy;
use KM\Lang\Reflect\Type;
use KM\Lang\RuntimeException;
use KM\Lang\System;
use KM\Lang\UnsupportedOperationException;
use KM\Lang\Void;
use KM\Util\ArrayList;
use KM\Util\Arrays;
use KM\Util\HashMap;
use KM\Util\HashSet;
use KM\Util\Map;

/**
 * Serialization's descriptor for classes.
 *
 * @author Blair
 */
class ObjectStreamClass extends Object implements Serializable
{

    /**
     * Value indicating no serializable fields.
     *
     * @var ObjectStreamField[]
     */
    public static $NO_FIELDS;

    /**
     * The descriptor cache
     *
     * @var Map
     */
    private static $cache;

    /**
     * Static constructor
     */
    public static function clinit()
    {
        self::$NO_FIELDS = [];
        self::$cache = new HashMap('<string, \KM\IO\ObjectStreamClass>');
    }

    /**
     * The class associated with this descriptor
     *
     * @var Clazz
     */
    private $cl;

    /**
     * Name of the class represented by this descriptor.
     *
     * @var string
     */
    private $name;

    /**
     * True if represents dynamic proxy class
     *
     * @var boolean
     */
    private $isProxy;

    /**
     * True if represents an Enum type
     *
     * @var boolean
     */
    private $isEnum;

    /**
     * True if represented class implements Serializable.
     *
     * @var boolean
     */
    private $serializable;

    /**
     * True if descriptor has data written by class-defined writeObject method.
     *
     * @var boolean
     */
    private $hasWriteObjectData;

    /**
     * Exception (if any) to throw if non-enum deserialization attempted.
     *
     * @var ExceptionInfo
     */
    private $deserializeEx;

    /**
     * Exception (if any) to throw if non-enum serialization attempted.
     *
     * @var ExceptionInfo
     */
    private $serializeEx;

    /**
     * Exception (if any) to throw if default serialization attempted.
     *
     * @var ExceptionInfo
     */
    private $defaultSerializeEx;

    /**
     * Serializable fields.
     *
     * @var ObjectStreamField[]
     */
    private $fields;

    /**
     * Number of primitive fields.
     *
     * @var int
     */
    private $primDataSize;

    /**
     * Number of non-primitive fields.
     *
     * @var int
     */
    private $numObjFields;

    /**
     * Reflector for setting/getting serializable field values.
     *
     * @var FieldReflector
     */
    private $fieldRefl;

    /**
     * Data layout of serialized objects described by this class descriptor.
     *
     * @var \KM\IO\ObjectStreamClass\ClassDataSlot[]
     */
    private $dataLayout;

    /**
     * Serialization-appropriate constructor, or null if none.
     *
     * @var Constructor
     */
    private $cons;

    /**
     * Class-defined writeObject method, or null if none.
     *
     * @var Method
     */
    private $writeObjectMethod;

    /**
     * Class-defined readObject method, or null if none.
     *
     * @var Method
     */
    private $readObjectMethod;

    /**
     * Local class descriptor for represented class (may point to self).
     *
     * @var ObjectStreamClass
     */
    private $localDesc;

    /**
     * Superclass descriptor appearing in stream.
     *
     * @var ObjectStreamClass
     */
    private $superDesc;

    /**
     * Returns the name of the class described by this descriptor.
     *
     * @return string A string representing the name of the class
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the class in the local VM that this version is mapped to.
     * Null is
     * returned if there is no corresponding local class.
     *
     * @return \KM\Lang\Clazz The <code>Clazz</code> instance that this
     *         descriptor represents.
     */
    public function forClass()
    {
        if ($this->cl == null) {
            return null;
        }
        return $this->cl;
    }

    /**
     * Returns a string describing this ObjectStreamClass.
     *
     * @return string
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Find the descriptor for a class that can be serialized.
     * Creates an
     * ObjectStreamClass instance if one does not exist yet for class. Null is
     * returned if the specified class does not implement KM\IO\Serializable
     *
     * @param Clazz $cl
     * @param boolean $all If true, returns descriptors for all classes. If
     *        false, only return descriptors for serializable classes.
     * @return \KM\IO\ObjectStreamClass
     */
    public static function lookup(Clazz $cl, $all = true)
    {
        /* @var $entry ObjectStreamClass */
		/* @var $ex RuntimeException */
        if (!($all || $cl->implementsInterface('\KM\IO\Serializable'))) {
            return null;
        }
        
        $ex = null;
        $key = System::identityHashCode($cl);
        $entry = self::$cache->get($key);
        if ($entry == null) {
            try {
                $entry = new self($cl);
                self::$cache->put($key, $entry);
            } catch (RuntimeException $e) {
                $ex = $e;
            } catch (\Exception $e) {
                $ex = $e;
            }
        }
        if ($ex == null) {
            return $entry;
        } else {
            throw $ex;
        }
    }

    /**
     * Creates blank class descriptor which should be initialized via a
     * subsequent call to initProxy(), initNonProxy() or readNonProxy().
     *
     * @return \KM\IO\ObjectStreamClass
     */
    public static function getInstance()
    {
        return new self(null);
    }

    /**
     * Creates local class descriptor representing given class.
     *
     * @param Clazz $cl
     */
    private function __construct(Clazz $cl = null)
    {
        if ($cl != null) {
            $this->cl = $cl;
            $this->name = $cl->getName();
            $this->isProxy = Proxy::isProxyClass($cl);
            $this->isEnum = Enum::clazz()->isAssignableFrom($cl);
            $this->serializable = $cl->implementsInterface(
                '\KM\IO\Serializable');
            
            $superCl = $cl->getSuperclass();
            $this->superDesc = ($superCl != null) ? self::lookup($superCl,
                false) : null;
            $this->localDesc = $this;
            
            if ($this->serializable) {
                $this->init($cl);
            } else {
                $this->fields = self::$NO_FIELDS;
            }
            
            try {
                $this->fieldRefl = self::getReflector($this->fields, $this);
            } catch (InvalidClassException $e) {
                // Field matches are impossible when matching local fields with
                // self.
                trigger_error('internal error');
            }
            
            if ($this->deserializeEx == null) {
                if ($this->isEnum) {
                    $this->deserializeEx = new ExceptionInfo($this->name,
                        'enum type');
                } elseif ($this->cons == null) {
                    /*
                     * The Java method here does not hold for PHP. We have the
                     * ability to instantiate an object through Reflection
                     * without invoking its constructor. The lack of a no-arg
                     * constructor in a non-serializable super-class does not
                     * prevent us from deserializing the persisted object.
                     */
                    // $this->deserializeEx = new ExceptionInfo($this->name, 'no
                    // valid constructor');
                }
            }
            for ($i = 0; $i < count($this->fields); $i++) {
                if ($this->fields[$i]->getField() == null) {
                    $this->defaultSerializeEx = new ExceptionInfo($this->name,
                        'unmatched serializable field(s) declared');
                }
            }
        }
    }

    private function init(Clazz $cl)
    {
        if ($this->isEnum) {
            $this->fields = self::$NO_FIELDS;
            return null;
        }
        try {
            $this->fields = self::getSerialFields($cl);
            $this->computeFieldOffsets();
        } catch (InvalidClassException $e) {
            $this->serializeEx = $this->deserializeEx = new ExceptionInfo(
                $e->className, $e->getMessage());
            $this->fields = self::$NO_FIELDS;
        }
        $this->cons = self::getSerializableConstructor($cl);
        $this->writeObjectMethod = self::getPrivateMethod($cl, 'writeObject');
        $this->readObjectMethod = self::getPrivateMethod($cl, 'readObject');
        $this->hasWriteObjectData = ($this->writeObjectMethod != null);
    }

    /**
     * Initializes class descriptor representing a proxy class.
     *
     * @param Clazz $cl
     * @param ObjectStreamClass $superDesc
     */
    public function initProxy(Clazz $cl = null, ObjectStreamClass $superDesc = null)
    {
        $this->cl = $cl;
        $this->superDesc = $superDesc;
        $this->isProxy = true;
        $this->serializable = true;
        $this->fields = self::$NO_FIELDS;
        
        if ($cl != null) {
            $this->localDesc = self::lookup($cl, true);
            if (!$this->localDesc->isProxy) {
                throw new InvalidClassException(
                    'cannot bind proxy descriptor to a non-proxy class');
            }
            $this->name = $this->localDesc->name;
            $this->cons = $this->localDesc->cons;
            $this->deserializeEx = $this->localDesc->deserializeEx;
        }
        $this->fieldRefl = self::getReflector($this->fields, $this->localDesc);
    }

    /**
     * Initializes class descriptor representing a non-proxy class.
     *
     * @param ObjectStreamClass $model
     * @param Clazz $cl
     * @param ObjectStreamClass $superDesc
     */
    public function initNonProxy(ObjectStreamClass $model, Clazz $cl = null,
        ObjectStreamClass $superDesc = null)
    {
        $this->cl = $cl;
        $this->superDesc = $superDesc;
        $this->name = $model->name;
        $this->isProxy = false;
        $this->isEnum = $model->isEnum;
        $this->serializable = $model->serializable;
        $this->hasWriteObjectData = $model->hasWriteObjectData;
        $this->fields = $model->fields;
        $this->primDataSize = $model->primDataSize;
        $this->numObjFields = $model->numObjFields;
        
        if ($cl != null) {
            $this->localDesc = self::lookup($cl, true);
            if ($this->localDesc->isProxy) {
                throw new InvalidClassException(
                    'cannot bind non-proxy descriptor to a proxy class');
            }
            if ($this->isEnum != $this->localDesc->isEnum) {
                $message = $this->isEnum ? 'cannot bind enum descriptor to a non-enum class' : 'cannot bind non-enum descriptor to an enum class';
                throw new InvalidClassException($message);
            }
            if ($this->name != $this->localDesc->name) {
                throw new InvalidClassException(
                    'local class name incompatible with stream class name "' .
                         $this->name . '"');
            }
            if (!$this->isEnum) {
                if (($this->serializable != $this->localDesc->serializable) ||
                     !($this->serializable)) {
                    $this->deserializeEx = new ExceptionInfo(
                        $this->localDesc->name,
                        'class invalid for deserialization');
                }
            }
            $this->cons = $this->localDesc->cons;
            $this->writeObjectMethod = $this->localDesc->writeObjectMethod;
            $this->readObjectMethod = $this->localDesc->readObjectMethod;
            if ($this->deserializeEx == null) {
                $this->deserializeEx = $this->localDesc->deserializeEx;
            }
        }
        $this->fieldRefl = self::getReflector($this->fields, $this->localDesc);
        // Reassign to matched fields so as to reflect local settings
        $this->fields = $this->fieldRefl->getFields();
    }

    /**
     * Reads non-proxy class descriptor information from given input stream.
     * The resulting class descriptor is not fully functional; it can only be
     * used as input to the ObjectInputStream.resolveClass() and
     * ObjectStreamClass.initNonProxy() methods.
     *
     * @param ObjectInputStream $in
     */
    public function readNonProxy(ObjectInputStream $in)
    {
        $this->name = $in->readUTF();
        $this->isProxy = false;
        
        $flags = ord($in->readSingleByte());
        $this->hasWriteObjectData = (($flags &
             ObjectStreamConstants::SC_WRITE_METHOD) != 0);
        $this->serializable = (($flags & ObjectStreamConstants::SC_SERIALIZABLE) !=
             0);
        $this->isEnum = (($flags & ObjectStreamConstants::SC_ENUM) != 0);
        
        $numFields = $in->readShort();
        if ($this->isEnum && $numFields != 0) {
            throw new InvalidClassException(
                'enum descriptor has non-zero field count');
        }
        $this->fields = ($numFields > 0) ? array_fill(0, $numFields, null) : self::$NO_FIELDS;
        for ($i = 0; $i < $numFields; $i++) {
            $tc = $in->readSingleByte();
            $tcode = chr(unpack('C*', $tc)[1]);
            
            $fname = $in->readUTF();
            $signature = ($tcode == 'L') ? $in->readTypeString() : $tcode;
            
            try {
                $this->fields[$i] = ObjectStreamField::forName($fname,
                    $signature, false);
            } catch (RuntimeException $e) {
                throw new InvalidClassException(
                    'invalid descriptor for field ' . $fname, $this->name);
            }
        }
        $this->computeFieldOffsets();
    }

    /**
     * Writes non-proxy class descriptor information to given output stream.
     *
     * @param ObjectOutputStream $out
     */
    public function writeNonProxy(ObjectOutputStream $out)
    {
        /* @var $f ObjectStreamField */
        $out->writeUTF($this->name);
        
        $flags = 0;
        if ($this->serializable) {
            $flags |= ObjectStreamConstants::SC_SERIALIZABLE;
        }
        if ($this->hasWriteObjectData) {
            $flags |= ObjectStreamConstants::SC_WRITE_METHOD;
        }
        if ($this->isEnum) {
            $flags |= ObjectStreamConstants::SC_ENUM;
        }
        $out->writeSingleByte($flags);
        
        $out->writeShort(count($this->fields));
        for ($i = 0; $i < count($this->fields); $i++) {
            $f = $this->fields[$i];
            $out->writeSingleByte(ord($f->getTypeCode()));
            $out->writeUTF($f->getName());
            if (!$f->isPrimitive()) {
                $out->writeTypeString($f->getTypeString());
            }
        }
    }

    /**
     * Throws an InvalidClassException if object instances referencing this
     * class descriptor should not be allowed to deserialize.
     * This method does
     * not apply to deserialization of enum constants.
     *
     * @throws InvalidClassException
     */
    public function checkDeserialize()
    {
        if ($this->deserializeEx != null) {
            throw $this->deserializeEx->newInvalidClassException();
        }
    }

    /**
     * Throws an InvalidClassException if objects whose class is represented by
     * this descriptor should not be allowed to serialize.
     * This method does not
     * apply to serialization of enum constants.
     *
     * @throws InvalidClassException
     */
    public function checkSerialize()
    {
        if ($this->serializeEx != null) {
            throw $this->serializeEx->newInvalidClassException();
        }
    }

    /**
     * Throws an InvalidClassException if objects whose class is represented by
     * this descriptor should not be permitted to use default serialization
     * (e.g., if the class declares serializable fields that do not correspond
     * to actual fields, and hence must use the GetField API).
     * This method does
     * not apply to deserialization of enum constants.
     *
     * @throws InvalidClassException
     */
    public function checkDefaultSerialize()
    {
        if ($this->defaultSerializeEx != null) {
            throw $this->defaultSerializeEx->newInvalidClassException();
        }
    }

    /**
     * Returns superclass descriptor.
     * Note that on the receiving side, the
     * superclass descriptor may be bound to a class that is not a superclass of
     * the subclass descriptor's bound class.
     *
     * @return \KM\IO\ObjectStreamClass
     */
    public function getSuperDesc()
    {
        return $this->superDesc;
    }

    /**
     * Returns the "local" class descriptor for the class associated with this
     * class descriptor (i.e., the result of
     * ObjectStreamClass.lookup(this.forClass())) or null if there is no class
     * associated with this descriptor.
     *
     * @return \KM\IO\ObjectStreamClass
     */
    public function getLocalDesc()
    {
        return $this->localDesc;
    }

    /**
     * Returns an array of ObjectStreamFields representing the serializable
     * fields of the represented class.
     * If copy is true, a clone of this class descriptor's field array is
     * returned, otherwise the array itself is returned.
     *
     * @param boolean $copy If true, a clone of this descriptor's field array is
     *        returned, otherwise the array itself is returned.
     * @return \KM\IO\ObjectStreamField[]
     */
    public function getFields($copy = true)
    {
        if ($copy) {
            $res = [];
            foreach ($this->fields as $f) {
                $res[] = $f;
            }
            return $res;
        }
        return $this->fields;
    }

    /**
     * Looks up a serializable field of the represented class by name and type.
     * A specified type of null matches all types, Object.class matches all
     * non-primitive types, and any other non-null type matches assignable types
     * only. Returns matching field, or null if no match found.
     *
     * @param string $name
     * @param Type $type
     * @return \KM\IO\ObjectStreamField
     */
    public function getField($name, Type $type = null)
    {
        /* @var $f ObjectStreamField */
        for ($i = 0; $i < count($this->fields); $i++) {
            $f = $this->fields[$i];
            if ($f->getName() == $name) {
                if ($type == null ||
                     ($type == Object::clazz() && !$f->isPrimitive())) {
                    return $f;
                }
                $ftype = $f->getType();
                if ($ftype != null && $type == $ftype) {
                    return $f;
                }
            }
        }
        return null;
    }

    /**
     * Returns true if class descriptor represents a dynamic proxy class, false
     * otherwise.
     *
     * @return boolean
     */
    public function isProxy()
    {
        return $this->isProxy;
    }

    /**
     * Returns true if class descriptor represents an enum type, false
     * otherwise.
     *
     * @return boolean
     */
    public function isEnum()
    {
        return $this->isEnum;
    }

    /**
     * Returns true if represented class implements Serializable, false
     * otherwise.
     *
     * @return boolean
     */
    public function isSerializable()
    {
        return $this->serializable;
    }

    /**
     * Returns true if class descriptor represents serializable class which has
     * written its data via a custom writeObject() method, false otherwise.
     *
     * @return boolean
     */
    public function hasWriteObjectData()
    {
        return $this->hasWriteObjectData;
    }

    /**
     * Returns true if represented class is serializable and can be instantiated
     * by the serialization runtime--i.e., if its first non-serializable
     * superclass defines an accessible no-arg constructor.
     * Otherwise, returns
     * false.
     *
     * NOTE: Unlike Java, PHP has the ability via Reflection to instantiate
     * objects without invoking their constructor and does not require the
     * existence of a no-arg constructor in the first non-serializable parent
     * class. We instead invoke the <code>isInstantiable<code> method on the
     * class object.
     *
     * @return boolean
     */
    public function isInstantiable()
    {
        // return ($this->cons != null);
        return $this->cl->isInstantiable();
    }

    /**
     * Returns true if represented class is serializable and defines a
     * conforming writeObject method.
     *
     * @return boolean
     */
    public function hasWriteObjectMethod()
    {
        return ($this->writeObjectMethod != null);
    }

    /**
     * Returns true if represented class is serializable and defines a
     * conforming readObject method.
     *
     * @return boolean
     */
    public function hasReadObjectMethod()
    {
        return ($this->readObjectMethod != null);
    }

    /**
     * Creates a new instance of the represented class.
     * If the class is serializable, invokes the no-arg constructor of the first
     * non-serializable superclass. Throws UnsupportedOperationException if this
     * class descriptor is not associated with a class, if the associated class
     * is non-serializable or if the appropriate no-arg constructor is
     * inaccessible/unavailable.
     *
     * @return \KM\Lang\Object
     */
    public function newInstance()
    {
        if ($this->cons != null) {
            try {
                return $this->cons->newInstance();
            } catch (\ReflectionException $e) {
                // Should not occur as access checks have been suppressed.
                trigger_error(__METHOD__ . ': internal error');
            }
        } else {
            /*
             * Unlike Java, we have the ability to instantiate an object via
             * Reflection without invoking its constructor. We do so here.
             */
            // throw new UnsupportedOperationException();
            try {
                return $this->cl->newInstanceWithoutConstructor();
            } catch (InstantiationException $e) {
                trigger_error(__METHOD__ . ': internal error');
            }
        }
    }

    /**
     * Invokes the writeObject method of the represented serializable class.
     *
     * @param Object $obj
     * @param ObjectOutputStream $out
     * @throws IOException if an invocation error occurs.
     * @throws UnsupportedOperationException if this class descriptor is not
     *         associated with a class, or if the class is not serializable or
     *         does not define writeObject.
     */
    public function invokeWriteObject(Object $obj, ObjectOutputStream $out)
    {
        if ($this->writeObjectMethod != null) {
            try {
                $arg[] = $out;
                $this->writeObjectMethod->invoke($obj, $arg);
            } catch (InvocationTargetException $ex) {
                $th = $ex->getPrevious();
                if ($th instanceof IOException) {
                    throw $th;
                } else {
                    self::throwMiscException($th);
                }
            } catch (\ReflectionException $e) {
                self::throwMiscException($e);
            }
        } else {
            throw new UnsupportedOperationException();
        }
    }

    /**
     * Invokes the readObject method of the represented serializable class.
     *
     * @param Object $obj
     * @param ObjectInputStream $in
     * @throws IOException if an invocation error occurs.
     * @throws UnsupportedOperationException if this class descriptor is not
     *         associated with a class, or if the class is not serializable or
     *         does not define readObject.
     */
    public function invokeReadObject(Object $obj, ObjectInputStream $in)
    {
        if ($this->readObjectMethod != null) {
            try {
                $arg[] = $in;
                $this->readObjectMethod->invoke($obj, $arg);
            } catch (InvocationTargetException $ex) {
                $th = $ex->getPrevious();
                if ($th instanceof IOException) {
                    throw $th;
                } else {
                    self::throwMiscException($th);
                }
            } catch (\ReflectionException $e) {
                self::throwMiscException($e);
            }
        } else {
            throw new UnsupportedOperationException();
        }
    }

    /**
     * Returns array of ClassDataSlot instances representing the data layout
     * (including superclass data) for serialized objects described by this
     * class descriptor.
     * ClassDataSlots are ordered by inheritance with those containing "higher"
     * superclasses appearing first. The final ClassDataSlot contains a
     * reference to this descriptor.
     *
     * @return \KM\IO\ObjectStreamClass\ClassDataSlot[]
     */
    public function getClassDataLayout()
    {
        if ($this->dataLayout == null) {
            $this->dataLayout = $this->getClassDataLayout0();
        }
        return $this->dataLayout;
    }

    private function getClassDataLayout0()
    {
        /* @var $c Clazz */
		/* @var $d ObjectStreamClass */
		/* @var $match Clazz */
		$slots = new ArrayList('\KM\IO\ObjectStreamClass\ClassDataSlot');
        $start = $this->cl;
        $end = $this->cl;
        
        // Locate closest non=serializable superclass
        while ($end != null && $end->implementsInterface('\KM\IO\Serializable')) {
            $end = $end->getSuperclass();
        }
        
        $oscNames = new HashSet('string');
        for ($d = $this; $d != null; $d = $d->superDesc) {
            if ($oscNames->contains($d->name)) {
                throw new InvalidClassException('circular reference');
            } else {
                $oscNames->add($d->name);
            }
            
            // Search up the inheritance hierarchy for class with matching name.
            $searchName = ($d->cl != null) ? $d->cl->getName() : $d->name;
            $match = null;
            for ($c = $start; $c != $end; $c = $c->getSuperclass()) {
                if ($searchName == $c->getName()) {
                    $match = $c;
                    break;
                }
            }
            // Add 'no-data' slot for each unmatched class below match
            if ($match != null) {
                for ($c = $start; $c != $match; $c = $c->getSuperclass()) {
                    $slots->add(
                        new ClassDataSlot(ObjectStreamClass::lookup($c, true),
                            false));
                }
                $start = $match->getSuperclass();
            }
            // Record descriptor/class pairing
            $slots->add(new ClassDataSlot($d->getVariantFor($match), true));
        }
        // Add 'no-data' slot for any leftover unmatched classes
        for ($c = $start; $c != $end; $c = $c->getSuperclass()) {
            $slots->add(
                new ClassDataSlot(ObjectStreamClass::lookup($c, true), false));
        }
        // Order slots from superclass -> subclass
        $res = array_reverse($slots->toArray());
        return $res;
    }

    /**
     * Returns the aggregate size (in bytes) of marshalled primitive field
     * values for the represented class.
     *
     * @return int
     */
    public function getPrimDataSize()
    {
        return $this->primDataSize;
    }

    /**
     * Returns the number of non-primitive serializable fields of the
     * represented class.
     *
     * @return int
     */
    public function getNumObjectFields()
    {
        return $this->numObjFields;
    }

    /**
     * Fetches the serializable primitive field values of object
     * <code>obj</code> and marshals them into byte array <code>buf</code>
     * starting at offset 0.
     * It is the responsibility of the caller to ensure that <code>obj</code> is
     * of the proper type if non-null.
     *
     * @param Object $obj
     * @param array $buf
     */
    public function getPrimFieldValues(Object $obj, array &$buf)
    {
        $this->fieldRefl->getPrimFieldValues($obj, $buf);
    }

    /**
     * Sets the serializable primitive fields of object <code>obj</code> using
     * values unmarshalled from byte array <code>buf</code> starting at offset
     * 0.
     * It is the responsibility of the caller to ensure that <code>obj</code>
     * is of the proper type if non-null.
     *
     * @param Object $obj
     * @param array $buf
     */
    public function setPrimFieldValues(Object $obj, array &$buf)
    {
        $this->fieldRefl->setPrimFieldValues($obj, $buf);
    }

    /**
     * Fetches the serializable object field values of object <code>obj</code>
     * and stores them in array <code>vals</code> starting at offset 0.
     * It is
     * the responsibility of the caller to ensure that <code>obj</code> is of
     * the proper type if non-null.
     *
     * @param Object $obj
     * @param array $vals
     */
    public function getObjFieldValues(Object $obj, array &$vals)
    {
        $this->fieldRefl->getObjFieldValues($obj, $vals);
    }

    /**
     * Sets the serializable object fields of object <code>obj</code> using
     * values from array <code>vals</code> starting at offset 0.
     * It is the responsibility of the caller to ensure that <code>obj</code> is
     * of the proper type if non-null.
     *
     * @param Object $obj
     * @param array $vals
     */
    public function setObjFieldValues(Object $obj, array &$vals)
    {
        $this->fieldRefl->setObjFieldValues($obj, $vals);
    }

    /**
     * Calculates and sets serializable field offsets, as well as primitive data
     * size and object field count totals.
     *
     * @throws \KM\IO\InvalidClassException if fields are illegally ordered.
     */
    private function computeFieldOffsets()
    {
        /* @var $f ObjectStreamField */
        $this->primDataSize = 0;
        $this->numObjFields = 0;
        $firstObjIndex = -1;
        
        for ($i = 0; $i < count($this->fields); $i++) {
            $f = $this->fields[$i];
            switch ($f->getTypeCode()) {
                case 'Z':
                    $f->setOffset($this->primDataSize++);
                    break;
                
                case 'S':
                case 'J':
                case 'D':
                case 'I':
                case 'F':
                    // PHP treats short and long types as a 32-bit (4-byte)
                    // integer, and the double type as a 32-bit (4-byte) float.
                    $f->setOffset($this->primDataSize);
                    $this->primDataSize += 4;
                    break;
                
                case '[':
                case 'C':
                case 'M':
                case 'N':
                case 'L':
                    // We group arrays, strings and mixed types with objects
                    // since they are of variable length. The defaultWriteFields
                    // and defaultReadFields methods will parse the types into
                    // the different write/read methods.
                    $f->setOffset($this->numObjFields++);
                    if ($firstObjIndex == -1) {
                        $firstObjIndex = $i;
                    }
                    break;
                
                default:
                    trigger_error('Invalid type');
            }
        }
        if (($firstObjIndex != -1) &&
             ($firstObjIndex + $this->numObjFields != count($this->fields))) {
            throw new InvalidClassException('Illegal field order', $this->name);
        }
    }

    /**
     * If given class is the same as the class associated with this class
     * descriptor, returns reference to this class descriptor.
     * Otherwise, returns variant of this class descriptor bound to given class.
     *
     * @param Clazz $cl
     * @return \KM\IO\ObjectStreamClass
     */
    private function getVariantFor(Clazz $cl = null)
    {
        if ($this->cl == $cl) {
            return $this;
        }
        $desc = new ObjectStreamClass();
        if ($this->isProxy) {
            $desc->initProxy($cl, $this->superDesc);
        } else {
            $desc->initNonProxy($this, $cl, $this->superDesc);
        }
        return $desc;
    }

    /**
     * Returns subclass-accessible no-arg constructor of first non-serializable
     * superclass, or null if none found.
     * Access checks are disabled on the returned constructor (if any).
     *
     * @param Clazz $cl
     * @return \KM\Lang\ReflectMethod
     */
    private static function getSerializableConstructor(Clazz $cl)
    {
        /* @var $cons Method */
        $initCl = $cl;
        while ($initCl->implementsInterface('\KM\IO\Serializable')) {
            if (($initCl = $initCl->getSuperclass()) == null) {
                return null;
            }
        }
        try {
            $cons = $initCl->getDeclaredConstructor();
            if ($cons != null) {
                if (!$cons->isPublic()) {
                    $cons->setAccessible(true);
                }
            }
            return $cons;
        } catch (NoSuchMethodException $e) {
            return null;
        }
    }

    /**
     * Returns non-static private method with the given name defined by the the
     * given class, or null if none found.
     *
     * @param Clazz $cl
     * @param string $name
     * @return \KM\Lang\Reflect\Method>
     */
    private static function getPrivateMethod(Clazz $cl, $name)
    {
        /* @var $method Method */
        try {
            $method = $cl->getDeclaredMethod($name);
            $method->setAccessible(true);
            return (!$method->isStatic() && $method->isPrivate()) ? $method : null;
        } catch (NoSuchMethodException $ex) {
            return null;
        }
    }

    /**
     * Returns the type signature for the given Type object.
     *
     * @param Type $type
     * @return string
     */
    public static function getTypeSignature(Type $type)
    {
        $sb = '';
        while ($type->isArray()) {
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
            } elseif ($type == PrimitiveType::FLOAT()) {
                $sb .= 'F';
            } elseif ($type = PrimitiveType::LONG()) {
                $sb .= 'J';
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

    /**
     * Convenience method for throwing an exception that is either a
     * RuntimeException or of some unexpected type, in which case it is wrapped
     * in an IOException.
     *
     * @param \Exceptioon $th
     */
    private static function throwMiscException(\Exception $th)
    {
        if ($th instanceof RuntimeException) {
            throw $th;
        } else {
            throw new IOException('unexpected exception type', $th);
        }
    }

    /**
     * Returns an ObjectStreamField array describing the serializable fields of
     * the given class.
     * Serializable fields backed by an actual field of the class are
     * represented by ObjectStreamFields with corresponding non-null field
     * objects.
     *
     * @param Clazz $cl
     * @return \KM\IO\ObjectStreamField[]
     */
    private static function getSerialFields(Clazz $cl)
    {
        /* @var $fields ObjectStreamField[] */
        $fields = null;
        if (!$cl->isInterface() &&
             $cl->implementsInterface('\KM\IO\Serializable') && !Proxy::isProxyClass(
                $cl)) {
            $fields = self::getDefaultSerialFields($cl);
            Arrays::sort($fields);
        } else {
            $fields = self::$NO_FIELDS;
        }
        return $fields;
    }

    /**
     * Returns an array of ObjectStreamFields corresponding to all non-static
     * non-transient fields declared in the given class.
     *
     * Each ObjectStreamField contains a field object for the field it
     * represents. If no default serializable fields exist, NO_FIELDS is
     * returned.
     *
     * @param Clazz $cl
     * @return \KM\IO\ObjectStreamField[]
     */
    private static function getDefaultSerialFields(Clazz $cl)
    {
        /* @var $field Field */
        $clFields = $cl->getDeclaredFields();
        $list = [];
        for ($i = 0; $i < count($clFields); $i++) {
            $field = $clFields[$i];
            if (!$field->isStatic() && !$field->isTransient()) {
                $list[] = new ObjectStreamField($field, false);
            }
        }
        $size = count($list);
        return ($size == 0) ? self::$NO_FIELDS : $list;
    }

    /**
     * Matches given set of serializable field with serializable fields
     * described by the given local class descriptor and returns a
     * FieldReflector instance capable of setting/getting values form the subset
     * of fields that match.
     *
     * Non-matching fields are treated as filler, for which get operations
     * return default values and set operations discard given values. Throws
     * InvalidClassException if unresolvable type conflicts exist between the
     * two sets of fields.
     *
     * @param array $fields
     * @param ObjectStreamClass $localDesc
     * @throws Exception
     * @return \KM\IO\ObjectStreamClass\FieldReflector
     */
    private static function getReflector(array $fields,
        ObjectStreamClass $localDesc)
    {
        // Class is irrelevant if no fields
        $cl = ($localDesc != null && count($fields) > 0) ? $localDesc->cl : null;
        
        $entry = null;
        try {
            $entry = new FieldReflector(self::matchFields($fields, $localDesc));
        } catch (\Exception $e) {
            throw $e;
        }
        return $entry;
    }

    /**
     * Matches given set of serializable fields with serializable fields
     * obtained from given local class descriptor (which contains bindings to
     * reflective field objects).
     *
     * Returns a list of ObjectStreamFields in which each ObjectStreamField
     * whose signature matches that of a local field contains a Field object for
     * that field; unmatched fields contain null field objects.
     *
     * @param ObjectStreamField[] $fields
     * @param ObjectStreamClass $localDesc
     * @throws InvalidClassException
     * @return \KM\IO\ObjectStreamField[]
     */
    private static function matchFields(array $fields,
        ObjectStreamClass $localDesc)
    {
        /* @var $f ObjectStreamField */
		/* @var $m ObjectStreamField */
		/* @var $lf ObjectStreamField */
		
		$localFields = ($localDesc != null) ? $localDesc->fields : self::$NO_FIELDS;
        
        $matches = [];
        for ($i = 0; $i < count($fields); $i++) {
            $f = $fields[$i];
            $m = null;
            for ($j = 0; $j < count($localFields); $j++) {
                $lf = $localFields[$j];
                if ($f->getName() == $lf->getName()) {
                    if (($f->isPrimitive() || $lf->isPrimitive()) &&
                         $f->getTypeCode() != $lf->getTypeCode()) {
                        throw new InvalidClassException(
                            'incompatible types for field ' . $f->getName(),
                            $localDesc->name);
                    }
                    if ($lf->getField() != null) {
                        $m = new ObjectStreamField($lf->getField(),
                            $lf->isUnshared());
                    } else {
                        $m = ObjectStreamField::forName($lf->getName(),
                            $lf->getSignature(), $lf->isUnshared());
                    }
                }
            }
            if ($m == null) {
                $m = ObjectStreamField::forName($f->getName(),
                    $f->getSignature(), false);
            }
            $m->setOffset($f->getOffset());
            $matches[$i] = $m;
        }
        return $matches;
    }
}
?>