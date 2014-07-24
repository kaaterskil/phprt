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
use KM\IO\NotActiveException;
use KM\IO\ObjectInputStream\BlockDataInputStream;
use KM\IO\ObjectInputStream\HandleTable;
use KM\IO\ObjectStreamClass;
use KM\IO\ObjectStreamClass\ClassDataSlot;
use KM\IO\ObjectStreamField;
use KM\IO\SerialCallbackContext;
use KM\IO\StreamCorruptedException;
use KM\Lang\ClassNotFoundException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\Clazz;
use KM\Lang\Enum;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\Reflect\MixedType;
use KM\Lang\Reflect\PrimitiveType;
use KM\Lang\Reflect\Proxy;
use KM\Lang\Reflect\ReflectionUtility;
use KM\Lang\Reflect\Type;
use KM\IO\ObjectInputStream\GetFieldImpl;

/**
 * An ObjectInputStream deserializes primitive data and objects previously
 * written using an ObjectOutputStream.
 * <p>
 * ObjectOutputStream and ObjectInputStream can provide an application with
 * persistent storage for graphs of objects when used with a FileOutputStream
 * and FileInputStream respectively. ObjectInputStream is used to recover those
 * objects previously serialized. Other uses include passing objects between
 * hosts using a socket stream or for marshaling and unmarshaling arguments and
 * parameters in a remote communication system.
 * <p>
 * ObjectInputStream ensures that the types of all objects in the graph created
 * from the stream match the classes present in PHP. Classes are loaded as
 * required using the standard mechanisms.
 * <p>
 * Only objects that support the KM\IO\Serializable interface can be read from
 * streams.
 * <p>
 * The method <code>readObject</code> is used to read an object from the stream.
 * Java's safe casting should be used to get the desired type. In Java, strings
 * and arrays are objects and are treated as objects during serialization. When
 * read they need to be cast to the expected type.
 * <p>
 * Primitive data types can be read from the stream using the appropriate method
 * on DataInput.
 * <p>
 * The default deserialization mechanism for objects restores the contents of
 * each field to the value and type it had when it was written. Fields declared
 * as transient or static are ignored by the deserialization process. References
 * to other objects cause those objects to be read from the stream as necessary.
 * Graphs of objects are restored correctly using a reference sharing mechanism.
 * New objects are always allocated when deserializing, which prevents existing
 * objects from being overwritten.
 * <p>
 * Reading an object is analogous to running the constructors of a new object.
 * Memory is allocated for the object and initialized to zero (NULL). No-arg
 * constructors are invoked for the non-serializable classes and then the fields
 * of the serializable classes are restored from the stream starting with the
 * serializable class closest to KM\Lang\Object and finishing with the object's
 * most specific class.
 * <p>
 * For example to read from a stream as written by the example in
 * ObjectOutputStream: <br>
 * <pre>
 * FileInputStream fis = new FileInputStream("t.tmp");
 * ObjectInputStream ois = new ObjectInputStream(fis);
 * int i = ois.readInt();
 * String today = (String) ois.readObject();
 * Date date = (Date) ois.readObject();
 * ois.close();
 * </pre>
 * <p>
 * Implementing the Serializable interface allows object serialization to
 * save and restore the entire state of the object and it allows classes to
 * evolve between the time the stream is written and the time it is read. It
 * automatically traverses references between objects, saving and restoring
 * entire graphs.
 * <p>
 * Serializable classes that require special handling during the serialization
 * and deserialization process should implement the following methods:
 * <pre>
 * private void writeObject(ObjectOutputStream stream);
 * private void readObject(ObjectInputStream stream);
 * private void readObjectNoData();
 * </pre>
 * <p>
 * The readObject method is responsible for reading and restoring the state of
 * the object for its particular class using data written to the stream by the
 * corresponding writeObject method. The method does not need to concern itself
 * with the state belonging to its superclasses or subclasses. State is restored
 * by reading data from the ObjectInputStream for the individual fields and
 * making assignments to the appropriate fields of the object. Reading primitive
 * data types is supported by DataInput.
 * <p>
 * Any attempt to read object data which exceeds the boundaries of the custom
 * data written by the corresponding writeObject method will cause an
 * OptionalDataException to be thrown with an eof field value of true.
 * Non-object reads which exceed the end of the allotted data will reflect the
 * end of data in the same way that they would indicate the end of the stream:
 * bytewise reads will return -1 as the byte read or number of bytes read, and
 * primitive reads will throw EOFExceptions. If there is no corresponding
 * writeObject method, then the end of default serialized data marks the end of
 * the allotted data.
 * <p>
 * Primitive and object read calls issued from within a readExternal method
 * behave in the same manner--if the stream is already positioned at the end of
 * data written by the corresponding writeExternal method, object reads will
 * throw OptionalDataExceptions with eof set to true, bytewise reads will return
 * -1, and primitive reads will throw EOFExceptions. Note that this behavior
 * does not hold for streams written with the old
 * <code>ObjectStreamConstants.PROTOCOL_VERSION_1</code> protocol, in which the
 * end of data written by writeExternal methods is not demarcated, and hence
 * cannot be detected.
 * <p>
 * The readObjectNoData method is responsible for initializing the state of the
 * object for its particular class in the event that the serialization stream
 * does not list the given class as a superclass of the object being
 * deserialized. This may occur in cases where the receiving party uses a
 * different version of the deserialized instance's class than the sending
 * party, and the receiver's version extends classes that are not extended by
 * the sender's version. This may also occur if the serialization stream has
 * been tampered; hence, readObjectNoData is useful for initializing
 * deserialized objects properly despite a "hostile" or incomplete source
 * stream.
 * <p>
 * Serialization does not read or assign values to the fields of any object that
 * does not implement the KM\IO\Serializable interface. Subclasses of Objects
 * that are not serializable can be serializable. In this case the
 * non-serializable class must have a no-arg constructor to allow its fields to
 * be initialized. In this case it is the responsibility of the subclass to save
 * and restore the state of the non-serializable class. It is frequently the
 * case that the fields of that class are accessible (public, package, or
 * protected) or that there are get and set methods that can be used to restore
 * the state.
 * <p>
 * Any exception that occurs while deserializing an object will be caught by the
 * ObjectInputStream and abort the reading process.
 * <p>
 * Enum constants are deserialized differently than ordinary serializable
 * objects. The serialized form of an enum constant consists solely of its name;
 * field values of the constant are not transmitted. To deserialize an enum
 * constant, ObjectInputStream reads the constant name from the stream; the
 * deserialized constant is then obtained by calling the static method
 * <code>Enum.valueOf(Class, String)</code> with the enum constant's base type
 * and the received constant name as arguments. Like other serializable objects,
 * enum constants can function as the targets of back references appearing
 * subsequently in the serialization stream. The process by which enum constants
 * are deserialized cannot be customized: any class-specific readObject,
 * readObjectNoData, and readResolve methods defined by enum types are ignored
 * during deserialization..
 *
 * @author Blair
 */
