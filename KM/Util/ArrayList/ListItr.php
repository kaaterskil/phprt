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
namespace KM\Util\ArrayList;

use KM\Lang\ClassCastException;
use KM\Lang\Object;
use KM\Util\ArrayList\Itr;
use KM\Util\ListIterator;
use KM\Util\ArrayList;
use KM\Util\NoSuchElementException;
use KM\Lang\IllegalStateException;

/**
 * An optimized version of AbstractList.ListItr
 *
 * @package KM\Util\ArrayList
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ListItr extends Itr implements ListIterator {

	public function __construct($index, ArrayList $list) {
		parent::__construct( $list );
		$this->cursor = (int) $index;
	}

	public function hasPrevious() {
		return $this->cursor != 0;
	}

	public function nextIndex() {
		return $this->cursor;
	}

	public function previousIndex() {
		return $this->cursor - 1;
	}

	public function previous() {
		$i = $this->cursor - 1;
		if ($i < 0) {
			throw new NoSuchElementException();
		}
		$elementData = $this->list->toArray();
		$this->cursor = $i;
		return $elementData[$this->lastRet = $i];
	}

	public function set($e) {
		if ($this->lastRet < 0) {
			throw new IllegalStateException();
		}
		$this->list->set( $this->lastRet, $e );
	}

	public function add($e) {
		$i = $this->cursor;
		$this->list->addAtIndex( $i, $e );
		$this->cursor = $i + 1;
		$this->lastRet = -1;
	}
}
?>