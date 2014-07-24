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

use KM\Lang\Enum;

/**
 * A typesafe enumeration of byte orders.
 *
 * @package KM\NIO
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ByteOrder extends Enum {
	
	/**
	 * Constant denoting big-endian byte order.
	 * In this order, the bytes of a multibyte value are ordered from most significant to least
	 * significant.
	 * @var ByteOrder
	 */
	const BIG_ENDIAN = 'BIG_ENDIAN';
	
	/**
	 * Constant denoting little-endian byte order, In this order, the bytes of a multibyte value are
	 * ordered from the least significant to most significant.
	 * @var ByteOrder
	 */
	const LITTLE_ENDIAN = 'LITTLE_ENDIAN';

	/**
	 * Returns the native byte order of the underlying platform.
	 * @return ByteOrder The native byte order of the hardware upon which this application is
	 *         running.
	 */
	public static function nativeOrder() {
		switch (pack( 'd', 1 )) {
			case "\0\0\0\0\0\0\360\77" :
				return self::LITTLE_ENDIAN();
			case "\77\360\0\0\0\0\0\0" :
				return self::BIG_ENDIAN();
		}
	}
}
?>