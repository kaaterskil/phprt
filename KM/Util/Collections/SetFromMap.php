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

use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\Util\AbstractSet;
use KM\Util\Collection;
use KM\Util\Map;
use KM\Util\Set;

/**
 * SetFromMap Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class SetFromMap extends AbstractSet implements Set {
	
	/**
	 * The backing map.
	 * @var Map
	 */
	private $m;
	
	/**
	 * Its key set.
	 * @var Set
	 */
	private $s;

	public function __construct($typeParameters = '<string>', Map $map) {
		if (!$map->isEmpty()) {
			throw new IllegalArgumentException( 'Map is non-empty' );
		}
		$this->m = $map;
		$this->s = $map->keySet();
	}

	public function clear() {
		$this->m->clear();
	}

	public function size() {
		return $this->m->size();
	}

	public function isEmpty() {
		return $this->m->isEmpty();
	}

	public function contains($o = null) {
		return $this->m->containsKey( $o );
	}

	public function remove($o = null) {
		return $this->m->remove( $o ) != null;
	}

	public function add($e) {
		return $this->m->put( $e, true );
	}

	public function getIterator() {
		return $this->s->getIterator();
	}

	public function toArray($a = null) {
		return $this->s->toArray( $a );
	}

	public function __toString() {
		return $this->s->__toString();
	}

	public function hashCode() {
		return $this->s->hashCode();
	}

	public function equals(Object $obj = null) {
		return $obj === $this || $this->s->equals( $obj );
	}

	public function containsAll(Collection $c) {
		return $this->s->containsAll( $c );
	}

	public function removeAll(Collectionl $c) {
		return $this->s->removeAll( $c );
	}

	public function retainAll(Collectionl $c) {
		return $this->s->retainAll( $c );
	}
}
?>