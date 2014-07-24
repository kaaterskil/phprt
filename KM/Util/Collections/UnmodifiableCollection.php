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
use KM\Util\Collections\UnmodifiableCollectionIterator;

/**
 * UnmodifiableCollection Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class UnmodifiableCollection extends Object implements Collection {
	/**
	 * The backing collection.
	 * @var Collection
	 */
	protected $c;

	public function __construct(Collection $c) {
		$this->c = $c;
	}

	public function getTypeParameters() {
		return $this->c->getTypeParameters();
	}

	public function setTypeParameters($typeParameters) {
		return $this->c->setTypeParameters($typeParameters);
	}

	public function size() {
		return $this->c->size();
	}

	public function isEmpty() {
		return $this->c->isEmpty();
	}

	public function contains($o = null) {
		return $this->c->contains( $o );
	}

	public function toArray(array $a = null) {
		return $this->c->toArray( $a );
	}

	public function __toString() {
		return (string) $this->c;
	}

	public function getIterator() {
		return new UnmodifiableCollectionIterator( $this->c->getIterator() );
	}

	public function add($e) {
		throw new UnsupportedOperationException();
	}

	public function remove($o = null) {
		throw new UnsupportedOperationException();
	}

	public function containsAll(Collection $coll) {
		return $this->c->containsAll( $coll );
	}

	public function addAll(Collection $coll) {
		throw new UnsupportedOperationException();
	}

	public function removeAll(Collection $coll) {
		throw new UnsupportedOperationException();
	}

	public function retainAll(Collection $coll) {
		throw new UnsupportedOperationException();
	}

	public function clear() {
		throw new UnsupportedOperationException();
	}
}
?>