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

use KM\Lang\Object;
use KM\Lang\IndexOutOfBoundsException;

/**
 * A container for data of a specific primitive type.
 *
 * <p> A buffer is a linear, finite sequence of elements of a specific
 * primitive type. Aside from its content, the essential properties of a
 * buffer are its capacity, limit, and position: </p>
 *
 * <blockquote>
 *
 * <p> A buffer's <i>capacity</i> is the number of elements it contains. The
 * capacity of a buffer is never negative and never changes. </p>
 *
 * <p> A buffer's <i>limit</i> is the index of the first element that should
 * not be read or written. A buffer's limit is never negative and is never
 * greater than its capacity. </p>
 *
 * <p> A buffer's <i>position</i> is the index of the next element to be
 * read or written. A buffer's position is never negative and is never
 * greater than its limit. </p>
 *
 * </blockquote>
 *
 * <p> There is one subclass of this class for each non-boolean primitive type.
 *
 *
 * <h2> Transferring data </h2>
 *
 * <p> Each subclass of this class defines two categories of <i>get</i> and
 * <i>put</i> operations: </p>
 *
 * <blockquote>
 *
 * <p> <i>Relative</i> operations read or write one or more elements starting
 * at the current position and then increment the position by the number of
 * elements transferred. If the requested transfer exceeds the limit then a
 * relative <i>get</i> operation throws a {@link BufferUnderflowException}
 * and a relative <i>put</i> operation throws a {@link
 * BufferOverflowException}; in either case, no data is transferred. </p>
 *
 * <p> <i>Absolute</i> operations take an explicit element index and do not
 * affect the position. Absolute <i>get</i> and <i>put</i> operations throw
 * an {@link IndexOutOfBoundsException} if the index argument exceeds the
 * limit. </p>
 *
 * </blockquote>
 *
 * <p> Data may also, of course, be transferred in to or out of a buffer by the
 * I/O operations of an appropriate channel, which are always relative to the
 * current position.
 *
 *
 * <h2> Marking and resetting </h2>
 *
 * <p> A buffer's <i>mark</i> is the index to which its position will be reset
 * when the {@link #reset reset} method is invoked. The mark is not always
 * defined, but when it is defined it is never negative and is never greater
 * than the position. If the mark is defined then it is discarded when the
 * position or the limit is adjusted to a value smaller than the mark. If the
 * mark is not defined then invoking the {@link #reset reset} method causes an
 * {@link InvalidMarkException} to be thrown.
 *
 *
 * <h2> Invariants </h2>
 *
 * <p> The following invariant holds for the mark, position, limit, and
 * capacity values:
 *
 * <blockquote>
 * <tt>0</tt> <tt>&lt;=</tt>
 * <i>mark</i> <tt>&lt;=</tt>
 * <i>position</i> <tt>&lt;=</tt>
 * <i>limit</i> <tt>&lt;=</tt>
 * <i>capacity</i>
 * </blockquote>
 *
 * <p> A newly-created buffer always has a position of zero and a mark that is
 * undefined. The initial limit may be zero, or it may be some other value
 * that depends upon the type of the buffer and the manner in which it is
 * constructed. Each element of a newly-allocated buffer is initialized
 * to zero.
 *
 *
 * <h2> Clearing, flipping, and rewinding </h2>
 *
 * <p> In addition to methods for accessing the position, limit, and capacity
 * values and for marking and resetting, this class also defines the following
 * operations upon buffers:
 *
 * <ul>
 *
 * <li><p> {@link #clear} makes a buffer ready for a new sequence of
 * channel-read or relative <i>put</i> operations: It sets the limit to the
 * capacity and the position to zero. </p></li>
 *
 * <li><p> {@link #flip} makes a buffer ready for a new sequence of
 * channel-write or relative <i>get</i> operations: It sets the limit to the
 * current position and then sets the position to zero. </p></li>
 *
 * <li><p> {@link #rewind} makes a buffer ready for re-reading the data that
 * it already contains: It leaves the limit unchanged and sets the position
 * to zero. </p></li>
 *
 * </ul>
 *
 *
 * <h2> Read-only buffers </h2>
 *
 * <p> Every buffer is readable, but not every buffer is writable. The
 * mutation methods of each buffer class are specified as <i>optional
 * operations</i> that will throw a {@link ReadOnlyBufferException} when
 * invoked upon a read-only buffer. A read-only buffer does not allow its
 * content to be changed, but its mark, position, and limit values are mutable.
 * Whether or not a buffer is read-only may be determined by invoking its
 * {@link #isReadOnly isReadOnly} method.
 *
 *
 * <h2> Thread safety </h2>
 *
 * <p> Buffers are not safe for use by multiple concurrent threads. If a
 * buffer is to be used by more than one thread then access to the buffer
 * should be controlled by appropriate synchronization.
 *
 *
 * <h2> Invocation chaining </h2>
 *
 * <p> Methods in this class that do not otherwise have a value to return are
 * specified to return the buffer upon which they are invoked. This allows
 * method invocations to be chained; for example, the sequence of statements
 *
 * <blockquote><pre>
 * b.flip();
 * b.position(23);
 * b.limit(42);</pre></blockquote>
 *
 * can be replaced by the single, more compact statement
 *
 * <blockquote><pre>
 * b.flip().position(23).limit(42);</pre></blockquote>
 *
 * @package KM\NIO
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class Buffer extends Object {
	
	/**
	 * The last marked position.
	 * @var int
	 */
	private $mark = -1;
	
	/**
	 * The cursor position
	 * @var int
	 */
	private $position = 0;
	
	/**
	 * The maximum number of valid elements.
	 * @var int
	 */
	private $limit;
	
	/**
	 * The buffer capacity.
	 * @var int
	 */
	private $capacity;

	/**
	 * Creates a new buffer with the given mark, position, limit and capacity after checking
	 * invariants.
	 * @param int $mark
	 * @param int $pos
	 * @param int $lim
	 * @param int $cap
	 * @throws \InvalidArgumentException
	 */
	public function __construct($mark, $pos, $lim, $cap) {
		$mark = (int) $mark;
		$pos = (int) $pos;
		$lim = (int) $lim;
		$cap = (int) $cap;
		
		if ($cap < 0) {
			throw new \InvalidArgumentException( 'Negative capacity: ' . $cap );
		}
		$this->capacity = $cap;
		$this->setLimit($lim);
		$this->setPosition($pos);
		if($mark >= 0) {
			if($mark > $pos) {
				throw new \InvalidArgumentException('mark > position (' . $mark . ' > ' > $pos . ')');
			}
			$this->mark = $mark;
		}
	}

	/**
	 * Returns this buffer's capacity.
	 * @return int
	 */
	public final function getCapacity() {
		return $this->capacity;
	}

	/**
	 * Returns this buffer's position.
	 * @return int
	 */
	public final function getPosition() {
		return $this->position;
	}

	/**
	 * Sets this buffer's position.
	 * If the mark is defined and larger than the new position then it is discarded.
	 * @param int $newPosition The new position value: must be non-negative and no larger than the
	 *        current limit.
	 * @throws \InvalidArgumentException if the preconditions on $newPosition do not hold.
	 * @return \KM\NIO\Buffer This buffer.
	 */
	public final function setPosition($newPosition) {
		$newPosition = (int) $newPosition;
		if ($newPosition > $this->limit || $newPosition < 0) {
			throw new \InvalidArgumentException();
		}
		$this->position = $newPosition;
		if ($this->mark > $this->position) {
			$this->mark = -1;
		}
		return $this;
	}

	/**
	 * Returns this buffer's limit.
	 * @return int
	 */
	public final function getLimit() {
		return $this->limit;
	}

	/**
	 * Sets this buffer's limit.
	 * If the position is larger than the new limit then it is set to the new limit, If the mark is
	 * defined and larger than the new limit then it is discarded.
	 * @param int $newLimit The new limit value: must be non-negative and no larger than this
	 *        buffer's capacity.
	 * @throws \InvalidArgumentException if the preconditions on $newLimit do not hold.
	 * @return \KM\NIO\Buffer This buffer.
	 */
	public final function setLimit($newLimit) {
		$newLimit = (int) $newLimit;
		if ($newLimit > $this->capacity || $newLimit < 0) {
			throw new \InvalidArgumentException();
		}
		$this->limit = $newLimit;
		if ($this->position > $this->limit) {
			$this->position = $this->limit;
		}
		if ($this->mark > $this->limit) {
			$this->mark = -1;
		}
		return $this;
	}

	/**
	 * Sets this buffer's mark at its position.
	 * @return \KM\NIO\Buffer This buffer.
	 */
	public final function mark() {
		$this->mark = $this->position;
		return $this;
	}

	/**
	 * Resets this buffer's position to the previously marked position.
	 * Invoking this method neither changes nor discards the mark's value.
	 * @throws InvalidMarkException if the mark has not been set.
	 * @return \KM\NIO\Buffer This buffer.
	 */
	public final function reset() {
		$m = $this->mark;
		if ($m < 0) {
			throw new InvalidMarkException();
		}
		$this->position = $m;
		return $this;
	}

	/**
	 * Clears this buffer.
	 * The position is set to zero, the limit is set to the capacity and the mark is discarded.
	 * Invoke this method before using a sequence of operations to fill this buffer. This method
	 * does not actually erase the data in the buffer but it is named as if it did because it will
	 * most often be used in situations in which that might as well be the case.
	 * @return \KM\NIO\Buffer This buffer.
	 */
	public final function clear() {
		$this->position = 0;
		$this->limit = $this->capacity;
		$this->mark = -1;
		return $this;
	}

	/**
	 * Flips this buffer.
	 * The limit is set to the current position and the position is set to zero. If the mark is
	 * defined than it is discarded. After a sequence of put() operations, invoke this method to
	 * prepare for a sequence of get() operations. This method is often used in conjunction with the
	 * compact() method when transferring data from one place to another.
	 * @return \KM\NIO\Buffer This buffer.
	 */
	public final function flip() {
		$this->limit = $this->position;
		$this->position = 0;
		$this->mark = -1;
		return $this;
	}

	/**
	 * Rewinds this buffer.
	 * The position is set to zero and the mark is discarded. Invoke this method before a sequence
	 * of get9) operations assuming that the limit has already been set appropriately.
	 * @return \KM\NIO\Buffer This buffer.
	 */
	public final function rewind() {
		$this->position = 0;
		$this->mark = -1;
		return $this;
	}

	/**
	 * Returns the number of elements between the current position and the limit.
	 * @return int The number of elements remaining in this buffer.
	 */
	public final function remaining() {
		return $this->limit - $this->position;
	}

	/**
	 * Tells whether there are any elements between the current position and the limit.
	 * @return boolean True if and only if there is at least one element remaining in this buffer.
	 */
	public final function hasRemaining() {
		return $this->position < $this->limit;
	}

	/**
	 * Tells whether or not this buffer is read=only.
	 * @return boolean True if and only if this buffer is read=only.
	 */
	public abstract function isReadOnly();

	/**
	 * Tells whether or not this buffer is backed by an accessible array.
	 * If this method returns true, than the toArray() and arrayOffset() methods may safely be
	 * invoked.
	 * @return boolean If and only if this buffer is backed by an array and is not read-only.
	 */
	public abstract function hasArray();

	/**
	 * Returns the array that backs this buffer.
	 * This method is intended to allow array-backed buffers to be passed to native code more
	 * efficiently.
	 * @return array
	 */
	public abstract function toArray();

	/**
	 * Returns the offset within this buffer's backing array of the first element of the buffer.
	 * If this buffer is backed by an array then the buffer position $p corresponds to array index
	 * $p + arrayOffset().
	 * @return int The offset within this buffer's array of the first element of the buffer.
	 */
	public abstract function arrayOffset();

	/**
	 * Tells whether or not this buffer is direct.
	 * @return boolean True if and only if this buffer is direct.
	 */
	public abstract function isDirect();

	/**
	 * Checks the current position against the limit, throwing a BufferUnderflowException if it is
	 * not smaller than the limit, and then increments the position.
	 * @throws BufferUnderflowException
	 * @return int The current position value before it is incremented.
	 */
	public final function nextGetIndex($nb = null) {
		if ($nb == null) {
			return $this->nextGetIndex0();
		} else {
			return $this->nextGetIndex1( $nb );
		}
	}

	private final function nextGetIndex0() {
		if ($this->position >= $this->limit) {
			throw new BufferUnderflowException();
		}
		return $this->position++;
	}

	private final function nextGetIndex1($nb) {
		$nb = (int) $nb;
		if ($this->limit - $this->position < $nb) {
			throw new BufferUnderflowException();
		}
		$p = $this->position;
		$this->position += $nb;
		return $p;
	}

	/**
	 * Checks the current position against the limit, throwing a BufferOverflowException if it is
	 * not smaller than the limit, and then increments the position.
	 * @param int $nb
	 * @return int The current position value before it is incremented.
	 */
	public final function nextPutIndex($nb = null) {
		if ($nb == null) {
			return $this->nextPutIndex0();
		}
		return $this->nextPutIndex1( $nb );
	}

	private final function nextPutIndex0() {
		if ($this->position >= $this->limit) {
			throw new BufferOverflowException();
		}
		return $this->position;
	}

	private final function nextPutIndex1($nb) {
		$nb = (int) $nb;
		if ($this->limit - $this->position < $nb) {
			throw new BufferOverflowException();
		}
		$p = $this->position;
		$this->position += $nb;
		return $p;
	}

	/**
	 * Checks the given index against the limit, throwing an IndexOutOfBoundsException if it is not
	 * smaller than the limit or is smaller than zero.
	 * @param int $i
	 * @param int $nb
	 * @return int
	 */
	public final function checkIndex($i, $nb = null) {
		if ($nb == null) {
			return $this->checkIndex0( $i );
		}
		return $this->checkIndex1( $i, $nb );
	}

	private final function checkIndex0($i) {
		$i = (int) $i;
		if (($i < 0) || ($i >= $this->limit)) {
			throw new IndexOutOfBoundsException();
		}
		return $i;
	}

	private final function checkIndex1($i, $nb) {
		$i = (int) $i;
		$nb = (int) $nb;
		if (($i < 0) || ($nb > $this->limit - $i)) {
			throw new IndexOutOfBoundsException();
		}
		return $i;
	}

	public final function markValue() {
		return $this->mark;
	}

	public final function truncate() {
		$this->mark = -1;
		$this->position = 0;
		$this->limit = 0;
		$this->capacity = 0;
	}

	public final function discardMark() {
		$this->mark = -1;
	}

	public static function checkBounds($off, $len, $size) {
		if (($off | $len | ($off + $len) | ($size - ($off + $len))) < 0) {
			throw new IndexOutOfBoundsException();
		}
	}
}
?>