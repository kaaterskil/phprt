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

use KM\IO\IOException;
use KM\IO\ObjectOutput;
use KM\IO\ObjectOutputStream\BlockDataOutputStream;
use KM\IO\ObjectOutputStream\HandleTable;
use KM\IO\ObjectOutputStream\PutFieldImpl;
use KM\IO\ObjectStreamClass;
use KM\IO\OutputStream;
use KM\IO\SerialCallbackContext;
use KM\Lang\ClassCastException;
use KM\Lang\Clazz;
use KM\Lang\Enum;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Util\ArrayObject;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Lang\Reflect\PrimitiveType;
use KM\Lang\Reflect\MixedType;
use KM\Lang\Reflect\Type;
use KM\IO\ObjectOutputStream\PutField;
use KM\Lang\Reflect\ArrayType;

/**
 * An ObjectOutputStream writes primitive data types and graphs of PHP objects
 * to an OutputStream.
 * The objects can be read (reconstituted) using an
 * ObjectInputStream. Persistent storage of objects can be accomplished by using
 * a file for the stream. If the stream is a network socket stream, the objects
 * can be reconstituted on another host or in another process. <p> Only objects
 * that support the Serializable interface can be written to streams. The class
 * of each serializable object is encoded including the class name and signature
 * of the class, the values of the object's fields and arrays, and the closure
 * of any other objects referenced from the initial objects. <p> The method
 * writeObject is used to write an object to the stream. Any object, including
 * Strings and arrays, is written with writeObject. Multiple objects or
 * primitives can be written to the stream. The objects must be read back from
 * the corresponding ObjectInputstream with the same types and in the same order
 * as they were written. <p> Primitive data types can also be written to the
 * stream using the appropriate methods from DataOutput. Strings can also be
 * written using the writeUTF method.
 *
 * @author Blair
 */
