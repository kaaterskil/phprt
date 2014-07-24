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

use KM\Lang\System;

/**
 * HeapByteBuffer Class
 *
 * @package KM\NIO
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class HeapByteBuffer extends ByteBuffer {

	public function __construct($mark, $pos, $lim, $cap, array &$buf = null, $off = null) {
		if ($buf == null) {
			$cap = (int) $cap;
			$buf = array_fill( 0, $cap, 0 );
			parent::__construct( -1, 0, $lim, $cap, $buf, 0 );
		} else {
			parent::__construct( $mark, $pos, $lim, $cap, $buf, $off );
		}
		$this->isReadOnly = true;
	}

	public function slice() {
		return new self( -1, 0, $this->remaining(), $this->remaining(), $this->hb,
			$this->getPosition() + $this->offset );
	}

	public function duplicate() {
		return new self( $this->markValue(), $this->getPosition(), $this->getLimit(), $this->getCapacity(),
			$this->hb, $this->offset );
	}

	public function asReadOnlyBuffer() {
		return new self( $this->markValue(), $this->getPosition(), $this->getLimit(), $this->getCapacity(),
			$this->hb, $this->offset );
	}

	protected function ix($i) {
		return $this->offset + intval( $i );
	}

	public function getByte($i = null) {
		if ($i == null) {
			return $this->getByte0();
		}
		return $this->getByte1( $i );
	}

	private function getByte0() {
		$i = $this->ix( $this->nextGetIndex() );
		return $this->hb[$i];
	}

	private function getByte1($i) {
		$i = $this->ix( $this->checkIndex( $i ) );
		return $this->hb[$i];
	}

	public function get(array &$dst, $offset = 0, $length = null) {
		$offset = (int) $offset;
		$length = (int) $length;
		self::checkBounds( $off, $len, $size );
		if ($length > $this->remaining()) {
			throw new BufferUnderflowException();
		}
		System::arraycopy( $this->hb, $this->ix( $this->getPosition() ), $dst, $offset, $length );
		$this->setPosition( $this->getPosition() + $length );
		return $this;
	}

	public function isDirect() {
		return false;
	}

	public function isReadOnly() {
		return false;
	}

	public function putByte($x, $i = null) {
		if ($i == null) {
			return $this->putByte0( $x );
		}
		return $this->putByte1( $x, $i );
	}

	private function putByte0($x) {
		$i = $this->ix( $this->nextPutIndex() );
		$this->hb[$i] = $x;
		return $this;
	}

	private function putByte1($x, $i) {
		$i = $this->ix( $this->checkIndex( $i ) );
		$this->hb[$i] = $x;
		return $this;
	}

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
		System::arraycopy( $src, $offset, $this->hb, $this->ix( $this->getPosition() ), $length );
		$this->setPosition( $this->getPosition() + $length );
		return $this;
	}

	public function putBuffer(ByteBuffer $src) {
		/* @var $sb HeapByteBuffer */
		if ($src instanceof HeapByteBuffer) {
			if ($src === $this) {
				throw new \InvalidArgumentException();
			}
			$sb = & $src;
			$n = $sb->remaining();
			if ($n > $this->remaining()) {
				throw new BufferOverflowException();
			}
			System::arraycopy( $sb->hb, $sb->ix( $sb->getPosition() ), $this->hb,
				$this->ix( $this->getPosition() ), $n );
			$sb->setPosition( $sb->getPosition() + $n );
			$this->setPosition( $this->getPosition() + n );
		} elseif ($src->isDirect()) {
			$n = $src->remaining();
			if ($n > $this->remaining()) {
				throw new BufferOverflowException();
			}
			$src->get( $this->hb, $this->ix( $this->getPosition() ), $n );
			$this->setPosition( $this->getPosition() + $n );
		} else {
			parent::putBuffer( $src );
		}
		return $this;
	}

	public function compact() {
		System::arraycopy( $this->hb, $this->ix( $this->getPosition() ), $this->hb, $this->ix( 0 ),
			$this->remaining() );
		$this->setPosition( $this->remaining() );
		$this->setLimit( $this->getCapacity() );
		$this->discardMark();
		return $this;
	}
}
?>