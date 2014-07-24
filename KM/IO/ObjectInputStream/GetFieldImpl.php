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
namespace KM\IO\ObjectInputStream;

use KM\IO\ObjectStreamClass;
use KM\IO\ObjectStreamField;
use KM\Lang\Reflect\Type;
use KM\Lang\IllegalArgumentException;
use KM\IO\ObjectInputStream;
use KM\Lang\Reflect\PrimitiveType;
use KM\IO\Bits;

/**
 * Default GetField implementation.
 *
 * @author Blair
 */
class GetFieldImpl extends GetField
{

    /**
     * Class descriptor describing serializable fields.
     *
     * @var ObjectStreamClass
     */
    private $desc;

    /**
     * Primitive field values
     *
     * @var mixed[]
     */
    private $primVals;

    /**
     * Object field values
     *
     * @var mixed[]
     */
    private $objVals;

    /**
     * Object field value handles
     *
     * @var int[]
     */
    private $objHandles;

    /**
     * The backing object input stream
     *
     * @var ObjectInputStream
     */
    private $ois;

    public function __construct(ObjectStreamClass $desc, ObjectInputStream $ois)
    {
        $this->ois = $ois;
        $this->desc = $desc;
        $this->primVals = ($desc->getPrimDataSize() == 0) ? [] : array_fill(0,
            $desc->getPrimDataSize(), null);
        $this->objVals = ($desc->getNumObjectFields() == 0) ? [] : array_fill(0,
            $desc->getNumObjectFields(), null);
        $this->objHandles = (count($this->objVals) == 0) ? [] : array_fill(0,
            count($this->objVals), null);
    }

    public function getObjectStreamClass()
    {
        return $this->desc;
    }

    public function defaulted($name)
    {
        return ($this->getFieldOffset($name, null) < 0);
    }

    public function getBoolean($name, $val)
    {
        $off = $this->getFieldOffset($name, PrimitiveType::BOOLEAN());
        return ($off >= 0) ? Bits::getBoolean($this->primVals, $off) : $val;
    }

    public function getShort($name, $val)
    {
        $off = $this->getFieldOffset($name, PrimitiveType::SHORT());
        return ($off >= 0) ? Bits::getInt($this->primVals, $off) : $val;
    }

    public function getInt($name, $val)
    {
        $off = $this->getFieldOffset($name, PrimitiveType::INTEGER());
        return ($off >= 0) ? Bits::getInt($this->primVals, $off) : $val;
    }

    public function getLong($name, $val)
    {
        $off = $this->getFieldOffset($name, PrimitiveType::LONG());
        return ($off >= 0) ? Bits::getInt($this->primVals, $off) : $val;
    }

    public function getFloat($name, $val)
    {
        $off = $this->getFieldOffset($name, PrimitiveType::FLOAT());
        return ($off >= 0) ? Bits::getFloat($this->primVals, $off) : $val;
    }

    public function getDouble($name, $val)
    {
        $off = $this->getFieldOffset($name, PrimitiveType::DOUBLE());
        return ($off >= 0) ? Bits::getFloat($this->primVals, $off) : $val;
    }

    public function getString($name, $val)
    {
        $off = $this->getFieldOffset($name, null);
        if ($off >= 0) {
            $objHandle = $this->objHandles[$off];
            $this->ois->handles->markDependency($this->ois->passHandle,
                $objHandle);
            return ($this->ois->handles->lookupException($objHandle) == null) ? $this->objVals[$off] : null;
        } else {
            return $val;
        }
    }

    public function getMixed($name, $val)
    {
        $off = $this->getFieldOffset($name, null);
        if ($off >= 0) {
            $objHandle = $this->objHandles[$off];
            $this->ois->handles->markDependency($this->ois->passHandle,
                $objHandle);
            return ($this->ois->handles->lookupException($objHandle) == null) ? $this->objVals[$off] : null;
        } else {
            return $val;
        }
    }

    public function getArray($name, $val)
    {
        $off = $this->getFieldOffset($name, null);
        if ($off >= 0) {
            $objHandle = $this->objHandles[$off];
            $this->ois->handles->markDependency($this->ois->passHandle,
                $objHandle);
            return ($this->ois->handles->lookupException($objHandle) == null) ? $this->objVals[$off] : null;
        } else {
            return $val;
        }
    }

    public function getObject($name, $val)
    {
        $off = $this->getFieldOffset($name, null);
        if ($off >= 0) {
            $objHandle = $this->objHandles[$off];
            $this->ois->handles->markDependency($this->ois->passHandle,
                $objHandle);
            return ($this->ois->handles->lookupException($objHandle) == null) ? $this->objVals[$off] : null;
        } else {
            return $val;
        }
    }

    /**
     * Reads primitive and object field values from the stream.
     */
    public function readFields()
    {
        /* @var $f ObjectStreamField */
        $this->ois->readFully($this->primVals, 0, count($this->primVals));
        
        $oldHandle = $this->ois->passHandle;
        $fields = $this->desc->getFields(false);
        $numPrimFields = count($fields) - count($this->objVals);
        for ($i = 0; $i < count($this->objVals); $i++) {
            $f = $fields[$numPrimFields + $i];
            if ($f->isArray()) {
                $this->objVals[$i] = $this->ois->readArray($f->isUnshared());
            } elseif ($f->isMixed()) {
                $this->objVals[$i] = $this->ois->readMixed($f->isUnshared());
            } else {
                $this->objVals[$i] = $this->ois->readObject0($f->isUnshared());
            }
            $this->objHandles[$i] = $this->ois->passHandle;
        }
        $this->ois->passHandle = $oldHandle;
    }

    /**
     * Returns offset of field with given name and type.
     * A specified type of null matches all types, Object.class matches all
     * non-primitive types, and any other non-null type matches assignable types
     * only. If no matching field is found in the (incoming) class descriptor
     * but a matching field is present in the associated local class descriptor,
     * returns -1. Throws IllegalArgumentException if neither incoming nor local
     * class descriptor contains a match.
     *
     * @param string $name
     * @param Type $type
     * @throws IllegalArgumentException
     * @return int
     */
    private function getFieldOffset($name, Type $type = null)
    {
        /* @var $field ObjectStreamField */
        $field = $this->desc->getField($name, $type);
        if ($field != null) {
            return $field->getOffset();
        } elseif ($this->desc->getLocalDesc()->getField($name, $type) != null) {
            return -1;
        } else {
            $format = 'no such field %s with type %s';
            throw new IllegalArgumentException(sprintf($format, $name, $type));
        }
    }
}
?>