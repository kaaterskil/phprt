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
namespace KM\NIO\Charset;

use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\Lang\Enum;
use KM\NIO\BufferUnderflowException;
use KM\NIO\BufferOverflowException;

/**
 * CoderResult Class
 *
 * @package KM\NIO\Charset
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class CoderResult extends Enum {
	const UNDERFLOW = 'UNDERFLOW';
	const OVERFLOW = 'OVERFLOW';
	const MALFORMED = 'MALFORMED';

	public function __toString() {
		$nm = $this->getName();
		return $this->isError() ? $nm . '[' . $this->getOrdinal() . ']' : $nm;
	}

	public function isUnderflow() {
		return ($this == self::UNDERFLOW());
	}

	public function isOverflow() {
		return ($this == self::OVERFLOW());
	}

	public function isError() {
		return ($this == self::MALFORMED());
	}

	public function isMalformed() {
		return ($this == self::MALFORMED());
	}

	public function isUnmappable() {
		return ($this == self::MALFORMED());
	}

	/**
	 * Throws an exception appropriate to the result described by this object.
	 * @throws BufferUnderflowException if this object is UNDERFLOW.
	 * @throws BufferOverflowException if this object is OVERFLOW.
	 * @throws MalformedInputException if this object is MALFORMED.
	 */
	public function throwException() {
		switch ($this->getOrdinal()) {
			case 0 :
				throw new BufferUnderflowException();
				break;
			case 1 :
				throw new BufferOverflowException();
				break;
			case 2 :
				throw new MalformedInputException();
				break;
			default :
				assert( false );
		}
	}
}
?>