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

use KM\Util\ListInterface;
use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\Util\Collection;
use KM\Util\Comparator;

/**
 * UnmodifiableList Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class UnmodifiableList extends UnmodifiableCollection implements ListInterface {
	
	/**
	 * The backing list.
	 * @var ListInterface
	 */
	protected $list;

	public function __construct(ListInterface $list) {
		parent::__construct( $list );
		$this->list = $list;
	}

	public function equals(Object $obj = null) {
		return ($obj === $this || $this->list->equals( $obj ));
	}

	public function hashCode() {
		return $this->list->hashCode();
	}

	public function get($index) {
		return $this->list->get( $index );
	}

	public function set($index, $element) {
		throw new UnsupportedOperationException();
	}

	public function addAt($index, $element) {
		throw new UnsupportedOperationException();
	}

	public function removeAt($index) {
		throw new UnsupportedOperationException();
	}

	public function indexOf($o = null) {
		return $this->list->indexOf( $o );
	}

	public function lastIndexOf($o = null) {
		return $this->list->lastIndexOf( $o );
	}

	public function addAll(Collection $c) {
		throw new UnsupportedOperationException();
	}

	public function addAllAt($index, Collection $c) {
		throw new UnsupportedOperationException();
	}

	public function replaceAll() {
		throw new UnsupportedOperationException();
	}

	public function sort(Comparator $c) {
		throw new UnsupportedOperationException();
	}

	public function listIterator($index = 0) {
		return new UnmodifiableListIterator( $this->list->listIterator( $index ) );
	}
}
?>