class ObjectOutputStream extends OutputStream implements ObjectOutput,
    ObjectStreamConstants
{

    /**
     * The underlying output stream.
     *
     * @var BlockDataOutputStream
     */
    private $bout;

    /**
     * The hash map of objects in the graph.
     *
     * @var HandleTable
     */
    private $handles;

    /**
     * Recursion depth
     *
     * @var int
     */
    private $depth;

    /**
     * Buffer for writing primitive values.
     *
     * @var array
     */
    private $primVals;

    /**
     * If true, invoke writeObjectOverride() instead of writeObject().
     *
     * @var boolean
     */
    private $enableOverride = false;

    /**
     * Context during upcalls to class-defined writeObject methods; holds object
     * currently being serialized and descriptor for current class.
     * Null when
     * not during writeObject upcall.
     *
     * @var SerialCallbackContext
     */
    private $curContext;

    /**
     * Current PutField object
     *
     * @var PutFieldImpl
     */
    private $curPut;

    /**
     * Creates an ObjectOutputStream that writes to the specified OutputStream.
     * This constructor writes the serialization stream header to the underlying
     * stream; callers may wish to flush the stream immediately to ensure that
     * constructors for receiving ObjectInputStreams will not block when reading
     * the header.
     *
     * @param OutputStream $out The output stream to write to.
     */
    public function __construct(OutputStream $out = null)
    {
        if ($out != null) {
            $this->bout = new BlockDataOutputStream($out, $this);
            $this->handles = new HandleTable();
            $this->enableOverride = false;
            $this->writeStreamHeader();
            $this->bout->setBlockDataMode(true);
        } else {
            $this->bout = null;
            $this->handles = null;
            $this->enableOverride = true;
        }
    }

    /**
     * Write the specified object to the ObjectOutputStream.
     * The class of the
     * object, the signature of the class, and the values of the non-transient
     * and non-static fields of the class and all of its super types are
     * written. Default serialization for a class can be overridden using the
     * writeObject and the readObject methods. Objects referenced by this object
     * are written transitively so that a complete equivalent graph of objects
     * can be reconstructed by an ObjectInputStream. <p> Exceptions are thrown
     * for problems with the OutputStream and for classes that should not be
     * serialized. All exceptions are fatal to the OutputStream, which is left
     * in an indeterminate state, and it is up to the caller to ignore or
     * recover the stream state.
     *
     * @param Object $obj object to be written to the underlying stream
     * @throws NotSerializableException Some object to be serialized does not
     *         implement the Serializable interface.
     * @throws IOException Any exception thrown by the underlying output stream.
     */
    public final function writeObject(Object $obj)
    {
        if ($this->enableOverride) {
            $this->writeObjectOverride($obj);
            return;
        }
        try {
            $this->writeObject0($obj, false);
        } catch (IOException $ex) {
            if ($this->depth == 0) {
                $this->writeFatalException($ex);
            }
            throw $ex;
        }
    }

    /**
     * Method used by subclasses to override the default writeObject method.
     * This method is called by trusted subclasses of ObjectInputStream that
     * constructed ObjectInputStream using the protected no-arg constructor. The
     * subclass is expected to provide an override method with the modifier
     * "final".
     *
     * @param Object $obj object to be written to the underlying stream
     * @throws IOException Any exception thrown by the underlying output stream.
     */
    protected function writeObjectOverride(Object $obj)
    {
        // Hook to do something here in subclass
    }

    /**
     * Writes an "unshared" object to the ObjectOutputStream.
     * This method is
     * identical to writeObject, except that it always writes the given object
     * as a new, unique object in the stream (as opposed to a back-reference
     * pointing to a previously serialized instance). Specifically: <ul> <li>An
     * object written via writeUnshared is always serialized in the same manner
     * as a newly appearing object (an object that has not been written to the
     * stream yet), regardless of whether or not the object has been written
     * previously. <li>If writeObject is used to write an object that has been
     * previously written with writeUnshared, the previous writeUnshared
     * operation is treated as if it were a write of a separate object. In other
     * words, ObjectOutputStream will never generate back-references to object
     * data written by calls to writeUnshared. </ul> While writing an object via
     * writeUnshared does not in itself guarantee a unique reference to the
     * object when it is deserialized, it allows a single object to be defined
     * multiple times in a stream, so that multiple calls to readUnshared by the
     * receiver will not conflict. Note that the rules described above only
     * apply to the base-level object written with writeUnshared, and not to any
     * transitively referenced sub-objects in the object graph to be serialized.
     *
     * @param Object $obj
     * @throws NotSerializableException if an object in the graph to be
     *         serialized does not implement the Serializable interface
     * @throws InvalidClassException if a problem exists with the class of
     *         object to be serialized.
     * @throws IOException if an I/O error occurs during serialization.
     */
    public function writeUnshared(Object $obj)
    {
        try {
            $this->writeObject0($obj, true);
        } catch (IOException $e) {
            if ($this->depth == 0) {
                $this->writeFatalException($e);
            }
            throw $e;
        }
    }

    /**
     * Write the non-static and non-transient fields of the current class to
     * this stream.
     * This may only be called from the writeObject method of the
     * class being serialized. It will throw the NotActiveException if it is
     * called otherwise.
     *
     * @throws NotActiveException
     */
    public function defaultWriteObject()
    {
        /* @var $ctx SerialCallbackContext */
        $ctx = $this->curContext;
        if ($ctx == null) {
            throw new NotActiveException('not in call to writeObject');
        }
        $curObj = $ctx->getObj();
        $curDesc = $ctx->getDesc();
        $this->bout->setBlockDataMode(false);
        $this->defaultWriteFields($curObj, $curDesc);
        $this->bout->setBlockDataMode(true);
    }

    /**
     * Retrieve the object used to buffer persistent fields to be written to the
     * stream.
     * The fields will be written to the stream when writeFields method
     * is called.
     *
     * @return \KM\IO\ObjectOutputStream\PutField An instance of the class
     *         PutField that holds the serializable fields.
     * @throws IOException if I/O errors occur.
     */
    public function putFields()
    {
        if ($this->curPut === null) {
            $ctx = $this->curContext;
            if ($ctx === null) {
                throw new NotActiveException('not in call to writeObject');
            }
            $curObj = $ctx->getObj();
            $curDesc = $ctx->getDesc();
            $this->curPut = new PutFieldImpl($curDesc, $this);
        }
        return $this->curPut;
    }

    /**
     * Write the buffered fields to the stream.
     *
     * @throws IOException if I/O errors occur while writing to the underlying
     *         stream.
     * @throws NotActiveException when a class' writeObject method was not
     *         called to write the state of the object.
     */
    public function writeFields()
    {
        if ($this->curPut === null) {
            throw new NotActiveException('no current PutField object');
        }
        $this->bout->setBlockDataMode(false);
        $this->curPut->writeFields();
        $this->bout->setBlockDataMode(true);
    }

    /**
     * Reset will disregard the state of any objects already written to the
     * stream.
     * The state is reset to be the same as a new ObjectOutputStream.
     * The current point in the stream is marked as reset so the corresponding
     * ObjectInputStream will be reset at the same point. Objects previously
     * written to the stream will not be referred to as already being in the
     * stream. They will be written to the stream again.
     *
     * @throws IOException if reset() is invoked while serializing an object.
     */
    public function reset()
    {
        if ($this->depth != 0) {
            throw new IOException('stream active');
        }
        $this->bout->setBlockDataMode(false);
        $this->bout->writeSingleByte(self::TC_RESET);
        $this->clear();
        $this->bout->setBlockDataMode(true);
    }

    /**
     * Subclasses may implement this method to allow class data to be stored in
     * the stream.
     * By default this method does nothing. The corresponding method
     * in ObjectInputStream is resolveClass. This method is called exactly once
     * for each unique class in the stream. The class name and signature will
     * have already been written to the stream. This method may make free use of
     * the ObjectOutputStream to save any representation of the class it deems
     * suitable (for example, the bytes of the class file). The resolveClass
     * method in the corresponding subclass of ObjectInputStream must read and
     * use any data or objects written by annotateClass.
     *
     * @param Clazz $cl The class to annotate custom data for.
     * @throws IOException if an I/O error occurs in the underlying output
     *         stream.
     */
    protected function annotateClass(Clazz $cl)
    {
        // Noop
    }

    /**
     * The writeStreamHeader method is provided so subclasses can append or
     * prepend their own header to the stream.
     * It writes the magic number and
     * version to the stream.
     *
     * @throws IOException is an I/O error occurs while writing to the
     *         underlying stream.
     */
    protected function writeStreamHeader()
    {
        $this->bout->writeShort(self::STREAM_MAGIC);
        $this->bout->writeShort(self::STREAM_VERSION);
    }

    /**
     * Write the specified class descriptor to the ObjectOutputStream.
     * Class
     * descriptors are used to identify the classes of objects written to the
     * stream. Subclasses of ObjectOutputStream may override this method to
     * customize the way in which class descriptors are written to the
     * serialization stream. The corresponding method in ObjectInputStream,
     * <code>readClassDescriptor</code>, should then be overridden to
     * reconstitute the class descriptor from its custom stream representation.
     * By default, this method writes class descriptors according to the format
     * defined in the Object Serialization specification. <p> Note that this
     * method will only be called if the ObjectOutputStream is not using the old
     * serialization stream format (set by calling ObjectOutputStream's
     * <code>useProtocolVersion</code> method). If this serialization stream is
     * using the old format (<code>PROTOCOL_VERSION_1</code>), the class
     * descriptor will be written internally in a manner that cannot be
     * overridden or customized.
     *
     * @param ObjectStreamClass $desc Class descriptor to write to the stream.
     * @throws IOException is an I/O error occurs.
     */
    protected function writeClassDescriptor(ObjectStreamClass $desc)
    {
        $desc->writeNonProxy($this);
    }

    /**
     * Writes a byte.
     * This method will block until the byte is actually written.
     *
     * @param int $val The byte to be written to the stream.
     * @throws IOException if an I/O error has occurred.
     * @see \KM\IO\OutputStream::writeByte()
     */
    public function writeByte($val)
    {
        $this->bout->writeByte($val);
    }

    /**
     * Writes a sub-array of bytes.
     *
     * @param array $b The data to be written.
     * @param int $off The start offset in the data.
     * @param int $len The number of bytes that are written.
     * @throws NullPointerException
     * @throws IndexOutOfBoundsException
     * @throws IOException if an I/O error has occurred.
     * @see \KM\IO\OutputStream::write()
     */
    public function write(array &$b, $off = 0, $len = null)
    {
        if ($b == null) {
            throw new NullPointerException();
        }
        if ($len == null) {
            $len = count($b);
        }
        $endoff = $off + $len;
        if ($off < 0 || $len < 0 || $endoff > count($b) || $endoff < 0) {
            throw new IndexOutOfBoundsException();
        }
        $this->bout->writeCopy($b, $off, $len, false);
    }

    /**
     * Drain any buffered data in ObjectOutputStream.
     * Similar to flush but does
     * not propagate the flush to the underlying stream.
     *
     * @throws IOException if an I/O error occurs while writing to the
     *         underlying stream.
     * @see \KM\IO\OutputStream::flush()
     */
    public function flush()
    {
        $this->bout->flush();
    }

    /**
     * Drain any buffered data in ObjectOutputStream.
     * Similar to flush but does
     * not propagate the flush to the underlying stream.
     *
     * @throws IOException is an I/O error occurs while writing to the
     *         underlying stream.
     */
    protected function drain()
    {
        $this->bout->drain();
    }

    /**
     * Closes the stream.
     * This method must be called to release any resources
     * associated with the stream.
     *
     * @throws IOException if an I/O error has occurred.
     * @see \KM\IO\OutputStream::close()
     */
    public function close()
    {
        $this->flush();
        $this->clear();
        $this->bout->close();
    }

    /**
     * Writes a boolean.
     *
     * @param boolean $v The boolean to be written.
     * @throws IOException if an I/O error occurs while writing to the
     *         underlying stream.
     * @see \KM\IO\DataOutput::writeBoolean()
     */
    public function writeBoolean($v)
    {
        $this->bout->writeBoolean($v);
    }

    /**
     * Writes an 8-bit byte.
     *
     * @param int $b The byte value to be written.
     * @throws IOException if an I/O error occurs while writing to the
     *         underlying stream.
     * @see \KM\IO\DataOutput::writeSingleByte()
     */
    public function writeSingleByte($b)
    {
        $this->bout->writeSingleByte($b);
    }

    /**
     * Writes a 16 bit short.
     *
     * @param int $v
     * @throws IOException if an I/O error occurs while writing to the
     *         underlying stream.
     * @see \KM\IO\DataOutput::writeShort()
     */
    public function writeShort($v)
    {
        $this->bout->writeShort($v);
    }

    /**
     * Writes a 32-bit int.
     *
     * @param int $v The integer value to be written.
     * @throws IOException if an I/O error occurs while writing to the
     *         underlying stream.
     * @see \KM\IO\DataOutput::writeInt()
     */
    public function writeInt($v)
    {
        $this->bout->writeInt($v);
    }

    /**
     * Writes a 32-bit float.
     *
     * @param float $v The float value to be written.
     * @throws IOException if an I/O error occurs while writing to the
     *         underlying stream.
     * @see \KM\IO\DataOutput::writeFloat()
     */
    public function writeFloat($v)
    {
        $this->bout->writeFloat($v);
    }

    /**
     * Writes a string as a sequence of bytes.
     *
     * @param string $s The string of bytes to be written.
     * @throws IOException if an I/O error occurs while writing to the
     *         underlying stream.
     * @see \KM\IO\DataOutput::writeBytes()
     */
    public function writeBytes($s)
    {
        $this->bout->writeBytes($s);
    }

    /**
     * Primitive data write of this String in modified UTF-8 format.
     * Note that
     * there is a significant difference between writing a String into the
     * stream as primitive data or as an Object. A String instance written by
     * writeObject is written into the stream as a String initially. Future
     * writeObject() calls write references to the string into the stream.
     *
     * @param string $s The string to be written.
     * @throws IOException if an I/O error occurs while writing to the
     *         underlying stream.
     * @see \KM\IO\DataOutput::writeUTF()
     */
    public function writeUTF($s)
    {
        $this->bout->writeUTF($s);
    }

    /**
     * Writes string without allowing it to be replaced in stream.
     * Used by
     * ObjectStreamClass to write class descriptor type strings.
     *
     * @param string $str
     */
    public function writeTypeString($str)
    {
        $handle;
        if ($str == null) {
            $this->writeNull();
        } elseif (($handle = $this->handles->lookup($str)) != -1) {
            $this->writeHandle($handle);
        } else {
            $this->writeString($str, false);
        }
    }

    /**
     * Clears internal data structure.
     */
    private function clear()
    {
        $this->handles->clear();
    }

    /**
     * Underlying writeObject() implementation.
     *
     * @param Object $obj
     * @param boolean $unshared
     * @throws IOException
     */
    private function writeObject0(Object $obj = null, $unshared)
    {
        /* @var $desc ObjectStreamClass */
        $oldMode = $this->bout->setBlockDataMode(false);
        $this->depth++;
        try {
            // Handle previously written and non-replaceable objects.
            $h;
            if ($obj === null) {
                $this->writeNull();
                return;
            } elseif (!$unshared && ($h = $this->handles->lookup($obj)) != -1) {
                $this->writeHandle($h);
                return;
            } elseif ($obj instanceof Clazz) {
                $this->writeClass($obj, $unshared);
                return;
            } elseif ($obj instanceof ObjectStreamClass) {
                $this->writeClassDesc($obj, $unshared);
                return;
            }
            
            $cl = $obj->getClass();
            $desc = ObjectStreamClass::lookup($cl, true);
            
            // Remaining cases
            if ($obj instanceof Enum) {
                $this->writeEnum($obj, $desc, $unshared);
            } elseif ($obj instanceof Serializable) {
                $this->writeOrdinaryObject($obj, $desc, $unshared);
            } else {
                throw new NotSerializableException($cl->getName());
            }
        } finally {
            $this->depth--;
            $this->bout->setBlockDataMode($oldMode);
        }
    }

    /**
     * Same as writeObject0 but for PHP objects that do not extend KM\Lang\Object.
     * @param object $obj
     * @param boolean $unshared
     * @throws IOException
     */
    private function writeObject1($obj = null, $unshared)
    {
        /* @var $desc ObjectStreamClass */
        $oldMode = $this->bout->setBlockDataMode(false);
        $this->depth++;
        try {
            // Handle previously written and non-replaceable objects.
            $h;
            if ($obj === null) {
                $this->writeNull();
                return;
            } elseif (!$unshared && ($h = $this->handles->lookup($obj)) != -1) {
                $this->writeHandle($h);
                return;
            } elseif ($obj instanceof Clazz) {
                $this->writeClass($obj, $unshared);
                return;
            } elseif ($obj instanceof ObjectStreamClass) {
                $this->writeClassDesc($obj, $unshared);
                return;
            }
            
            $cl = Clazz::forName(get_class($obj));
            $desc = ObjectStreamClass::lookup($cl, true);
            
            // Remaining cases
            if ($obj instanceof Enum) {
                $this->writeEnum($obj, $desc, $unshared);
            } elseif ($obj instanceof Serializable) {
                $this->writeOrdinaryObject($obj, $desc, $unshared);
            } else {
                throw new NotSerializableException($cl->getName());
            }
        } finally {
            $this->depth--;
            $this->bout->setBlockDataMode($oldMode);
        }
    }

    /**
     * Writes null code to stream.
     */
    private function writeNull()
    {
        $this->bout->writeSingleByte(self::TC_NULL);
    }

    /**
     * Write the given object handle to the stream.
     *
     * @param int $handle
     */
    private function writeHandle($handle)
    {
        $this->bout->writeSingleByte(self::TC_REFERENCE);
        $this->bout->writeInt(self::BASE_WIRE_HANDLE + $handle);
    }

    /**
     * Writes representation of given class to stream.
     *
     * @param Clazz $cl
     * @param boolean $unshared
     */
    private function writeClass(Clazz $cl, $unshared)
    {
        $this->bout->writeSingleByte(self::TC_CLASS);
        $this->writeClassDesc(ObjectStreamClass::lookup($cl, true), false);
        $this->handles->assign($unshared ? null : $cl);
    }

    /**
     * Writes representation of given class descriptor to stream.
     *
     * @param ObjectStreamClass $desc
     * @param boolean $unshared
     */
    private function writeClassDesc(ObjectStreamClass $desc = null, $unshared)
    {
        $handle;
        if ($desc == null) {
            $this->writeNull();
        } elseif (!$unshared && ($handle = $this->handles->lookup($desc)) != -1) {
            $this->writeHandle($handle);
        } elseif ($desc->isProxy()) {
            $this->writeProxyDesc($desc, $unshared);
        } else {
            $this->writeNonProxyDesc($desc, $unshared);
        }
    }

    /**
     * Writes class descriptor representing a dynamic proxy class to stream.
     *
     * @param ObjectStreamClass $desc
     * @param boolean $unshared
     */
    private function writeProxyDesc(ObjectStreamClass $desc, $unshared)
    {
        $this->bout->writeSingleByte(self::TC_PROXYCLASSDESC);
        $this->handles->assign($unshared ? null : $desc);
        
        $cl = $desc->forClass();
        $ifaces = $cl->getInterfaces();
        $this->bout->writeInt(count($ifaces));
        for ($i = 0; $i < count($ifaces); $i++) {
            $this->bout->writeUTF($ifaces[$i]->getName());
        }
        $this->bout->writeSingleByte(self::TC_ENDBLOCKDATA);
        
        $this->writeClassDesc($desc->getSuperDesc(), false);
    }

    /**
     * Writes class descriptor representing a standard (i.e., not a dynamic
     * proxy) class to stream.
     *
     * @param ObjectStreamClass $desc
     * @param boolean $unshared
     */
    private function writeNonProxyDesc(ObjectStreamClass $desc, $unshared)
    {
        $this->bout->writeSingleByte(self::TC_CLASSDESC);
        $this->handles->assign($unshared ? null : $desc);
        
        $this->writeClassDescriptor($desc);
        
        $cl = $desc->forClass();
        $this->bout->setBlockDataMode(true);
        $this->annotateClass($cl);
        $this->bout->setBlockDataMode(false);
        $this->bout->writeSingleByte(self::TC_ENDBLOCKDATA);
        
        $this->writeClassDesc($desc->getSuperDesc(), false);
    }

    /**
     * Writes given string to stream.
     *
     * @param string $str
     * @param boolean $unshared
     */
    private function writeString($str, $unshared)
    {
        $this->handles->assign($unshared ? null : $str);
        $utflen = $this->bout->getUTFLength($str);
        if ($utflen <= 0xffff) {
            $this->bout->writeSingleByte(self::TC_STRING);
            $this->bout->writeUTF($str, $utflen);
        } else {
            $this->bout->writeSingleByte(self::TC_LONGSTRING);
            $this->bout->writeLongUTF($str, $utflen);
        }
    }

    /**
     * Write the given mixed value to the stream.
     *
     * @param mixed $v
     */
    public function writeMixed($v)
    {
        $type = ReflectionUtility::typeForValue($v);
        if ($type->isPrimitive()) {
            $this->writeSingleByte(ObjectStreamClass::getTypeSignature($type));
            if ($type == PrimitiveType::BOOLEAN()) {
                $this->writeSingleByte();
                $this->writeBoolean($v);
            } elseif ($type == PrimitiveType::SHORT()) {
                $this->writeInt($v);
            } elseif ($type == PrimitiveType::INTEGER()) {
                $this->writeInt($v);
            } elseif ($type == PrimitiveType::FLOAT()) {
                $this->writeFloat($v);
            } elseif ($type == PrimitiveType::LONG()) {
                $this->writeInt($v);
            } elseif ($type == PrimitiveType::DOUBLE()) {
                $this->writeFloat($v);
            } elseif ($type == PrimitiveType::STRING()) {
                $this->writeUTF($v);
            }
        } elseif ($type->isArray()) {
            $this->writeArray($v, $type, false);
        } elseif ($v instanceof Object) {
            $this->writeObject0($v, false);
        } elseif ($type->isObject()) {
            $this->writeObject1($v, false);
        } else {
            trigger_error('internal error');
        }
    }

    /**
     * Write the given array to the stream.
     *
     * @param array $array
     * @param Type $type
     * @param boolean $unshared
     */
    public function writeArray(array $array, Type $type, $unshared)
    {
        $this->bout->writeSingleByte(self::TC_ARRAY);
        $this->handles->assign($unshared ? null : $array);
        
        $ct = $type->getComponentType();
        if ($ct instanceof Clazz) {
            $desc = ObjectStreamClass::lookup($ct, true);
            $this->writeClassDesc($desc, $unshared);
        } else {
            $this->writeSingleByte(ObjectStreamClass::getTypeSignature($ct));
            $this->writeUTF($ct->getTypeName());
        }
        if ($ct->isPrimitive()) {
            if ($ct == PrimitiveType::INTEGER()) {
                $this->bout->writeInt(count($array));
                $this->bout->writeInts($array, 0, count($array));
            } elseif ($ct == PrimitiveType::LONG()) {
                $this->bout->writeInt(count($array));
                $this->bout->writeInts($array, 0, count($array));
            } elseif ($ct == PrimitiveType::FLOAT()) {
                $this->bout->writeInt(count($array));
                $this->bout->writeFloat($array, 0, count($array));
            } elseif ($ct == PrimitiveType::DOUBLE()) {
                $this->bout->writeInt(count($array));
                $this->bout->writeFloat($array, 0, count($array));
            } elseif ($ct == PrimitiveType::SHORT()) {
                $this->bout->writeInt(count($array));
                $this->bout->writeInts($array, 0, count($array));
            } elseif ($ct == PrimitiveType::BOOLEAN()) {
                $this->bout->writeInt(count($array));
                $this->bout->writeBooleans($array, 0, count($array));
            } elseif ($ct == PrimitiveType::STRING()) {
                $this->bout->writeInt(count($array));
                foreach ($array as $s) {
                    $this->bout->writeUTF($s);
                }
            } else {
                trigger_error('internal error');
            }
        } elseif ($ct->isMixed()) {
            if ($ct == MixedType::MIXED()) {
                $this->writeInt(count($array));
                foreach ($array as $v) {
                    $this->writeMixed($v);
                }
            } elseif ($ct == MixedType::NUMBER()) {
                $this->writeInt(count($array));
                foreach ($array as $v) {
                    $this->writeMixed($v, false);
                }
            }
        } elseif ($ct->isArray()) {
            $cct = null;
            $this->writeInt(count($array));
            foreach ($array as $v) {
                if ($cct === null) {
                    $cct = ReflectionUtility::typeForValue($v);
                }
                $this->writeArray($array, $cct, false);
            }
        } else {
            $this->bout->writeInt(count($array));
            foreach ($array as $obj) {
                $this->writeObject0($obj, false);
            }
        }
    }

    /**
     * Write given enum constant to stream.
     *
     * @param Enum $en
     * @param ObjectStreamClass $desc
     * @param boolean $unshared
     */
    private function writeEnum(Enum $en, ObjectStreamClass $desc, $unshared)
    {
        $this->bout->writeSingleByte(self::TC_ENUM);
        $sdesc = $desc->getSuperDesc();
        $this->writeClassDesc(
            ($sdesc->forClass() == Enum::clazz()) ? $desc : $sdesc, false);
        $this->handles->assign($unshared ? $null : $en);
        $this->writeString($en->getName(), false);
    }

    /**
     * Writes representation of a "ordinary" (i.e., not a String, Class,
     * ObjectStreamClass, array, or enum constant) serializable object to the
     * stream.
     *
     * @param Object $obj
     * @param ObjectStreamClass $desc
     * @param boolean $unshared
     */
    private function writeOrdinaryObject(Object $obj, ObjectStreamClass $desc,
        $unshared)
    {
        $desc->checkSerialize();
        
        $this->bout->writeSingleByte(self::TC_OBJECT);
        $this->writeClassDesc($desc, false);
        $this->handles->assign($unshared ? null : $obj);
        $this->writeSerialData($obj, $desc);
    }

    /**
     * Writes instance data for each serializable class of given object, from
     * superclass to subclass.
     *
     * @param Object $obj
     * @param ObjectStreamClass $desc
     */
    private function writeSerialData(Object $obj, ObjectStreamClass $desc)
    {
        $slots = $desc->getClassDataLayout();
        for ($i = 0; $i < count($slots); $i++) {
            $slotDesc = $slots[$i]->desc;
            if ($slotDesc->hasWriteObjectMethod()) {
                $oldPut = $this->curPut;
                $this->curPut = null;
                $oldContext = $this->curContext;
                
                try {
                    $this->curContext = new SerialCallbackContext($obj,
                        $slotDesc);
                    $this->bout->setBlockDataMode(true);
                    $slotDesc->invokeWriteObject($obj, $this);
                    $this->bout->setBlockDataMode(false);
                    $this->bout->writeSingleByte(self::TC_ENDBLOCKDATA);
                } finally {
                    $this->curContext->setUsed();
                    $this->curContext = $oldContext;
                }
                $this->curPut = $oldPut;
            } else {
                $this->defaultWriteFields($obj, $slotDesc);
            }
        }
        // $this->bout->writeBytes( serialize( $obj ) );
    }

    /**
     * Fetches and writes values of serializable fields of given object to
     * stream.
     * The given class descriptor specifies which field values to write,
     * and in which order they should be written.
     *
     * @param Object $obj
     * @param ObjectStreamClass $desc
     * @throws ClassCastException
     * @throws IOException
     */
    private function defaultWriteFields(Object $obj = null, ObjectStreamClass $desc)
    {
        $cl = $desc->forClass();
        if ($cl != null && $obj != null && !$cl->isInstance($obj)) {
            throw new ClassCastException();
        }
        
        $desc->checkDefaultSerialize();
        
        $primDataSize = $desc->getPrimDataSize();
        if ($this->primVals == null || count($this->primVals) < $primDataSize) {
            $this->primVals = ($primDataSize == 0) ? [] : array_fill(0,
                $primDataSize, null);
        }
        $desc->getPrimFieldValues($obj, $this->primVals);
        $this->bout->writeCopy($this->primVals, 0, $primDataSize, false);
        
        $fields = $desc->getFields(false);
        $objVals = ($desc->getNumObjectFields() == 0) ? [] : array_fill(0,
            $desc->getNumObjectFields(), null);
        $numPrimFields = count($fields) - count($objVals);
        $desc->getObjFieldValues($obj, $objVals);
        for ($i = 0; $i < count($objVals); $i++) {
            $val = $objVals[$i];
            $field = $fields[$numPrimFields + $i];
            $type = ReflectionUtility::typeForValue($field);
            $unshared = $field->isUnshared();
            if ($type == PrimitiveType::STRING()) {
                $this->writeUTF($val);
            } elseif ($type->isMixed()) {
                $this->writeMixed($val, $unshared);
            } elseif ($type->isArray()) {
                $this->writeArray($val, $type, $unshared);
            } else {
                $this->writeObject0($val, $unshared);
            }
        }
    }

    /**
     * Attempts to write to the stream the fatal IOException that has caused
     * serialization to abort.
     *
     * @param IOException $ex
     */
    private function writeFatalException(IOException $ex)
    {
        $this->clear();
        $oldMode = $this->bout->setBlockDataMode(false);
        
        try {
            $this->bout->writeSingleByte(self::TC_EXCEPTION);
            $this->writeObject1($ex, false);
            $this->clear();
        } finally {
            $this->bout->setBlockDataMode($oldMode);
        }
    }
}
?>