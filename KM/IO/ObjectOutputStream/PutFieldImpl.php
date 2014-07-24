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
namespace KM\IO\ObjectOutputStream;

use KM\IO\ObjectStreamClass;
use KM\IO\Bits;
use KM\IO\ObjectOutputStream;
use KM\IO\ObjectStreamField;
use KM\Lang\Reflect\Type;
use KM\Lang\IllegalArgumentException;
use KM\Lang\Reflect\PrimitiveType;
use KM\Lang\Object;
use KM\Lang\Reflect\InvocationHandler;
use KM\IO\IOException;

/**
 * Default PutField implementation.
 *
 * @author Blair
 */
class PutFieldImpl extends PutField
{

    /**
     * The backing object output stream.
     *
     * @var ObjectOutputStream
     */
    private $oos;

    /**
     * The given class descriptor.
     *
     * @var ObjectStreamClass
     */
    private $desc;

    /**
     * The array of primitive field values.
     *
     * @var array
     */
    private $primVals;

    /**
     * The array of object values.
     *
     * @var array
     */
    private $objVals;

    /**
     * Creates a PutFieldImpl object for writing fields defined in the gien
     * class descriptor.
     *
     * @param ObjectStreamClass $desc
     * @param ObjectOutputStream $oos
     */
    public function __construct(ObjectStreamClass $desc, ObjectOutputStream $oos)
    {
        $this->desc = $desc;
        $this->oos = $oos;
        $this->primVals = (($psize = $desc->getPrimDataSize()) == 0) ? [] : array_fill(0, $psize, null);
        $this->objVals = (($osize = $desc->getNumObjectFields()) == 0) ? [] : array_fill(0, $osize, null);
    }

    public function put($name, $val)
    {
        $offset = $this->getFieldOffset($name, null);
        if (is_bool($val)) {
            Bits::putBoolean($this->primVals, $offset, $val);
        } elseif (is_int($val)) {
            Bits::putInt($this->primVals, $offset, $val);
        } elseif (is_float($val)) {
            Bits::putFloat($this->primVals, $offset, $val);
        } elseif (($val instanceof Object) || (is_array($val) || is_string($val))) {
            $this->objVals[$offset] = $val;
        }
    }

    /**
     * Write buffered primitive data and object fields to the stream.
     *
     * @throws IOException
     */
    public function writeFields()
    {
        $this->oos->write($this->primVals, 0, count($this->primVals));
        
        $cl = $this->oos->getClass();
        $m = $cl->getMethod('writeObject0');
        $m->setAccessible(true);
        
        $fields = $this->desc->getFields(false);
        $numPrimFields = count($fields) - count($this->objVals);
        for ($i = 0; $i < count($this->objVals); $i ++) {
            try {
                $args = [
                    $this->objVals[$i],
                    $fields[$numPrimFields + $i]->isUnshared()
                ];
                $m->invoke($this->oos, $args);
            } catch (InvocationHandler $e) {
                throw new IOException(null, $e);
            }
        }
    }

    /**
     * Returns offset of field with given name and type. A specified type of
     * null matches all types, Object.class matches all non-primitive types, and
     * any other non-null type matches assignable types only. Throws
     * IllegalArgumentException if no matching field found.
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
        if ($field === null) {
            $format = 'no such field %s with type %s';
            throw new IllegalArgumentException(sprintf($format, $name, $type));
        }
        return $field->getOffset();
    }
}
?>