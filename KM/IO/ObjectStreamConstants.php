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

/**
 * Constants written into the Object Serialization Stream.
 *
 * @author Blair
 */
interface ObjectStreamConstants
{
    
    /* ---------- Stream header tags ---------- */
    /**
     * Magic number that is written to the stream header.
     */
    const STREAM_MAGIC = 0xaced;

    /**
     * Version number that is written to the stream header.
     */
    const STREAM_VERSION = 5;

    /* ---------- Item tags ---------- */
    
    /**
     * First tag value.
     */
    const TC_BASE = 0x70;

    /**
     * Null object reference.
     */
    const TC_NULL = 0x70;

    /**
     * Reference to an object already written into the stream.
     */
    const TC_REFERENCE = 0x71;

    /**
     * new Class Descriptor.
     */
    const TC_CLASSDESC = 0x72;

    /**
     * new Object.
     */
    const TC_OBJECT = 0x73;

    /**
     * new String.
     */
    const TC_STRING = 0x74;

    /**
     * new Array.
     */
    const TC_ARRAY = 0x75;

    /**
     * Reference to Class.
     */
    const TC_CLASS = 0x76;

    /**
     * Block of optional data.
     * Byte following tag indicates number of bytes in
     * this block data.
     */
    const TC_BLOCKDATA = 0x77;

    /**
     * End of optional block data blocks for an object.
     */
    const TC_ENDBLOCKDATA = 0x78;

    /**
     * Reset stream context.
     * All handles written into stream are reset.
     */
    const TC_RESET = 0x79;

    /**
     * long Block data.
     * The long following the tag indicates the number of bytes
     * in this block data.
     */
    const TC_BLOCKDATALONG = 0x7A;

    /**
     * Exception during write.
     */
    const TC_EXCEPTION = 0x7B;

    /**
     * Long string.
     */
    const TC_LONGSTRING = 0x7C;

    /**
     * new Proxy Class Descriptor.
     */
    const TC_PROXYCLASSDESC = 0x7D;

    /**
     * new Enum constant.
     */
    const TC_ENUM = 0x7E;

    /**
     * Added tag for mixed type
     */
    const TC_MIXED = 0x7f;

    /**
     * Last tag value.
     */
    // const TC_MAX = 0x7E;
    const TC_MAX = 0x7f;

    /**
     * First wire handle to be assigned.
     */
    const BASE_WIRE_HANDLE = 0x7e0000;
    
    /* ----------Bit masks for ObjectStreamClass flag---------- */
    
    /**
     * Bit mask for ObjectStreamClass flag.
     * Indicates a Serializable class
     * defines its own writeObject method.
     */
    const SC_WRITE_METHOD = 0x01;

    /**
     * Bit mask for ObjectStreamClass flag.
     * Indicates Externalizable data
     * written in Block Data mode. Added for PROTOCOL_VERSION_2.
     *
     * @see #PROTOCOL_VERSION_2
     * @since 1.2
     */
    const SC_BLOCK_DATA = 0x08;

    /**
     * Bit mask for ObjectStreamClass flag.
     * Indicates class is Serializable.
     */
    const SC_SERIALIZABLE = 0x02;

    /**
     * Bit mask for ObjectStreamClass flag.
     * Indicates class is Externalizable.
     */
    const SC_EXTERNALIZABLE = 0x04;

    /**
     * Bit mask for ObjectStreamClass flag.
     * Indicates class is an enum type.
     */
    const SC_ENUM = 0x10;

    /**
     * A Stream Protocol Version.
     * <p> All externalizable data is written in JDK
     * 1.1 external data format after calling this method. This version is
     * needed to write streams containing Externalizable data that can be read
     * by pre-JDK 1.1.6 JVMs.
     *
     * @see java.io.ObjectOutputStream#useProtocolVersion(int)
     * @since 1.2
     */
    const PROTOCOL_VERSION_1 = 1;

    /**
     * A Stream Protocol Version.
     * <p> This protocol is written by JVM 1.2.
     * Externalizable data is written in block data mode and is terminated with
     * TC_ENDBLOCKDATA. Externalizable class descriptor flags has SC_BLOCK_DATA
     * enabled. JVM 1.1.6 and greater can read this format change. Enables
     * writing a nonSerializable class descriptor into the stream. The
     * serialVersionUID of a nonSerializable class is set to 0L.
     *
     * @see java.io.ObjectOutputStream#useProtocolVersion(int)
     * @see #SC_BLOCK_DATA
     * @since 1.2
     */
    const PROTOCOL_VERSION_2 = 2;
}
?>