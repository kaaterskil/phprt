<?php

/**
 * Kaaterskil Library
 *
 * PHP version 5.5
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KAATERSKIL MANAGEMENT, LLC BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Kaaterskil
 * @copyright   Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version     SVN $Id$
 */
namespace KM\NIO;

use KM\Lang\Comparable;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\UnsupportedOperationException;
use KM\Lang\Object;
use KM\Lang\ClassCastException;

/**
 * ByteBuffer Class
 *
 * @package KM\NIO
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class ByteBuffer extends Buffer implements Comparable {
	
	/**
	 * The underlying array.
	 * @var array
	 */
	protected $hb;
	
	/**
	 * The array offset.
	 * @var int
	 */
	protected $offset;
	
	/**
	 * True if this buffer is read-only.
	 * @var boolean
	 */
	protected $isReadOnly;

	/**
	 * Creates a new buffer with the given mark, position, limit, capacity, backing array and array
	 * offset.
	 * @param int $mark
	 * @param int $pos
	 * @param int $lim
	 * @param int $cap
	 * @param array $hb
	 * @param int $offset
	 */
	public function __construct($mark, $pos, $lim, $cap, array &$hb = null, $offset = 0) {
		parent::__construct( $mark, $pos, $lim, $cap );
		$this->hb = $hb;
		$this->offset = (int) $offset;
		$this->nativeByteOrder = (ByteOrder::nativeOrder() == ByteOrder::BIG_ENDIAN());
	}

	/**
	 * Allocates a new byte buffer.
	 * The new buffer's position will be zero, its limit will be its capacity, its mark will be
	 * undefined and each of its elements will be initialized to zero. It will have a backing array
	 * and its arrayOffset will be zero.
	 * @param int $capacity The new buffer's capacity.
	 * @throws \InvalidArgumentException if the $capacity is a negative integer.
	 * @return \KM\NIO\HeapByteBuffer The new byte buffer.
	 */
	public static function allocate($capacity) {
		$capacity = (int) $capacity;
		if ($capacity < 0) {
			throw new \InvalidArgumentException();
		}
		return new HeapByteBuffer(-1, 0, $capacity, $capacity );
	}

	/**
	 * Wraps a byte array into a buffer.
	 *
	 * <p> The new buffer will be backed by the given byte array; that is, modifications to the
	 * buffer will cause the array to be modified and vice versa. The new buffer's capacity will be
	 * <tt>array.length</tt>, its position will be <tt>offset</tt>, its limit will be <tt>offset +
	 * length</tt>, and its mark will be undefined. Its {@link #array backing array} will be the
	 * given array, and its {@link #arrayOffset array offset} will be zero. </p>
	 * @param array $array The array that will back the new buffer.
	 * @param int $offset The offset of the sub-array to be used; must be non-negative and no larger
	 *        than count(array). The new buffer's position will be set at this value.
	 * @param int $length The length of the sub-array to be used; must be non-negative and no larger
	 *        than count(array) - offset, The new buffer's limit will be set to offset + length.
	 * @throws IndexOutOfBoundsException of the preconditions of the $offset and $length parameters
	 *         do not hold.
	 * @return \KM\NIO\HeapByteBuffer The new byte buffer.
	 */
	public static function wrap(array &$array, $offset = 0, $length = null) {
		$offset = (int) $offset;
		if ($length == null) {
			$length = count( $array );
		}
		$length = (int) $length;
		try {
			return new HeapByteBuffer( -1, $offset, $offset + $length, count( $array ), $array, 0 );
		} catch ( \InvalidArgumentException $e ) {
			throw new IndexOutOfBoundsException();
		}
	}

	/**
	 * Creates a new byte buffer whose content is a shared subsequence of this buffer's content.
	 *
	 * <p> The content of the new buffer will start at this buffer's current position. Changes to
	 * this buffer's content will be visible in the new buffer, and vice versa; the two buffers'
	 * position, limit, and mark values will be independent.
	 *
	 * <p> The new buffer's position will be zero, its capacity and its limit will be the number of
	 * bytes remaining in this buffer, and its mark will be undefined. The new buffer will be direct
	 * if, and only if, this buffer is direct, and it will be read-only if, and only if, this buffer
	 * is read-only. </p>
	 * @return \KM\NIO\ByteBuffer This buffer.
	 */
	public abstract function slice();

	/**
	 * Creates a new byte buffer that shares this buffer's content.
	 *
	 * <p> The content of the new buffer will be that of this buffer. Changes to this buffer's
	 * content will be visible in the new buffer, and vice versa; the two buffers' position, limit,
	 * and mark values will be independent.
	 *
	 * <p> The new buffer's capacity, limit, position, and mark values will be identical to those of
	 * this buffer. The new buffer will be direct if, and only if, this buffer is direct, and it
	 * will be read-only if, and only if, this buffer is read-only. </p>
	 * @return \KM\NIO\ByteBuffer This buffer.
	 */
	public abstract function duplicate();

	/**
	 * Creates a new, read-only byte buffer that shares this buffer's content.
	 *
	 * <p> The content of the new buffer will be that of this buffer. Changes to this buffer's
	 * content will be visible in the new buffer; the new buffer itself, however, will be read-only
	 * and will not allow the shared content to be modified. The two buffers' position, limit, and
	 * mark values will be independent.
	 *
	 * <p> The new buffer's capacity, limit, position, and mark values will be identical to those of
	 * this buffer.
	 *
	 * <p> If this buffer is itself read-only then this method behaves in exactly the same way as
	 * the {@link #duplicate duplicate} method. </p>
	 * @return \KM\NIO\ByteBuffer This buffer.
	 */
	public abstract function asReadOnlyBuffer();

	/**
	 * Absolute getByte() method.
	 * Reads the byte at the given index.
	 * @param int $index The index from which the byte will be read or, if null, the current
	 *        position.
	 * @return string The byte at the given index.
	 */
	public abstract function getByte($index = null);

	/**
	 * Absolute putByte() method.
	 * Writes the given byte into this buffer at the given index.
	 * @param string $b The byte value to be written
	 * @param int $index The index at which the byte will be written or, if null, the current
	 *        position.
	 * @return \KM\NIO\ByteBuffer This buffer.
	 */
	public abstract function putByte($b, $index = null);

	/**
	 * Relative bulk get() method.
	 * This method transfers bytes from this buffer into the given destination array. If there are
	 * fewer bytes remaining in the buffer than are required to satisfy the request. that is, if
	 * $length > remaining(), than no bytes are transferred and a BufferUnderflowException is
	 * thrown.
	 * Otherwise, this method copies $length bytes from this buffer into the given array, starting
	 * at the current position of this buffer and at the given offset in the array. The position of
	 * this buffer is then incremented by $length.
	 * @param array $dst The array into which bytes are to be written.
	 * @param int $offset The offset within the array of the first byte to be written; must be
	 *        non-negative and no larger than count(dst).
	 * @param int $length The maximum number of bytes to be written to the given array; must be
	 *        non-negative and no larger than count(dst) - offset.
	 * @throws BufferUnderflowException if there are fewer than $length bytes remaining in this
	 *         buffer.
	 * @return \KM\NIO\ByteBuffer
	 */
	public function get(array &$dst, $offset = 0, $length = null) {
		$offset = (int) $offset;
		if ($length == null) {
			$length = count( $dst );
		}
		$length = (int) $length;
		
		self::checkBounds( $offset, $length, count( $dst ) );
		if ($length > $this->remaining()) {
			throw new BufferUnderflowException();
		}
		$end = $offset + $length;
		for($i = $offset; $i < $end; $i++) {
			$dst[$i] = $this->getByte();
		}
		return $this;
	}

	/**
	 * Relative bulk put() method.
	 * This method transfers the bytes remaining in the given source buffer into this buffer. IF
	 * there are more bytes remaining in the source buffer than in this buffer, that is. if
	 * src.remaining() > this.remaining(), then no bytes are transferred and a
	 * BufferOverflowException is thrown. Otherwise, this method copies $n=src.remaining() bytes
	 * from the given buffer into this buffer, starting at each buffer's current position. The
	 * positions of both buffers are then incremented by $n.
	 * @param ByteBuffer $src The source buffer from which bytes are to be read; must not be this
	 *        buffer.
	 * @throws \InvalidArgumentException if the source buffer is this buffer.
	 * @throws ReadOnlyBufferException if this buffer is read-only.
	 * @throws BufferOverflowException if there is insufficient space in this buffer for the
	 *         remaining bytes in the source buffer.
	 * @return \KM\NIO\ByteBuffer This buffer.
	 */
	public function putBuffer(ByteBuffer $src) {
		if ($src === $this) {
			throw new \InvalidArgumentException();
		}
		if ($this->isReadOnly()) {
			throw new ReadOnlyBufferException();
		}
		$n = $src->remaining();
		if ($n > $this->remaining()) {
			throw new BufferOverflowException();
		}
		for($i = 0; $i < $n; $i++) {
			$this->putByte( $src->getByte() );
		}
		return $this;
	}

	/**
	 * Relative bulk put() method.
	 * This method transfers bytes into this buffer from the given source array. If there are more
	 * bytes to be copied from the array than remain in this buffer, that is, if getLength() >
	 * remaining(), then no bytes are transferred and a BufferOverflowException is thrown.
	 * Otherwise, this method copies $length bytes from the given array into this buffer, starting
	 * at the given offset in the array and at the current position of this buffer. The position of
	 * this buffer is then incremented by $length.
	 * @param array $src The array from which bytes are to be read.
	 * @param int $offset The offset within the array of the first byte to be read: must be
	 *        non-negative and no larger than count(array).
	 * @param int $length The number of bytes to be read from the given array: must be non-negative
	 *        and no larger than count(array) - offset.
	 * @throws BufferOverflowException If there is insufficient space in this buffer.
	 * @return \KM\NIO\ByteBuffer This buffer.
	 */
	public function put(array &$src, $offset = 0, $length = null) {
		$offset = (int) $offset;
		if ($length == null) {
			$length = count( $src );
		}
		$length = (int) $length;
		
		self::checkBounds( $offset, $length, count( $src ) );
		if ($length > $this->remaining()) {
			throw new BufferOverflowException();
		}
		$end = $offset + $length;
		for($i = $offset; $i < $end; $i++) {
			$this->putByte( $src[$i] );
		}
		return $this;
	}
	
	/* ---------- Other stuff ---------- */
	
	/**
	 * Tells whether or not this buffer is backed by an accessible byte array.
	 * @return boolean True if and only if this buffer is backed by an array and is not read-only.
	 * @see \KM\NIO\Buffer::hasArray()
	 */
	public final function hasArray() {
		return ($this->hb != null) && !$this->isReadOnly();
	}

	/**
	 * Returns the byte array that backs this buffer.
	 *
	 * Modifications to this buffer's content will cause the returned array's content to be
	 * modified, and vice versa.
	 *
	 * Invoke the hasArray() method before invoking this method in order to ensure that this bugger
	 * has an accessible backing array.
	 *
	 * @throws UnsupportedOperationException if this buffer is not backed by an accessible array.
	 * @throws ReadOnlyBufferException if this buffer is backed by an array that is read-only.
	 * @return array The array that backs this buffer.
	 * @see \KM\NIO\Buffer::toArray()
	 */
	public final function &toArray() {
		if ($this->hb == null) {
			throw new UnsupportedOperationException();
		}
		if ($this->isReadOnly()) {
			throw new ReadOnlyBufferException();
		}
		return $this->hb;
	}

	/**
	 * Returns the offset within this buffer's backing array of the first element of the buffer.
	 *
	 * If this buffer is backed by an array then buffer position $p corresponds to array index $p +
	 * arrayOPffset().
	 *
	 * Invoke the hasArray() method before invoking this method to ensure that this buffer has an
	 * accessible backing array.
	 *
	 * @throws UnsupportedOperationException if this buffer is not backed by an accessible array.
	 * @throws ReadOnlyBufferException if this buffer is backed by an array that is read-only.
	 * @return int The offset within this buffer's array of the first element of the buffer.
	 * @see \KM\NIO\Buffer::arrayOffset()
	 */
	public final function arrayOffset() {
		if ($this->hb == null) {
			throw new UnsupportedOperationException();
		}
		if ($this->isReadOnly()) {
			throw new ReadOnlyBufferException();
		}
		return $this->offset;
	}

	/**
	 * Compacts this buffer.
	 * @return \KM\NIO\ByteBuffer This buffer.
	 */
	public abstract function compact();

	/**
	 * Returns a string summarizing the state of this buffer.
	 * @return string
	 * @see \KM\Lang\Object::__toString()
	 */
	public function __toString() {
		$sb = $this->getClass()->getName();
		$sb .= '[pos=' . $this->getPosition();
		$sb .= ' lim=' . $this->getLimit();
		$sb .= ' cap=' . $this->getCapacity();
		$sb .= ']';
		return $sb;
	}

	/**
	 * Tells whether or not this buffer is equal to another object.
	 * Two byte buffers are equal if any only if:
	 * <ol>
	 * <li>They have the same element type</li>
	 * <li>They have the same number of remaining elements, and</li>
	 * <li>The two sequences of remaining elements, considered independently of their starting
	 * positions, are point-wise equal.</li>
	 * </ol>
	 * A byte buffer is not equal to any other type of object.
	 * @param Object $obj The object to which this buffer is to be compared.
	 * @return boolean True if and only if this buffer is equal to the given object.
	 * @see \KM\Lang\Object::equals()
	 */
	public function equals(Object $obj = null) {
		/* @var $that ByteBuffer */
		if ($obj === $this) {
			return true;
		}
		if (!$obj instanceof ByteBuffer) {
			return false;
		}
		$that = $obj;
		if ($this->remaining() != $that->remaining()) {
			return false;
		}
		$p = $this->getPosition();
		for($i = $this->getLimit() - 1, $j = $that->getLimit() - 1; $i >= $p; $i--, $j--) {
			if (!self::equals0( $this->getByte( $i ), $that->getByte( $j ) )) {
				return false;
			}
		}
		return true;
	}

	private static function equals0($x, $y) {
		return $x == $y;
	}

	/**
	 * Compares this buffer to another.
	 * Two byte buffers are compared by comparing their sequences of remaining elements
	 * lexicographically, without regard to the starting position of each sequence within its
	 * corresponding buffer.
	 *
	 * A byte buffer is not comparable to any other type of object.
	 * @param Object $value
	 * @throws ClassCastException if the given value is not a byte buffer type.
	 * @return int A negative integer, zero, or a positive integer as this buffer is less-than,
	 *         equal-to, or greater-than the given buffer.
	 * @see \KM\Lang\Comparable::compareTo()
	 */
	public function compareTo(Object $value = null) {
		/* @var $that ByteBuffer */
		if (!$value instanceof ByteBuffer) {
			throw new ClassCastException();
		}
		$that = $value;
		$n = $this->getPosition() + min( array(
			$this->remaining(),
			$that->remaining()
		) );
		for($i = $this->getPosition(), $j = $that->getPosition(); $i < $n; $i++, $j++) {
			$cmp = self::compare0( $this->getByte( $i ), $that->getByte( $j ) );
			if ($cmp != 0) {
				return $cmp;
			}
		}
		return $this->remaining() - $that->remaining();
	}

	private static function compare0($x, $y) {
		return $x - $y;
	}
	
	/* ---------- Other byte stuff: Access to binary data ---------- */
	
	/**
	 * Returns true if the byte order of this buffer is big-endian.
	 * @var boolean
	 */
	public $bigEndian = true;
	
	/**
	 * Returns true if the native byte order of the underlying platform is big-endian.
	 * @var boolean
	 */
	public $nativeByteOrder;

	/**
	 * Returns this buffer's byte order.
	 * The byte order is used when reading or writing multibyte values, and when creating buffers
	 * that are views of this byte buffer. The order of a newly-created byte buffer is always big
	 * endian.
	 * @return \KM\NIO\ByteOrder This buffer's byte order.
	 */
	public final function order() {
		return $this->bigEndian ? ByteOrder::BIG_ENDIAN() : ByteOrder::LITTLE_ENDIAN();
	}

	/**
	 * Modifies this buffer's byte order.
	 * @param ByteOrder $bo The new byte order.
	 * @return \KM\NIO\ByteBuffer This buffer,
	 */
	public final function orderModify(ByteOrder $bo) {
		$this->bigEndian = ($bo == ByteOrder::BIG_ENDIAN());
		$this->nativeByteOrder = ($this->bigEndian == (ByteOrder::nativeOrder() == ByteOrder::BIG_ENDIAN()));
		return $this;
	}
}
?>