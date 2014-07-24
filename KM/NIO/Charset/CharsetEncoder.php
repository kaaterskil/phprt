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
namespace KM\NIO\Charset;

use KM\Lang\CharSequence;
use KM\Lang\ClassCastException;
use KM\Lang\IllegalStateException;
use KM\Lang\Object;
use KM\NIO\ByteBuffer;
use KM\NIO\CharBuffer;
use KM\NIO\Charset\CoderResult;
use KM\NIO\Charset\CodingErrorAction;

/**
 * CharsetEncoder Class
 *
 * @package KM\NIO\Charset
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class CharsetEncoder extends Object {
	
	/**
	 * The charset name
	 * @var string
	 */
	private $charset;
	
	/**
	 * The action to take upon a malformed input.
	 * @var CodingErrorAction
	 */
	private $malformedInputAction;
	
	/**
	 * The state of this encoder.
	 * @var int
	 */
	private $state;
	private static $ST_RESET = 0;
	private static $ST_CODING = 1;
	private static $ST_END = 2;
	private static $ST_FLUSHED = 3;
	private static $stateNames = array(
		'RESET',
		'CODING',
		'CODING_END',
		'FLUSHED'
	);

	public function __construct($charsetName) {
		$this->malformedInputAction = CodingErrorAction::REPORT();
		$this->state = self::$ST_RESET;
		$this->charset = (string) $charsetName;
	}

	/**
	 * Returns the charset that created this encoder.
	 * @return string
	 */
	public final function charset() {
		return $this->charset;
	}

	/**
	 * Encodes as many characters as possible from the given input buffer,
	 * writing the results to the given output buffer.
	 *
	 * <p> The buffers are read from, and written to, starting at their current
	 * positions. At most {@link Buffer#remaining in.remaining()} characters
	 * will be read and at most {@link Buffer#remaining out.remaining()}
	 * bytes will be written. The buffers' positions will be advanced to
	 * reflect the characters read and the bytes written, but their marks and
	 * limits will not be modified.
	 *
	 * <p> In addition to reading characters from the input buffer and writing
	 * bytes to the output buffer, this method returns a {@link CoderResult}
	 * object to describe its reason for termination:
	 *
	 * <ul>
	 *
	 * <li><p> {@link CoderResult#UNDERFLOW} indicates that as much of the
	 * input buffer as possible has been encoded. If there is no further
	 * input then the invoker can proceed to the next step of the
	 * <a href="#steps">encoding operation</a>. Otherwise this method
	 * should be invoked again with further input. </p></li>
	 *
	 * <li><p> {@link CoderResult#OVERFLOW} indicates that there is
	 * insufficient space in the output buffer to encode any more characters.
	 * This method should be invoked again with an output buffer that has
	 * more {@linkplain Buffer#remaining remaining} bytes. This is
	 * typically done by draining any encoded bytes from the output
	 * buffer. </p></li>
	 *
	 * <li><p> A {@linkplain CoderResult#malformedForLength
	 * malformed-input} result indicates that a malformed-input
	 * error has been detected. The malformed characters begin at the input
	 * buffer's (possibly incremented) position; the number of malformed
	 * characters may be determined by invoking the result object's {@link
	 * CoderResult#length() length} method. This case applies only if the
	 * {@linkplain #onMalformedInput malformed action} of this encoder
	 * is {@link CodingErrorAction#REPORT}; otherwise the malformed input
	 * will be ignored or replaced, as requested. </p></li>
	 *
	 * <li><p> An {@linkplain CoderResult#unmappableForLength
	 * unmappable-character} result indicates that an
	 * unmappable-character error has been detected. The characters that
	 * encode the unmappable character begin at the input buffer's (possibly
	 * incremented) position; the number of such characters may be determined
	 * by invoking the result object's {@link CoderResult#length() length}
	 * method. This case applies only if the {@linkplain #onUnmappableCharacter
	 * unmappable action} of this encoder is {@link
	 * CodingErrorAction#REPORT}; otherwise the unmappable character will be
	 * ignored or replaced, as requested. </p></li>
	 *
	 * </ul>
	 *
	 * In any case, if this method is to be reinvoked in the same encoding
	 * operation then care should be taken to preserve any characters remaining
	 * in the input buffer so that they are available to the next invocation.
	 *
	 * <p> The <tt>endOfInput</tt> parameter advises this method as to whether
	 * the invoker can provide further input beyond that contained in the given
	 * input buffer. If there is a possibility of providing additional input
	 * then the invoker should pass <tt>false</tt> for this parameter; if there
	 * is no possibility of providing further input then the invoker should
	 * pass <tt>true</tt>. It is not erroneous, and in fact it is quite
	 * common, to pass <tt>false</tt> in one invocation and later discover that
	 * no further input was actually available. It is critical, however, that
	 * the final invocation of this method in a sequence of invocations always
	 * pass <tt>true</tt> so that any remaining un-encoded input will be treated
	 * as being malformed.
	 *
	 * <p> This method works by invoking the {@link #encodeLoop encodeLoop}
	 * method, interpreting its results, handling error conditions, and
	 * re-invoking it as necessary. </p>
	 * @param CharBuffer $in The input character buffer.
	 * @param ByteBuffer $out The output byte buffer. If none given, a byte buffer with the same
	 *        	capacity of the number of remaining elements of the input buffer will be
	 *        	allocated.
	 * @param string $endOfInput True if and only if the invoker can provide no additional input
	 *        	characters beyond those in the given buffer.
	 * @return \KM\NIO\Charset\CoderResult A coder result object describing the reason for
	 *         termination.
	 */
	public final function encode(CharBuffer $in, ByteBuffer $out = null, $endOfInput = false) {
		/* @var $cr CoderResult */
		/* @var $action CodingErrorAction */
		$newState = $endOfInput ? self::$ST_END : self::$ST_CODING;
		if (($this->state != self::$ST_RESET) && ($this->state != self::$ST_CODING) &&
			 !($endOfInput && ($this->state == self::$ST_END))) {
			$this->throwIllegalStateException( $this->state, $newState );
		}
		$this->state = $newState;
		
		for(;;) {
			$cr = $this->encodeLoop( $in, $out );
			if ($cr->isOverflow()) {
				return $cr;
			}
			if ($cr->isUnderflow()) {
				if ($endOfInput && $in->hasRemaining()) {
					// Noop
				} else {
					return $cr;
				}
			}
			$action = null;
			if ($cr->isMalformed()) {
				$action = $this->malformedInputAction;
			}
			if ($action == CodingErrorAction::REPORT()) {
				return $cr;
			}
			// TODO Implement replace and ignore actions.
			assert( false );
		}
	}

	/**
	 * Convenience method that encodes the remaining content of a single input
	 * character buffer into a newly-allocated byte buffer.
	 *
	 * <p> This method implements an entire <a href="#steps">encoding
	 * operation</a>; that is, it resets this encoder, then it encodes the
	 * characters in the given character buffer, and finally it flushes this
	 * encoder. This method should therefore not be invoked if an encoding
	 * operation is already in progress. </p>
	 * @param CharBuffer $in The input character buffer.
	 * @return \KM\NIO\HeapByteBuffer A newly allocated byte buffer containing the result of the
	 *         encoding operation. The buffer's position will be zero and its limit will follow the
	 *         last byte written.
	 */
	public function encodeRemaining(CharBuffer $in) {
		$n = $in->remaining();
		$out = ByteBuffer::allocate( $n );
		
		if (($n == 0) && ($in->remaining() == 0)) {
			return $out;
		}
		$this->reset();
		for(;;) {
			$cr = $in->hasRemaining() ? $this->encode( $in, $out, true ) : CoderResult::UNDERFLOW();
			if ($cr->isUnderflow()) {
				$cr = $this->flush( $out );
			}
			if ($cr->isUnderflow()) {
				break;
			}
			if ($cr->isOverflow()) {
				$n = 2 * $n + 1;
				$o = ByteBuffer::allocate( $n );
				$out->flip();
				$o->putBuffer( $out );
				$out = $o;
				continue;
			}
			$cr->throwException();
		}
		$out->flip();
		return $out;
	}

	/**
	 * Flushes this encoder.
	 *
	 * <p> Some encoders maintain internal state and may need to write some
	 * final bytes to the output buffer once the overall input sequence has
	 * been read.
	 *
	 * <p> Any additional output is written to the output buffer beginning at
	 * its current position. At most {@link Buffer#remaining out.remaining()}
	 * bytes will be written. The buffer's position will be advanced
	 * appropriately, but its mark and limit will not be modified.
	 *
	 * <p> If this method completes successfully then it returns {@link
	 * CoderResult#UNDERFLOW}. If there is insufficient room in the output
	 * buffer then it returns {@link CoderResult#OVERFLOW}. If this happens
	 * then this method must be invoked again, with an output buffer that has
	 * more room, in order to complete the current <a href="#steps">encoding
	 * operation</a>.
	 *
	 * <p> If this encoder has already been flushed then invoking this method
	 * has no effect.
	 *
	 * <p> This method invokes the {@link #implFlush implFlush} method to
	 * perform the actual flushing operation. </p>
	 * @param ByteBuffer $out The output byte buffer.
	 * @return \KM\NIO\Charset\CoderResult A CoderResult object, either UNDERFLOW or OVERFLOW.
	 */
	public final function flush(ByteBuffer $out) {
		/* @var $cr CoderResult */
		if ($this->state == self::$ST_END) {
			$cr = $this->implFlush( $out );
			if ($cr->isUnderflow()) {
				$this->state = self::$ST_FLUSHED;
			}
			return $cr;
		}
		if ($this->state != self::$ST_FLUSHED) {
			$this->throwIllegalStateException( $this->state, self::$ST_FLUSHED );
		}
		return CoderResult::UNDERFLOW(); // Already flushed.
	}

	/**
	 * Flushes this encoder.
	 * The default implementation of this method does nothing and always returns UNDERFLOW. This
	 * method should be overridden by encoders that may need to write final bytes to the output
	 * buffer once the entire input sequence has been read.
	 * @param ByteBuffer $out The output byte buffer.
	 * @return \KM\NIO\Charset\CoderResul0 A coder result object, either UNDEFLOW or OVERFLOW.
	 */
	protected function implFlush(ByteBuffer $out) {
		return CoderResult::UNDERFLOW();
	}

	/**
	 * Resets this encoder, clearing any internal state.
	 * This method resets charset-independent state and also invokes the implReset() method in order
	 * to performs any charset-specific reset actions.
	 * @return \KM\NIO\Charset\CharsetEncoder This encoder.
	 */
	public final function reset() {
		$this->implReset();
		$this->state = self::$ST_RESET;
		return $this;
	}

	/**
	 * Resets this encoder, clearing any charset-specific internal state.
	 * The default implementation of this method odes nothing, This method should be overridden by
	 * encoders that maintain internal state,
	 */
	protected function implReset() {
	}

	/**
	 * Encodes one or more characters into one or more bytes.
	 *
	 * <p> This method encapsulates the basic encoding loop, encoding as many
	 * characters as possible until it either runs out of input, runs out of room
	 * in the output buffer, or encounters an encoding error. This method is
	 * invoked by the {@link #encode encode} method, which handles result
	 * interpretation and error recovery.
	 *
	 * <p> The buffers are read from, and written to, starting at their current
	 * positions. At most {@link Buffer#remaining in.remaining()} characters
	 * will be read, and at most {@link Buffer#remaining out.remaining()}
	 * bytes will be written. The buffers' positions will be advanced to
	 * reflect the characters read and the bytes written, but their marks and
	 * limits will not be modified.
	 *
	 * <p> This method returns a {@link CoderResult} object to describe its
	 * reason for termination, in the same manner as the {@link #encode encode}
	 * method. Most implementations of this method will handle encoding errors
	 * by returning an appropriate result object for interpretation by the
	 * {@link #encode encode} method. An optimized implementation may instead
	 * examine the relevant error action and implement that action itself.
	 *
	 * <p> An implementation of this method may perform arbitrary lookahead by
	 * returning {@link CoderResult#UNDERFLOW} until it receives sufficient
	 * input. </p>
	 * @param CharBuffer $src The input character buffer.
	 * @param ByteBuffer $dst The output byte buffer.
	 * @return \KM\NIO\Charset\CoderResult A coder result object describing the reason for
	 *         termination.
	 */
	protected function encodeLoop(CharBuffer $src, ByteBuffer $dst) {
		if ($src->hasArray() && $dst->hasArray()) {
			return $this->encodeArrayLoop( $src, $dst );
		}
		return $this->encodeBufferLoop( $src, $dst );
	}

	private function encodeArrayLoop(CharBuffer $src, ByteBuffer $dst) {
		$sa = & $src->toArray();
		$sp = $src->arrayOffset() + $src->getPosition();
		$sl = $src->arrayOffset() + $src->getLimit();
		assert( $sp <= $sl );
		$sp = ($sp <= $sl ? $sp : $sl);
		
		$da = & $dst->toArray();
		$dp = $dst->arrayOffset() + $dst->getPosition();
		$dl = $dst->arrayOffset() + $dst->getLimit();
		assert( $dp <= $dl );
		$dp = ($dp <= $dl ? $dp : $dl);
		
		// Test encoding
		$success = mb_check_encoding( implode( '', $sa ), $this->charset );
		if (!$success) {
			return CoderResult::MALFORMED();
		}
		
		// Transfer chars into byte array after encoding
		$currentEncoding = iconv_get_encoding( 'output_encoding' );
		while ( $sp < $sl ) {
			$c = $sa[$sp];
			if ($dp >= $dl) {
				$src->setPosition( $sp - $src->arrayOffset() );
				$dst->setPosition( $dp - $dst->arrayOffset() );
				return CoderResult::OVERFLOW();
			}
			if ($currentEncoding != $this->charset) {
				$c = iconv( $currentEncoding, $this->charset, $c );
			}
			$da[$dp++] = $c;
			$sp++;
		}
		$src->setPosition( $sp - $src->arrayOffset() );
		$dst->setPosition( $dp - $dst->arrayOffset() );
		return CoderResult::UNDERFLOW();
	}

	private function encodeBufferLoop(CharBuffer $src, ByteBuffer $dst) {
		$mark = $src->getPosition();
		$currentEncoding = iconv_get_encoding( 'output_encoding' );
		while ( $src->hasRemaining() ) {
			$c = $src->getChar();
			$success = mb_check_encoding( $c, $this->charset );
			if (!$success) {
				$src->setPosition( $mark );
				return CoderResult::MALFORMED();
			}
			if (!$dst->hasRemaining()) {
				$src->setPosition( $mark );
				return CoderResult::OVERFLOW();
			}
			if ($currentEncoding != $this->charset) {
				$c = iconv( $currentEncoding, $this->charset, $c );
			}
			$dst->putByte(  $c );
			$mark++;
		}
		$src->setPosition( $mark );
		return CoderResult::UNDERFLOW();
	}

	private function throwIllegalStateException($from, $to) {
		throw new IllegalStateException(
			'Current state: ' . self::$stateNames[$from] . ', new state: ' . self::$stateNames[$to] );
	}
}
?>