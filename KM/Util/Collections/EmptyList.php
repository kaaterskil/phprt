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

use KM\Util\AbstractList;
use KM\Util\ListInterface;
use KM\Util\Collections;
use KM\Util\Collection;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\Object;

/**
 * EmptyList Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class EmptyList extends AbstractList implements ListInterface {

	public function __construct($typeParameters = null) {
		parent::__construct( $typeParameters );
	}

	public function getIterator() {
		return Collections::emptyIterator();
	}
	
	public function listIterator($index = 0) {
		return Collections::emptyListIterator();
	}

	public function size() {
		return 0;
	}

	public function isEmpty() {
		return true;
	}

	public function contains($o = null) {
		return false;
	}

	public function containsAll(Collection $c) {
		return $c->isEmpty();
	}

	public function toArray(array $a = null) {
		if ($a == null) {
			return array();
		}
		$a[0] = null;
		return $a;
	}

	public function get($index) {
		throw new IndexOutOfBoundsException( 'Index: ' . $index );
	}

	public function equals(Object $obj = null) {
		return ($obj instanceof ListInterface) && ($obj->isEmpty());
	}

	public function hashCode() {
		return 1;
	}
}
?>