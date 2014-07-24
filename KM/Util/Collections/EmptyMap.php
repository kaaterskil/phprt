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
namespace KM\Util\Collections;

use KM\Util\AbstractMap;
use KM\Util\Collections;
use KM\Util\Map;
use KM\Lang\Object;

/**
 * EmptyMap Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class EmptyMap extends AbstractMap {

	public function __construct($typeParameters = null) {
		parent::__construct( $typeParameters );
	}

	public function size() {
		return 0;
	}

	public function isEmpty() {
		return true;
	}

	public function containsKey($key = null) {
		return false;
	}

	public function containsValue($value = null) {
		return false;
	}

	public function get($key = null) {
		return null;
	}

	public function keySet() {
		return Collections::emptySet();
	}

	public function values() {
		return Collections::emptySet();
	}

	public function entrySet() {
		return Collections::emptySet();
	}

	public function equals(Object $obj = null) {
		/* @var $m Map */
		if ($obj instanceof Map) {
			$m = $obj;
			return $m->isEmpty() == true;
		}
		return false;
	}

	public function hashCode() {
		return 0;
	}
}
?>