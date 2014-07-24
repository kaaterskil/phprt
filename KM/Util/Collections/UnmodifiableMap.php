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

use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\Util\Collection;
use KM\Util\Collections;
use KM\Util\Collections\UnmodifiableEntrySet;
use KM\Util\Map;
use KM\Util\Set;

/**
 * UnmodifiableMap Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class UnmodifiableMap extends Object implements Map {
	
	/**
	 * The backing map.
	 * @var Map
	 */
	private $m;
	
	/**
	 * The backing key set.
	 * @var Set
	 */
	private $keySet = null;
	
	/**
	 * The backing entry set.
	 * @var Set
	 */
	private $entrySet = null;
	
	/**
	 * The backing values collection.
	 * @var Collection
	 */
	private $values = null;

	public function __construct(Map $m) {
		$this->m = $m;
	}
	
	public function getKeyParameterType() {
		return $this->m->getKeyParameterType();
	}
	
	public function getValueParameterType() {
		return $this->m->getValueParameterType();
	}

	public function size() {
		return $this->m->size();
	}

	public function isEmpty() {
		return $this->m->isEmpty();
	}

	public function containsKey($key = null) {
		return $this->m->containsKey( $key );
	}

	public function containsValue($value = null) {
		return $this->m->containsValue( $value );
	}

	public function get($key = null) {
		return $this->m->get( $key );
	}

	public function put($key, $value) {
		throw new UnsupportedOperationException();
	}

	public function remove($key = null) {
		throw new UnsupportedOperationException();
	}

	public function putAll(Map $m) {
		throw new UnsupportedOperationException();
	}

	public function clear() {
		throw new UnsupportedOperationException();
	}

	public function keySet() {
		if ($this->keySet === null) {
			$this->keySet = Collections::unmodifiableSet( $this->m->keySet() );
		}
		return $this->keySet;
	}

	public function entrySet() {
		if ($this->entrySet === null) {
			$this->entrySet = new UnmodifiableEntrySet( $this->m->entrySet() );
		}
		return $this->entrySet;
	}

	public function values() {
		if ($this->values === null) {
			$this->values = Collections::unmodifiableCollection( $this->m->values() );
		}
		return $this->values;
	}

	public function equals(Object $obj = null) {
		return $obj === $this || $this->m->equals( $obj );
	}

	public function hashCode() {
		return $this->m->hashCode();
	}

	public function __toString() {
		return (string) $this->m;
	}
}
?>