class ObjectInputStream extends InputStream implements ObjectInput,
    ObjectStreamConstants
{

    /**
     * Handle value representing null.
     *
     * @var int
     */
    const NULL_HANDLE = -1;

    /**
     * Marker for unshared objects in the internal handle table.
     *
     * @var \KM\Lang\Object
     */
    private static $unsharedMarker;

    /**
     * Filter stream for handling block data conversion
     *
     * @var BlockDataInputStream
     */
    private $bin;

    /**
     * Recursion depth
     *
     * @var int
     */
    private $depth;

    /**
     * Whether the stream is closed.
     *
     * @var boolean
     */
    private $closed;

    /**
     * Wire handle -> obj/exception map.
     *
     * @var HandleTable
     */
    public $handles;

    /**
     * Scratch field for passing handle values up/down the call stack.
     *
     * @var int
     */
    public $passHandle = self::NULL_HANDLE;

    /**
     * Flag set when at end of field value block with no TC_ENDBLOCKDATA
     *
     * @var boolean
     */
    public $defaultDataEnd = false;

    /**
     * Buffer for reading primitive values.
     *
     * @var array
     */
    private $primVals;

    /**
     * If true, invoke readObjectOverride() instead of readObject().
     *
     * @var boolean
     */
    private $enableOverride = false;

    /**
     * Context during up-calls to class-defined readObject methods; holds object
     * currently being deserialized and descriptor for current class.
     * Null when not during readObject up-call.
     *
     * @var SerialCallbackContext
     */
    private $curContext;

    /**
     * Creates an ObjectInputStream that reads from the specified InputStream.
     *
     * A serialization stream header is read from the stream and verified. This
     * constructor will block until the corresponding ObjectOutputStream has
     * written and flushed the header.
     *
     * @param InputStream $in The input stream to read from.
     */
    public function __construct(InputStream $in = null)
    {
        self::$unsharedMarker = new Object();
        if ($in != null) {
            $this->bin = new BlockDataInputStream($in, $this);
            $this->handles = new HandleTable();
            $this->enableOverride = false;
            $this->readStreamHeader();
            $this->bin->setBlockDataMode(true);
        } else {
            $this->bin = null;
            $this->handles = null;
            $this->enableOverride = true;
        }
    }

    /**
     * Read an object from the ObjectInputStream.
     * The class of the object, the signature of the class, and the values of
     * the non-transient and non-static fields of the class and all of its super
     * types are read. Default deserializing for a class can be overridden using
     * the writeObject and readObject methods. Objects referenced by this object
     * are read transitively so that a complete equivalent graph of objects is
     * reconstructed by readObject.
     * <p>
     * The root object is completely restored when all of its fields and the
     * objects it references are completely restored.
     * <p>
     * Exceptions are thrown for problems with the InputStream and for classes
     * that should not be deserialized. All exceptions are fatal to the
     * InputStream and leave it in an indeterminate state; it is up to the
     * caller to ignore or recover the stream state.
     *
     * @return \KM\Lang\Object
     * @throws ClassNotFoundException if the class of a serialized object cannot
     *         be found.
     * @throws InvalidClassException when something is wrong with a class used
     *         by serialization.
     * @throws StreamCorruptedException when control information in the stream
     *         is inconsistent.
     * @throws IOException if any other I/O error occurs.
     * @see \KM\IO\ObjectInput::readObject()
     */
    public function readObject()
    {
        if ($this->enableOverride) {
            return $this->readObjectOverride();
        }
        
        // If nested read, passHandle contains handle of enclosing object.
        $outerHandle = $this->passHandle;
        try {
            $obj = $this->readObject0(false);
            $this->handles->markDependency($outerHandle, $this->passHandle);
            $ex = $this->handles->lookupException($this->passHandle);
            if ($ex != null) {
                throw $ex;
            }
            return $obj;
        } finally {
            $this->passHandle = $outerHandle;
            if ($this->closed && $this->depth == 0) {
                $this->clear();
            }
        }
    }

    /**
     * This method is called by trusted subclasses of ObjectOutputStream that
     * constructed ObjectOutputStream using the protected no-arg constructor.
     * The subclass is expected to provide an override method with the modifier
     * "final".
     *
     * @return Object The object read from the stream.
     */
    protected function readObjectOverride()
    {
        // Hook to do something here in subclass
        return null;
    }

    /**
     * Reads an "unshared" object from the ObjectInputStream.
     * This method is
     * identical to readObject, except that it prevents subsequent calls to
     * readObject and readUnshared from returning additional references to the
     * deserialized instance obtained via this call. Specifically:
     * <ul>
     * <li>If readUnshared is called to deserialize a back-reference (the
     * stream representation of an object which has been written
     * previously to the stream), an ObjectStreamException will be
     * thrown.
     *
     * <li>If readUnshared returns successfully, then any subsequent attempts
     * to deserialize back-references to the stream handle deserialized
     * by readUnshared will cause an ObjectStreamException to be thrown.
     * </ul>
     * Deserializing an object via readUnshared invalidates the stream handle
     * associated with the returned object. Note that this in itself does not
     * always guarantee that the reference returned by readUnshared is unique;
     * the deserialized object may define a readResolve method which returns an
     * object visible to other parties, or readUnshared may return a Class
     * object or enum constant obtainable elsewhere in the stream or through
     * external means. If the deserialized object defines a readResolve method
     * and the invocation of that method returns an array, then readUnshared
     * returns a shallow clone of that array; this guarantees that the returned
     * array object is unique and cannot be obtained a second time from an
     * invocation of readObject or readUnshared on the ObjectInputStream,
     * even if the underlying data stream has been manipulated.
     *
     * @return \KM\Lang\Object reference to deserialized object,
     * @throws ClassNotFoundException if the class of the object to deserialize
     *         cannot be found.
     * @throws StreamCorruptedException if control information in the stream is
     *         inconsistent.
     * @throws ObjectStreamException if the object to deserialize has already
     *         appeared in the stream.
     * @throws OptionalDataException if primitive data is next in the stream.
     * @throws IOException if an I/O error occurs during deserialization.
     */
    public function readUnshared()
    {
        // If nested read, passHandle contains handle of enclosing object.
        $outerHandle = $this->passHandle;
        try {
            $obj = $this->readObject0(true);
            $this->handles->markDependency($outerHandle, $this->passHandle);
            $ex = $this->handles->lookupException($this->passHandle);
            if ($ex != null) {
                throw $ex;
            }
            return $obj;
        } finally {
            $this->passHandle = $outerHandle;
            if ($this->closed && $this->depth == 0) {
                $this->clear();
            }
        }
    }

    /**
     * Read the non-static and non-transient fields of the current class from
     * this stream.
     * This may only be called from the readObject method of the class being
     * deserialized. It will throw the NotActiveException if it is called
     * otherwise.
     *
     * @throws ClassNotFoundException if the class of a serialized object could
     *         not be found.
     * @throws NotActiveException if the stream is not currently reading
     *         objects.
     * @throws IOException if an I/O error occurs.
     */
    public function defaultReadObject()
    {
        $ctx = $this->curContext;
        if ($ctx == null) {
            throw new NotActiveException('not in call to readObject');
        }
        
        $curObj = $ctx->getObj();
        $curDesc = $ctx->getDesc();
        $this->bin->setBlockDataMode(false);
        $this->defaultReadFields($curObj, $curDesc);
        $this->bin->setBlockDataMode(true);
        if (!$curDesc->hasWriteObjectData()) {
            // Stream does not contain TC_ENDBLOCKDATA tag. Set flag so that
            // reading code elsewhere knows to simulate end-of-custom-data
            // behavior.
            $this->defaultDataEnd = true;
        }
        $ex = $this->handles->lookupException($this->passHandle);
        if ($ex != null) {
            throw $ex;
        }
    }

    /**
     * Reads the persistent fields from the stream and makes them available by
     * name.
     *
     * @return \KM\IO\ObjectInputStream\GetField The <code>GetField</code>
     *         object representing the persistent fields of the object being
     *         deserialized.
     * @throws ClassNotFoundException if the class of a serialized object could
     *         not be found.
     * @throws IOException if an I/O error occurs.
     * @throws NotActiveException if the stream is not currently reading
     *         objects.
     */
    public function readFields()
    {
        $ctx = $this->curContext;
        if ($ctx == null) {
            throw new NotActiveException('not in call to readObject');
        }
        $curObj = $ctx->getObj();
        $curDesc = $ctx->getDesc();
        $this->bin->setBlockDataMode(false);
        $getField = new GetFieldImpl($curDesc, $this);
        $getField->readFields();
        $this->bin->setBlockDataMode(true);
        if (!$curDesc->hasWriteObjectData()) {
            // Since stream does not contain terminating TC_ENDBLOCKDATA tag,
            // set flag so that reading code elsewhere knows to simulate
            // end-of-custom-data behavior.
            $this->defaultDataEnd = true;
        }
        return $getField;
    }

    /**
     * Load the local class equivalent of the specified stream class
     * description.
     *
     * <p>The corresponding method in <code>ObjectOutputStream</code> is
     * <code>annotateClass</code>. This method will be invoked only once for
     * each unique class in the stream. Once returned, if the class is not an
     * array class and if there is a mismatch, the deserialization fails and an
     * <code>InvalidClassException</code> is thrown.
     *
     * @param ObjectStreamClass $desc
     * @return \KM\Lang\Reflect\Type
     */
    protected function resolveClass(ObjectStreamClass $desc)
    {
        $name = $desc->getName();
        try {
            return Clazz::forName($name);
        } catch (ClassNotFoundException $e) {
            $cl = ReflectionUtility::typeFor($name);
            if ($cl !== null) {
                return $cl;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Returns a proxy class that implements the interfaces named in a proxy
     * class descriptor.
     *
     * <p>This method is called exactly once for each unique proxy class
     * descriptor in the stream.
     *
     * <p>The corresponding method in <code>ObjectOutputStream</code> is
     * <code>annotateProxyClass</code>.
     *
     * <p>The default implementation of this method in
     * <code>ObjectInputStream</code> returns the result of calling
     * <code>Proxy.getProxyClass</code> with the list of <code>Class</code>
     * objects for the interfaces that are named in the <code>interfaces</code>
     * parameter.
     *
     * If <code>Proxy.getProxyClass</code> throws an
     * <code>IllegalArgumentException</code>, <code>resolveProxyClass</code>
     * will throw a <code>ClassNotFoundException</code> containing the
     * <code>IllegalArgumentException</code>.
     *
     * @param array $interfaces An array of string interface names.
     * @throws ClassNotFoundException if the proxy class or any of the named
     *         interfaces could not be found.
     * @throws IOException if an I/O error occurs in the underlying input
     *         stream.
     * @return \KM\Lang\Clazz A proxy class for the specified interfaces.
     */
    protected function resolveProxyClass(array $interfaces)
    {
        $classObjs = [];
        for ($i = 0; $i < count($interfaces); $i++) {
            $classObjs[$i] = Clazz::forName($interfaces[$i]);
        }
        try {
            return Proxy::getProxyClass($classObjs);
        } catch (IllegalArgumentException $e) {
            throw new ClassNotFoundException(null, $e);
        }
    }

    /**
     * The readStreamHeader method is provided to allow subclasses to read and
     * verify their own stream headers.
     * It reads and verifies the magic number and version number.
     *
     * @throws StreamCorruptedException if control information in the stream is
     *         inconsistent.
     * @throws IOException if an I/O error occurs in the underlying input
     *         stream.
     */
    protected function readStreamHeader()
    {
        $s0 = $this->bin->readShort();
        $s1 = $this->bin->readShort();
        if ($s0 != self::STREAM_MAGIC || $s1 != self::STREAM_VERSION) {
            $format = 'invalid stream header: %04X%04X';
            throw new StreamCorruptedException(sprintf($format, $s0, $s1));
        }
    }

    /**
     * Read a class descriptor from the serialization stream.
     * This method is
     * called when the ObjectInputStream expects a class descriptor as the next
     * item in the serialization stream. By default, this method reads class
     * descriptors according to the format defined in the Object Serialization
     * specification.
     *
     * @return \KM\IO\ObjectStreamClass The class descriptor read.
     * @throws ClassNotFoundException if the class of a serialized object used
     *         in the class descriptor representation cannot be found.
     * @throws IOException if an I/O error occurs.
     */
    protected function readClassDescriptor()
    {
        /* @var $desc ObjectStreamClass */
        $desc = ObjectStreamClass::getInstance();
        $desc->readNonProxy($this);
        return $desc;
    }

    /**
     * Reads a byte of data.
     * This method will block if no input is available.
     *
     * @return int The bytes read, or -1 if the end of the stream is reached.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\InputStream::readByte()
     */
    public function readByte()
    {
        return $this->bin->readByte(); // OK
    }

    /**
     * Reads into an array of bytes.
     * This method will block until some input is available. Consider using
     * KM\IO\DataInputStream.readFully to read exactly 'length' bytes.
     *
     * @param array $buf The buffer into which the data is read.
     * @param int $off The start offset of the data.
     * @param int $len The maximum number of bytes read.
     * @throws IndexOutOfBoundsException
     * @throws IOException if an I/O exception occurs.
     * @return int The actual number of bytes read. -1 is returned when the end
     *         of the stream is reached.
     * @see \KM\IO\InputStream::read()
     */
    public function read(array &$buf, $off = 0, $len = null)
    {
        if ($len == null) {
            $len = count($buf);
        }
        $endoff = $off + $len;
        if ($off < 0 || $len < 0 || $endoff > count($buf) || $endoff < 0) {
            throw new IndexOutOfBoundsException();
        }
        return $this->bin->read($b, $off, $len);
    }

    /**
     * Returns the number of bytes that can be read without blocking.
     *
     * @return int The number of available bytes.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\InputStream::available()
     */
    public function available()
    {
        return $this->bin->available();
    }

    /**
     * Closes the input stream.
     * Must be called to release any resources associated with the stream.
     *
     * @see \KM\IO\InputStream::close()
     */
    public function close()
    {
        // Even if stream already closed, propagate redundant close to
        // underlying stream to stay consistent with previous implementations.
        $this->closed = true;
        if ($this->depth == 0) {
            $this->clear();
        }
        $this->bin->close();
    }

    /**
     * Reads in a boolean.
     *
     * @return boolean The boolean read.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readBoolean()
     */
    public function readBoolean()
    {
        return $this->bin->readBoolean();
    }

    /**
     * Reads an 8-bit byte.
     *
     * @return int The byte read,
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readSingleByte()
     */
    public function readSingleByte()
    {
        return $this->bin->readSingleByte();
    }

    /**
     * Reads a 16-bit short.
     *
     * @return int The 16-bit short read into a 32-bit int.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readShort()
     */
    public function readShort()
    {
        return $this->bin->readShort();
    }

    /**
     * Reads an unsigned 16-bit short.
     *
     * @return int The 16-bit short read.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readUnsignedShort()
     */
    public function readUnsignedShort()
    {
        return $this->bin->readUnsignedShort();
    }

    /**
     * Reads a 32-bit int.
     *
     * @return int The 32-bit int read.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readInt()
     */
    public function readInt()
    {
        return $this->bin->readInt();
    }

    /**
     * Reads a 64-bit long.
     *
     * @return int The read 16-bit long.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readLong()
     */
    public function readLong()
    {
        return $this->bin->readLong();
    }

    /**
     * Reads a 32-bit float.
     *
     * @return float The 32-bit float read.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readFloat()
     */
    public function readFloat()
    {
        return $this->bin->readFloat();
    }

    /**
     * Reads bytes, blocking until all bytes are read.
     *
     * @param array $buf The buffer into which the data is read.
     * @param int $off The start offset of the data.
     * @param int $len The maximum number of bytes to read.
     * @throws EOFException if the end of the file is reached.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readFully()
     */
    public function readFully(array &$buf, $off = 0, $len = null)
    {
        if ($len === null) {
            $len = count($buf);
        }
        $endoff = $off + $len;
        if ($off < 0 || $len < 0 || $endoff > count($buf) || $endoff < 0) {
            throw new IndexOutOfBoundsException();
        }
        $this->bin->readFully($buf, $off, $len, false);
    }

    /**
     * Skips bytes.
     *
     * @param int $len The number of bytes to skip.
     * @return int The actual number of bytes skipped.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::skipBytes()
     */
    public function skipBytes($len)
    {
        return $this->bin->skipBytes($len);
    }

    /**
     * Reads a string in.
     *
     * @return string The string.
     * @throws IOException if an I/O exception occurs.
     * @see \KM\IO\DataInput::readUTF()
     */
    public function readUTF()
    {
        return $this->bin->readUTF();
    }

    /**
     * Clears internal data structures.
     */
    private function clear()
    {
        $this->handles->clear();
    }

    /**
     * Underlying read object implementation.
     *
     * @param boolean $unshared
     * @throws IOException
     */
    public function readObject0($unshared)
    {
        $oldMode = $this->bin->getBlockDataMode();
        if ($oldMode) {
            $remain = $this->bin->currentBlockRemaining();
            if ($remain > 0) {
                throw new OptionalDataException($remain);
            } elseif ($this->defaultDataEnd) {
                // Stream is currently at the end of a field value block written
                // via default serialization. Since there is not terminating
                // TC_ENDBLOCKDATA tag, simulate end-of-custom-data behavior
                // explicitly.
                throw new OptionalDataException(true);
            }
            $this->bin->setBlockDataMode(false);
        }
        
        $tc = null;
        while (($tc = $this->bin->peekByte()) == self::TC_RESET) {
            $this->bin->readSingleByte();
            $this->handleReset();
        }
        
        $this->depth++;
        try {
            switch ($tc) {
                case self::TC_NULL:
                    return $this->readNull();
                
                case self::TC_REFERENCE:
                    return $this->readHandle($unshared);
                
                case self::TC_CLASS:
                    return $this->readClass($unshared);
                
                case self::TC_CLASSDESC:
                case self::TC_PROXYCLASSDESC:
                    return $this->readClassDesc($unshared);
                
                case self::TC_STRING:
                case self::TC_LONGSTRING:
                    return $this->readString($unshared);
                
                case self::TC_ARRAY:
                    return $this->readArray($unshared);
                
                case self::TC_ENUM:
                    return $this->readEnum($unshared);
                
                case self::TC_OBJECT:
                    return $this->readOrdinaryObject($unshared);
                
                case self::TC_EXCEPTION:
                    $ex = $this->readFatalException();
                    throw new WriteAbortedException('writing aborted', $ex);
                
                case self::TC_BLOCKDATA:
                case self::TC_BLOCKDATALONG:
                    if ($oldMode) {
                        $this->bin->setBlockDataMode(true);
                        $this->bin->peek();
                        throw new OptionalDataException(
                            $this->bin->currentBlockRemaining());
                    } else {
                        throw new StreamCorruptedException(
                            'unexpected block data');
                    }
                
                case self::TC_ENDBLOCKDATA:
                    if ($oldMode) {
                        throw new OptionalDataException(true);
                    } else {
                        throw new StreamCorruptedException(
                            'unexpected end of block data');
                    }
                
                default:
                    $format = 'invalid type code %02X';
                    throw new StreamCorruptedException(sprintf($format, $tc));
            }
        } finally {
            $this->depth--;
            $this->bin->setBlockDataMode($oldMode);
        }
    }

    /**
     * Reads string without allowing it to be replaced in stream.
     *
     * @throws StreamCorruptedException
     * @return string
     */
    public function readTypeString()
    {
        $oldHandle = $this->passHandle;
        try {
            $tc = $this->bin->peekByte();
            switch ($tc) {
                case self::TC_NULL:
                    return $this->readNull();
                
                case self::TC_REFERENCE:
                    return $this->readHandle(false);
                
                case self::TC_STRING:
                case self::TC_LONGSTRING:
                    return $this->readString(false);
                
                default:
                    $format = 'invalid type code %02X';
                    throw new StreamCorruptedException(sprintf($format, $tc));
            }
        } finally {
            $this->passHandle = $oldHandle;
        }
    }

    /**
     * Reads in null code and returns null.
     *
     * @return null
     */
    private function readNull()
    {
        if ($this->bin->readSingleByte() != self::TC_NULL) {
            trigger_error('internal error');
        }
        $this->passHandle = self::NULL_HANDLE;
        return null;
    }

    /**
     * Reads in object handle, sets passHandle to the read handle, and returns
     * object associated with the handle.
     *
     * @param boolean $unshared
     */
    private function readHandle($unshared)
    {
        if ($this->bin->readSingleByte() != self::TC_REFERENCE) {
            trigger_error('internal error');
        }
        $this->passHandle = $this->bin->readInt() - self::BASE_WIRE_HANDLE;
        if ($this->passHandle < 0 || $this->passHandle >= $this->handles->size()) {
            $format = 'invalid handle value %08X';
            throw new StreamCorruptedException(
                sprintf($format, $this->passHandle + self::BASE_WIRE_HANDLE));
        }
        if ($unshared) {
            throw new InvalidObjectException(
                'cannot read back reference as unshared');
        }
        
        $obj = $this->handles->lookupObject($this->passHandle);
        if ($obj === self::$unsharedMarker) {
            throw new InvalidObjectException(
                'cannot read back reference to unshared object');
        }
        return $obj;
    }

    /**
     * Reads in and returns class object.
     * Sets passHandle to class object's assigned handle. Returns null if class
     * is unresolvable (in which case a ClassNotFoundException will be
     * associated with the class' handle in the handle table).
     *
     * @param boolean $unshared
     * @return \KM\Lang\Clazz
     */
    private function readClass($unshared)
    {
        if ($this->bin->readSingleByte() != self::TC_CLASS) {
            trigger_error('internal error');
        }
        $desc = $this->readClassDesc();
        $cl = $desc->forClass();
        $this->passHandle = $this->handles->assign(
            $unshared ? self::$unsharedMarker : $cl);
        
        $this->handles->finish($this->passHandle);
        return $cl;
    }

    /**
     * Reads in and returns (possibly null) class descriptor.
     * Sets passHandle to class descriptor's assigned handle. If class
     * descriptor cannot be resolved to a class in the local VM, a
     * ClassNotFoundException is associated with the class descriptor's handle.
     *
     * @param boolean $unshared
     * @return \KM\IO\ObjectStreamClass
     */
    private function readClassDesc($unshared)
    {
        $tc = $this->bin->peekByte();
        switch ($tc) {
            case self::TC_NULL:
                return $this->readNull();
            
            case self::TC_REFERENCE:
                return $this->readHandle($unshared);
            
            case self::TC_PROXYCLASSDESC:
                return $this->readProxyDesc($unshared);
            
            case self::TC_CLASSDESC:
                return $this->readNonProxyDesc($unshared);
            
            default:
                $format = 'invalid type code %02X';
                throw new StreamCorruptedException(sprintf($format, $tc));
        }
    }

    /**
     * Reads in and returns class descriptor for a dynamic proxy class.
     * Sets passHandle to proxy class descriptor's assigned handle. If proxy
     * class descriptor cannot be resolved to a class in the local VM, a
     * ClassNotFoundException is associated with the descriptor's handle.
     *
     * @param boolean $unshared
     * @return \KM\IO\ObjectStreamClass
     */
    private function readProxyDesc($unshared)
    {
        if ($this->bin->readSingleByte() != self::TC_PROXYCLASSDESC) {
            trigger_error('internal error');
        }
        
        $desc = ObjectStreamClass::getInstance();
        $descHandle = $this->handles->assign(
            $unshared ? self::$unsharedMarker : $desc);
        $this->passHandle = self::NULL_HANDLE;
        
        $numIfaces = $this->bin->readInt();
        $ifaces = [];
        for ($i = 0; $i < $numIfaces; $i++) {
            $ifaces[$i] = $this->bin->readUTF();
        }
        
        $cl = null;
        $resolveEx = null;
        $this->bin->setBlockDataMode(true);
        try {
            if (($cl = $this->resolveProxyClass($ifaces)) == null) {
                $resolveEx = new ClassNotFoundException('null class');
            } elseif (!Proxy::isProxyClass($cl)) {
                throw new InvalidClassException('Not a proxy');
            }
        } catch (ClassNotFoundException $e) {
            $resolveEx = $e;
        }
        $this->skipCustomData();
        
        $desc->initProxy($cl, $this->readClassDesc());
        
        $this->handles->finish($descHandle);
        $this->passHandle = $descHandle;
        return $desc;
    }

    /**
     * Reads in and returns class descriptor for a class that is not a dynamic
     * proxy class.
     * Sets passHandle to class descriptor's assigned handle. If class
     * descriptor cannot be resolved to a class in the local VM, a
     * ClassNotFoundException is associated with the descriptor's handle.
     *
     * @param boolean $unshared
     * @return \KM\IO\ObjectStreamClass
     */
    private function readNonProxyDesc($unshared)
    {
        /* @var $readDesc ObjectStreamClass */
        /* @var $resolveEx ClassNotFoundException */
        if ($this->bin->readSingleByte() != self::TC_CLASSDESC) {
            trigger_error('internal error');
        }
        
        $desc = ObjectStreamClass::getInstance();
        $descHandle = $this->handles->assign(
            $unshared ? self::$unsharedMarker : $desc);
        $this->passHandle = self::NULL_HANDLE;
        
        $readDesc = null;
        try {
            $readDesc = $this->readClassDescriptor();
        } catch (ClassNotFoundException $e) {
            throw new InvalidClassException('failed to read class descriptor');
        }
        
        $cl = null;
        $resolveEx = null;
        $this->bin->setBlockDataMode(true);
        try {
            if (($cl = $this->resolveClass($readDesc)) == null) {
                $resolveEx = new ClassNotFoundException('null class');
            }
        } catch (ClassNotFoundException $e) {
            $resolveEx = $e;
        }
        $this->skipCustomData();
        
        $desc->initNonProxy($readDesc, $cl, $this->readClassDesc(false));
        
        $this->handles->finish($descHandle);
        $this->passHandle = $descHandle;
        return $desc;
    }

    /**
     * Reads in and returns new string.
     * Sets passHandle to new string's assigned handle.
     *
     * @param boolean $unshared
     * @return string
     */
    private function readString($unshared)
    {
        $str = '';
        $tc = $this->bin->readSingleByte();
        switch ($tc) {
            case self::TC_STRING:
                $str = $this->bin->readUTF();
                break;
            
            case self::TC_LONGSTRING:
                $str = $this->bin->readLongUTF();
                break;
            
            default:
                $format = 'invalid type code %02X';
                throw new StreamCorruptedException(sprintf($format, $tc));
        }
        $this->passHandle = $this->handles->assign(
            $unshared ? self::$unsharedMarker : $str);
        $this->handles->finish($this->passHandle);
        return $str;
    }

    /**
     * Reads a mixed value
     *
     * @param boolean $unshared
     * @return mixed
     */
    public function readMixed($unshared)
    {
        $val = null;
        $tc = $this->readSingleByte();
        switch ($tc) {
            case 'Z':
                $val = $this->bin->readBoolean();
                break;
            case 'S':
                $val = $this->bin->readInt();
                break;
            case 'I':
                $val = $this->bin->readInt();
                break;
            case 'J':
                $val = $this->bin->readInt();
                break;
            case 'F':
                $val = $this->bin->readFloat();
                break;
            case 'D':
                $val = $this->bin->readFloat();
                break;
            case 'C':
                $val = $this->bin->readUTF();
                break;
            case '[':
                $val = $this->readArray($unshared);
                break;
            case 'L':
                $val = $this->readObject0($unshared);
                break;
            default:
                trigger_error('internal error');
        }
        return $val;
    }

    /**
     * Reads in and returns array object.
     *
     * @return array
     */
    public function readArray($unshared)
    {
        if ($this->bin->readSingleByte() != self::TC_ARRAY) {
            trigger_error('internal error');
        }
        
        $ccl = null;
        if ($this->bin->peekByte() == self::TC_CLASSDESC) {
            $desc = $this->readClassDesc(false);
            $ccl = $desc->forClass();
        } else {
            $typeCode = $this->readSingleByte();
            $typeName = $this->readUTF();
            $ccl = ReflectionUtility::typeFor($typeName);
        }
        $len = $this->bin->readInt();
        $array = ($len == 0) ? [] : array_fill(0, $len, null);
        
        $arrayHandle = $this->handles->assign(
            $unshared ? self::$unsharedMarker : $array);
        
        if ($ccl === null) {
            for ($i = 0; $i < $len; $i++) {
                $this->readObject0(false);
            }
        } elseif ($ccl->isPrimitive()) {
            if ($ccl == PrimitiveType::INTEGER()) {
                $this->bin->readInts($array, 0, $len);
            } elseif ($ccl == PrimitiveType::LONG()) {
                $this->bin->readInts($array, 0, $len);
            } elseif ($ccl == PrimitiveType::FLOAT()) {
                $this->bin->readFloats($array, 0, $len);
            } elseif ($ccl == PrimitiveType::DOUBLE()) {
                $this->bin->readFloats($array, 0, $len);
            } elseif ($ccl == PrimitiveType::SHORT()) {
                $this->bin->readInts($array, 0, $len);
            } elseif ($ccl == PrimitiveType::BOOLEAN()) {
                $this->bin->readBooleans($array, 0, $len);
            } elseif ($ccl == PrimitiveType::STRING()) {
                for ($i = 0; $i < $len; $i++) {
                    $array[$i] = $this->bin->readUTF();
                }
            } else {
                trigger_error('internal error');
            }
        } elseif ($ccl->isMixed()) {
            for ($i = 0; $i < $len; $i++) {
                $array[$i] = $this->readMixed(false);
            }
        } elseif ($ccl->isArray()) {
            for ($i = 0; $i < $len; $i++) {
                $array[$i] = $this->readArray(false);
            }
        } else {
            for ($i = 0; $i < $len; $i++) {
                $array[$i] = $this->readObject0(false);
                $this->handles->markDependency($arrayHandle, $this->passHandle);
            }
        }
        $this->handles->finish($arrayHandle);
        $this->passHandle = $arrayHandle;
        return $array;
    }

    /**
     * Reads in and returns enum constant or null if enum type cannot be
     * resolved.
     * Sets passHandle to enum constant's assigned handle.
     *
     * @param boolean $unshared
     * @return \KM\Lang\Enum
     */
    private function readEnum($unshared)
    {
        /* @var $result Enum */
        if ($this->bin->readSingleByte() != self::TC_ENUM) {
            trigger_error('internal error');
        }
        
        $desc = $this->readClassDesc();
        if (!$desc->isEnum()) {
            throw new InvalidClassException('non-enum class: ' . $desc);
        }
        
        $enumHandle = $this->handles->assign(
            $unshared ? self::$unsharedMarker : null);
        
        $name = $this->readString();
        $result = null;
        $cl = $desc->forClass();
        if ($cl != null) {
            try {
                $result = Enum::valueOf($cl, $name);
            } catch (IllegalArgumentException $e) {
                throw new InvalidClassException(
                    'enum constant ' . $name . ' does not exist in ' . $cl);
            }
            if (!$unshared) {
                $this->handles->setObject($enumHandle, $result);
            }
        }
        
        $this->handles->finish($enumHandle);
        $this->passHandle = $enumHandle;
        return $result;
    }

    /**
     * Reads and returns "ordinary" (i.e., not a String, Class,
     * ObjectStreamClass, array, or enum constant) object, or null if object's
     * class cannot be resolved (in which case a ClassNotFoundException will be
     * associated with object's handle).
     *
     * @param boolean $unshared
     * @return \KM\Lang\Object
     */
    private function readOrdinaryObject($unshared)
    {
        /* @var $obj \KM\Lang\Object */
        if ($this->bin->readSingleByte() != self::TC_OBJECT) {
            trigger_error(__METHOD__ . ': internal error');
        }
        
        $desc = $this->readClassDesc(false);
        $desc->checkDeserialize();
        
        $cl = $desc->forClass();
        if ($cl == PrimitiveType::STRING || $cl == Clazz::clazz() ||
             $cl == ObjectStreamClass::clazz()) {
            throw new InvalidClassException('invalid class descriptor');
        }
        
        $obj = null;
        try {
            $obj = $desc->isInstantiable() ? $desc->newInstance() : null;
        } catch (\Exception $e) {
            throw new InvalidClassException('unable to create instance',
                $desc->forClass()->getName());
        }
        
        $this->passHandle = $this->handles->assign(
            $unshared ? self::$unsharedMarker : $obj);
        
        $this->readSerialData($obj, $desc);
        
        $this->handles->finish($this->passHandle);
        return $obj;
    }

    /**
     * Reads (or attempts to skip, if obj is null or is tagged with a
     * ClassNotFoundException) instance data for each serializable class of
     * object in stream, from superclass to subclass.
     *
     * @param Object $obj
     * @param ObjectStreamClass $desc
     */
    private function readSerialData(Object $obj = null, ObjectStreamClass $desc)
    {
        /* @var $slotDesc ObjectStreamClass */
        $slots = $desc->getClassDataLayout();
        for ($i = 0; $i < count($slots); $i++) {
            $slotDesc = $slots[$i]->desc;
            
            if ($slots[$i]->hasData) {
                if ($obj != null && $slotDesc->hasReadObjectMethod() &&
                     $this->handles->lookupException($this->passHandle) === null) {
                    $oldContext = $this->curContext;
                    
                    try {
                        $this->curContext = new SerialCallbackContext($obj,
                            $slotDesc);
                        
                        $this->bin->setBlockDataMode(true);
                        $slotDesc->invokeReadObject($obj, $this);
                    } catch (ClassNotFoundException $ex) {
                        // In most cases, the handle table has already
                        // ptopagated a ClassNotFoundException to passHandle at
                        // this point. This mark call is included to address
                        // cases where the custom readObject method has
                        // constructed and thrown a new ClassNotFoundException
                        // of its own.
                        $this->handles->markException($this->passHandle, $ex);
                    } finally {
                        $this->curContext->setUsed();
                        $this->curContext = $oldContext;
                    }
                    
                    // defaultDataEnd may have been set indirectly by custom
                    // readObject() method when calling defaultReadObject() or
                    // readFields(). Clear it to restore normal read behavior.
                    $this->defaultDataEnd = false;
                } else {
                    $this->defaultReadFields($obj, $slotDesc);
                }
                if ($slotDesc->hasWriteObjectData()) {
                    $this->skipCustomData();
                } else {
                    $this->bin->setBlockDataMode(false);
                }
            }
        }
    }

    /**
     * Skips over all block data and objects until TC_ENDBLOCKDATA is
     * encountered.
     */
    private function skipCustomData()
    {
        $oldHandle = $this->passHandle;
        for (;;) {
            if ($this->bin->getBlockDataMode()) {
                $this->bin->skipBlockData();
                $this->bin->setBlockDataMode(false);
            }
            switch ($this->bin->peekByte()) {
                case self::TC_BLOCKDATA:
                case self::TC_BLOCKDATALONG:
                    $this->bin->setBlockDataMode(true);
                    break;
                
                case self::TC_ENDBLOCKDATA:
                    $this->bin->readSingleByte();
                    $this->passHandle = $oldHandle;
                    return;
                
                default:
                    $this->readObject0(false);
                    break;
            }
        }
    }

    /**
     * Reads in values of serializable fields declared by given class
     * descriptor.
     * If obj is non-null, sets field values in obj.
     *
     * @param Object $obj
     * @param ObjectStreamClass $desc
     * @throws ClassCastException
     */
    private function defaultReadFields(Object $obj = null, ObjectStreamClass $desc)
    {
        /* @var $f ObjectStreamField */
        $cl = $desc->forClass();
        if ($cl != null && $obj != null && !$cl->isInstance($obj)) {
            throw new ClassCastException();
        }
        
        $primDataSize = $desc->getPrimDataSize();
        if ($this->primVals == null || count($this->primVals) < $primDataSize) {
            $this->primVals = ($primDataSize == 0) ? [] : array_fill(0,
                $primDataSize, null);
        }
        $this->bin->readFully($this->primVals, 0, $primDataSize, false);
        if ($obj != null) {
            $desc->setPrimFieldValues($obj, $this->primVals);
        }
        
        $objHandle = $this->passHandle;
        $fields = $desc->getFields(false);
        $objVals = ($desc->getNumObjectFields() == 0) ? [] : array_fill(0,
            $desc->getNumObjectFields(), null);
        $numPrimFields = count($fields) - count($objVals);
        for ($i = 0; $i < count($objVals); $i++) {
            $f = $fields[$numPrimFields + $i];
            if ($f->isArray()) {
                $objVals[$i] = $this->readArray($f->isUnshared());
            } elseif ($f->isMixed()) {
                $objVals[$i] = $this->readMixed($f->isUnshared());
            } elseif ($f->getType() == PrimitiveType::STRING()) {
                $objVals[$i] = $this->readString($f->isUnshared());
            } else {
                $objVals[$i] = $this->readObject0($f->isUnshared());
            }
            if ($f->getField() != null) {
                $this->handles->markDependency($objHandle, $this->passHandle);
            }
        }
        if ($obj != null) {
            $desc->setObjFieldValues($obj, $objVals);
        }
        $this->passHandle = $objHandle;
    }

    /**
     * Reads in and returns IOException that caused serialization to abort.
     * All stream state is discarded prior to reading in fatal exception.
     *
     * @return IOException
     */
    private function readFatalException()
    {
        if ($this->bin->readSingleByte() != self::TC_EXCEPTION) {
            trigger_error('internal error');
        }
        $this->clear();
        return $this->readObject0();
    }

    /**
     * If recursion depth is 0, clears internal data structures; otherwise,
     * throws a StreamCorruptedException.
     * This method is called when a TC_RESET type code is encountered.
     *
     * @throws StreamCorruptedException
     */
    private function handleReset()
    {
        if ($this->depth > 0) {
            throw new StreamCorruptedException(
                'unexpected reset: recursion depth: ' . $this->depth);
        }
        $this->clear();
    }
}
?>