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
 * @category Kaaterskil
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
namespace KM\NIO;

use KM\Lang\Appendable;
use KM\Lang\CharSequence;
use KM\Lang\Comparable;
use KM\Lang\Object;
use KM\NIO\ReadOnlyBufferException;
use KM\NIO\BufferOverflowException;
use KM\Lang\ClassCastException;

/**
 * A char buffer.
 *
 * <p> This class defines four categories of operations upon char buffers:
 *
 * <ul>
 * <li><p> Absolute and relative {@link #get() <i>get</i>} and {@link #put(char) <i>put</i>} methods
 * that read and write single chars; </p></li>
 *
 * <li><p> Relative {@link #get(char[]) <i>bulk get</i>} methods that transfer contiguous sequences
 * of chars from this buffer into an array; and</p></li>
 *
 * <li><p> Relative {@link #put(char[]) <i>bulk put</i>} methods that transfer contiguous sequences
 * of chars from a char array,&#32;a&#32;string, or some other char buffer into this buffer;&#32;and
 * </p></li>
 *
 * <li><p> Methods for {@link #compact compacting}, {@link #duplicate duplicating}, and {@link
 * #slice slicing} a char buffer. </p></li>
 * </ul>
 *
 * <p> Char buffers can be created either by {@link #allocate
 * <i>allocation</i>}, which allocates space for the buffer's content, by {@link #wrap(char[])
 * <i>wrapping</i>} an existing char array or&#32;string into a buffer, or by creating a <a
 * href="ByteBuffer.html#views"><i>view</i></a> of an existing byte buffer.
 *
 * <p> Like a byte buffer, a char buffer is either <a
 * href="ByteBuffer.html#direct"><i>direct</i> or <i>non-direct</i></a>. A
 * char buffer created via the <tt>wrap</tt> methods of this class will
 * be non-direct. A char buffer created as a view of a byte buffer will
 * be direct if, and only if, the byte buffer itself is direct. Whether or not
 * a char buffer is direct may be determined by invoking the {@link
 * #isDirect isDirect} method. </p>
 *
 * <p> Methods in this class that do not otherwise have a value to return are
 * specified to return the buffer upon which they are invoked. This allows
 * method invocations to be chained.
 *
 * @package KM\NIO
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class CharBuffer extends Buffer implements Comparable, Appendable, CharSequence {
	
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
	}

	/**
	 * Allocates a new byte buffer.
	 * The new buffer's position will be zero, its limit will be its capacity, its mark will be
	 * undefined and each of its elements will be initialized to zero. It will have a backing array
	 * and its arrayOffset will be zero.
	 * @param int $capacity The new buffer's capacity.
	 * @throws \InvalidArgumentException if the $capacity is a negative integer.
	 * @return \KM\NIO\HeapCharBuffer The new char buffer.
	 */
	public static function allocate($capacity) {
		$capacity = (int) $capacity;
		if ($capacity < 0) {
			throw new \InvalidArgumentException();
		}
		return new HeapCharBuffer( -1, 0, $capacity, $capacity );
	}

	/**
	 * Wraps a char array into a buffer.
	 *
	 * <p> The new buffer will be backed by the given char array; that is, modifications to the
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
	 * @return \KM\NIO\HeapCharBuffer The new char buffer.
	 */
	public static function wrap(array &$array, $offset = 0, $length = null) {
		$offset = (int) $offset;
		if ($length == null) {
			$length = count( $array );
		}
		$length = (int) $length;
		try {
			return new HeapCharBuffer( -1, $offset, $offset + $length, count( $array ), $array, 0 );
		} catch ( \InvalidArgumentException $e ) {
			throw new IndexOutOfBoundsException();
		}
	}

	/**
	 * Attempts to read characters into the specified character buffer.
	 * The buffer is used as a repository of characters as-is: the only changes made are the results
	 * of a put operation. No flipping or rewinding of the buffer is performed.
	 * @param CharBuffer $target The buffer to read characters into.
	 * @return The number of characters added to the buffer, or -1 if this source of characters is
	 *         at its end.
	 */
	public function read(CharBuffer $target) {
		// Define the number of chars $n that can be transferred.
		$targetRemaining = $target->remaining();
		$remaining = $this->remaining();
		if ($remaining == 0) {
			return -1;
		}
		$n = min( array(
			$remaining,
			$targetRemaining
		) );
		$limit = $this->getLimit();
		// Set source limit to prevent target overflow.
		if ($targetRemaining < $remaining) {
			$this->setLimit( $this->getPosition() + $n );
		}
		if ($n > 0) {
			$target->putBuffer( $this );
		}
		$this->setLimit( $limit ); // Restore real limit.
		return $n;
	}

	/**
	 * Creates a new byte buffer whose content is a shared subsequence of this buffer's content.
	 *
	 * <p> The content of the new buffer will start at this buffer's current position. Changes to
	 * this buffer's content will be visible in the new buffer, and vice versa; the two buffers'
	 * position, limit, and mark values will be independent.
	 *
	 * <p> The new buffer's position will be zero, its capacity and its limit will be the number of
	 * chars remaining in this buffer, and its mark will be undefined. The new buffer will be direct
	 * if, and only if, this buffer is direct, and it will be read-only if, and only if, this buffer
	 * is read-only. </p>
	 * @return \KM\NIO\CharBuffer This buffer.
	 */
	public abstract function slice();

	/**
	 * Creates a new char buffer that shares this buffer's content.
	 *
	 * <p> The content of the new buffer will be that of this buffer. Changes to this buffer's
	 * content will be visible in the new buffer, and vice versa; the two buffers' position, limit,
	 * and mark values will be independent.
	 *
	 * <p> The new buffer's capacity, limit, position, and mark values will be identical to those of
	 * this buffer. The new buffer will be direct if, and only if, this buffer is direct, and it
	 * will be read-only if, and only if, this buffer is read-only. </p>
	 * @return \KM\NIO\CharBuffer This buffer.
	 */
	public abstract function duplicate();

	/**
	 * Creates a new, read-only char buffer that shares this buffer's content.
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
	 * @return \KM\NIO\CharBuffer This buffer.
	 */
	public abstract function asReadOnlyBuffer();

	/**
	 * Absolute getChar() method.
	 * Reads the char at the given index,m or the current position if no index is given.
	 * @param int $index The index from which the char will be read or, if null, the current
	 *        position.
	 * @return string The char at the given index.
	 */
	public abstract function getChar($index = null);

	/**
	 * Absolute putChar() method.
	 * Writes the given char into this buffer at the given index, or the current position if no
	 * index is given.
	 * @param string $b The char to be written
	 * @param int $index The index at which the char will be written or, if null, the current
	 *        position.
	 * @return \KM\NIO\CharBuffer This buffer.
	 */
	public abstract function putChar($b, $index = null);

	/**
	 * Relative bulk get() method.
	 * This method transfers chars from this buffer into the given destination array. If there are
	 * fewer chars remaining in the buffer than are required to satisfy the request. that is, if
	 * $length > remaining(), than no chars are transferred and a BufferUnderflowException is
	 * thrown.
	 * Otherwise, this method copies $length chars from this buffer into the given array, starting
	 * at the current position of this buffer and at the given offset in the array. The position of
	 * this buffer is then incremented by $length.
	 * @param array $dst The array into which chars are to be written.
	 * @param int $offset The offset within the array of the first char to be written; must be
	 *        non-negative and no larger than count(dst).
	 * @param int $length The maximum number of chars to be written to the given array; must be
	 *        non-negative and no larger than count(dst) - offset.
	 * @throws BufferUnderflowException if there are fewer than $length chars remaining in this
	 *         buffer.
	 * @return \KM\NIO\CharBuffer
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
			$dst[$i] = $this->getChar();
		}
		return $this;
	}

	/**
	 * Relative bulk put() method.
	 * This method transfers the chars remaining in the given source buffer into this buffer. IF
	 * there are more chars remaining in the source buffer than in this buffer, that is. if
	 * src.remaining() > this.remaining(), then no chars are transferred and a
	 * BufferOverflowException is thrown. Otherwise, this method copies $n=src.remaining() chars
	 * from the given buffer into this buffer, starting at each buffer's current position. The
	 * positions of both buffers are then incremented by $n.
	 * @param CharBuffer $src The source buffer from which chars are to be read; must not be this
	 *        buffer.
	 * @throws \InvalidArgumentException if the source buffer is this buffer.
	 * @throws ReadOnlyBufferException if this buffer is read-only.
	 * @throws BufferOverflowException if there is insufficient space in this buffer for the
	 *         remaining chars in the source buffer.
	 * @return \KM\NIO\CharBuffer This buffer.
	 */
	public function putBuffer(CharBuffer $src) {
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
			$this->putChar( $src->getChar() );
		}
		return $this;
	}

	/**
	 * Relative bulk put() method.
	 * This method transfers chars into this buffer from the given source array. If there are more
	 * chars to be copied from the array than remain in this buffer, that is, if getLength() >
	 * remaining(), then no chars are transferred and a BufferOverflowException is thrown.
	 * Otherwise, this method copies $length chars from the given array into this buffer, starting
	 * at the given offset in the array and at the current position of this buffer. The position of
	 * this buffer is then incremented by $length.
	 * @param array $src The array from which chars are to be read.
	 * @param int $offset The offset within the array of the first char to be read: must be
	 *        non-negative and no larger than count(array).
	 * @param int $length The number of chars to be read from the given array: must be non-negative
	 *        and no larger than count(array) - offset.
	 * @throws BufferOverflowException If there is insufficient space in this buffer.
	 * @return \KM\NIO\CharBuffer This buffer.
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
			$this->putChar( $src[$i] );
		}
		return $this;
	}

	/**
	 * Relative bulk put method.
	 * This method transfers chars from the given string into this buffer. If there are more chars
	 * to be copied from the string than remain in this buffer, that is, if $end - $start >
	 * remaining(), then no chars are transferred and a BufferOverflowException is thrown.
	 * Otherwise, this method copies $n = $end - $start chars from the given string into this
	 * buffer,
	 * starting at the given $start index and at the current position of this buffer, The position
	 * of this buffer is then incremented by $n.
	 * @param string $src The string from which chars are to be read.
	 * @param int $start The offset within the string of the first char to be read; must be
	 *        non-negative and no larger than strlen($src), or zero if no value is given.
	 * @param int $end The offset within the string of the last char to be read plus one; must be
	 *        non-negative and no larger than stlen($src), or the length of the string if no
	 *        value is given.
	 * @return \KM\NIO\CharBuffer This buffer.
	 */
	public function putString(&$src, $start = 0, $end = null) {
		$src = (string) $src;
		$start = (int) $start;
		if ($end == null) {
			$end = strlen( $src );
		}
		$end = (int) $end;
		
		self::checkBounds( $start, $end - $start, strlen( $src ) );
		if ($this->isReadOnly()) {
			throw new ReadOnlyBufferException();
		}
		if ($end - $start > $this->remaining()) {
			throw new BufferOverflowException();
		}
		for($i = $start; $i < $end; $i++) {
			$this->putChar( $src[$i] );
		}
		return $this;
	}
	
	/* ---------- Other stuff ---------- */
	
	/**
	 * Tells whether or not this buffer is backed by an accessible char array.
	 * @return boolean True if and only if this buffer is backed by an array and is not read-only.
	 * @see \KM\NIO\Buffer::hasArray()
	 */
	public final function hasArray() {
		return ($this->hb != null) && !$this->isReadOnly();
	}

	/**
	 * Returns the char array that backs this buffer.
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
	 * @return \KM\NIO\CharBuffer This buffer.
	 */
	public abstract function compact();

	/**
	 * Tells whether or not this buffer is equal to another object.
	 * Two char buffers are equal if any only if:
	 * <ol>
	 * <li>They have the same element type</li>
	 * <li>They have the same number of remaining elements, and</li>
	 * <li>The two sequences of remaining elements, considered independently of their starting
	 * positions, are point-wise equal.</li>
	 * </ol>
	 * A char buffer is not equal to any other type of object.
	 * @param Object $obj The object to which this buffer is to be compared.
	 * @return boolean True if and only if this buffer is equal to the given object.
	 * @see \KM\Lang\Object::equals()
	 */
	public function equals(Object $obj = null) {
		/* @var $that CharBuffer */
		if ($obj === $this) {
			return true;
		}
		if (!$obj instanceof CharBuffer) {
			return false;
		}
		$that = $obj;
		if ($this->remaining() != $that->remaining()) {
			return false;
		}
		$p = $this->getPosition();
		for($i = $this->getLimit() - 1, $j = $that->getLimit() - 1; $i >= $p; $i--, $j--) {
			if (!self::equals0( $this->getChar( $i ), $that->getChar( $j ) )) {
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
	 * Two char buffers are compared by comparing their sequences of remaining elements
	 * lexicographically, without regard to the starting position of each sequence within its
	 * corresponding buffer.
	 *
	 * A char buffer is not comparable to any other type of object.
	 * @param Object $value
	 * @throws ClassCastException if the given value is not a char buffer type.
	 * @return int A negative integer, zero, or a positive integer as this buffer is less-than,
	 *         equal-to, or greater-than the given buffer.
	 * @see \KM\Lang\Comparable::compareTo()
	 */
	public function compareTo(Object $value = null) {
		/* @var $that CharBuffer */
		if (!$value instanceof CharBuffer) {
			throw new ClassCastException();
		}
		$that = $value;
		$n = $this->getPosition() + min( array(
			$this->remaining(),
			$that->remaining()
		) );
		for($i = $this->getPosition(), $j = $that->getPosition(); $i < $n; $i++, $j++) {
			$cmp = self::compare0( $this->getChar( $i ), $that->getChar( $j ) );
			if ($cmp != 0) {
				return $cmp;
			}
		}
		return $this->remaining() - $that->remaining();
	}

	private static function compare0($x, $y) {
		return $x - $y;
	}
	
	/* ---------- Methods to support CharSequence ---------- */
	
	/**
	 * Returns a string containing the characters in this buffer.
	 * @return string
	 * @see \KM\Lang\Object::__toString()
	 */
	public function __toString() {
		return $this->toString($this->getPosition(), $this->getLimit());
	}
	
	protected abstract function toString($start, $end);
	
	/**
	 * Returns the length of this character buffer.
	 * When viewed as a character sequence, the length of a character buffer is simply the number of
	 * characters between the position (inclusive) and the limit (exclusive); that is, equivalent to
	 * remaining().
	 * @return int The length of this character buffer.
	 * @see \KM\Lang\CharSequence::length()
	 */
	public final function length() {
		return $this->remaining();
	}

	/**
	 * Reads the character at the given index relative to the current position.
	 * @param int $index The index of the character to be read, relative to the position; must be
	 *        non-negative and smaller than remaining().
	 * @return string The character at index getPosition() + $index.
	 * @see \KM\Lang\CharSequence::charAt()
	 */
	public final function charAt($index) {
		return $this->getChar( $this->getPosition() + $this->checkIndex( $index, 1 ) );
	}

	/**
	 * Creates a new character buffer that represents the specified subsequence
	 * of this buffer, relative to the current position.
	 *
	 * <p> The new buffer will share this buffer's content; that is, if the
	 * content of this buffer is mutable then modifications to one buffer will
	 * cause the other to be modified. The new buffer's capacity will be that
	 * of this buffer, its position will be
	 * <tt>position()</tt>&nbsp;+&nbsp;<tt>start</tt>, and its limit will be
	 * <tt>position()</tt>&nbsp;+&nbsp;<tt>end</tt>. The new buffer will be
	 * direct if, and only if, this buffer is direct, and it will be read-only
	 * if, and only if, this buffer is read-only.</p>
	 * @param int $start The index, relative to the current position, of the first character in the
	 *        	subsequence; must be non-negative and no larger than remaining().
	 * @param int $end The index, relative to the current position, of the character following the
	 *        	last character in the subsequence; must be non-negative and no larger than
	 *        	remaining().
	 * @return \KM\NIO\CharBuffer The new character buffer.
	 * @see \KM\Lang\CharSequence::subSequence()
	 */
	public abstract function subSequence($start, $end);
	
	/* ---------- Methods to support Appendable ---------- */
	
	/**
	 * Appends the specified character sequence, or subsequence, to this buffer.
	 * @param CharSequence|string $csq The character sequence (or subsequence) to append.
	 * @param int $start
	 * @param int $end
	 * @return \KM\NIO\CharBuffer This buffer.
	 * @see \KM\Lang\Appendable::append()
	 */
	public function append($csq = null, $start = 0, $end = null) {
		/* @var $cs CharSequence */
		$csq = ($csq == null ? 'null' : $csq);
		$start = (int) $start;
		if ($csq instanceof CharSequence) {
			$cs = $csq;
			if ($end == null) {
				$end = $cs->length();
			}
			$end = (int) $end;
			return $this->putString( $cs->subSequence( $start, $end )
				->toString() );
		} elseif (!is_string( $csq )) {
			throw new ClassCastException();
		}
		return $this->putString( $src, $start, $end );
	}

	/**
	 * Appends the specified char to this buffer.
	 * An invocation of this method of the form $dst.append($c) behaves in exactly the same way as
	 * the invocation $dst.putChar($c).
	 * @param string $c The 16-bit char to append.
	 * @return \KM\NIO\CharBuffer This buffer.
	 * @see \KM\Lang\Appendable::appendChar()
	 */
	public function appendChar($c) {
		return $this->putChar( $c );
	}
	
	/* ---------- Other byte stuff: Access to binary data ---------- */
	
	/**
	 * Returns this buffer's byte order.
	 *
	 * <p> The byte order of a char buffer created by allocation or by wrapping an existing
	 * <tt>char</tt> array is the native order of the underlying hardware. The byte order of a char
	 * buffer created as a view of a byte buffer is that of the byte buffer at the moment that the
	 * view is created. </p>
	 * @return \KM\NIO\ByteOrder This buffer's byte order.
	 */
	public abstract function order();
}
?>