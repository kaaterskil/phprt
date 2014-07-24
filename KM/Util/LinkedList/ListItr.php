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
namespace KM\Util\LinkedList;

use KM\Lang\IllegalStateException;
use KM\Lang\Object;
use KM\Util\ListIterator;
use KM\Util\LinkedList\Node;
use KM\Util\LinkedList;
use KM\Util\NoSuchElementException;

/**
 * ListItr Class
 *
 * @package KM\Util\LinkedList
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ListItr extends Object implements ListIterator {
	
	/**
	 * The last node returned.
	 * @var Node
	 */
	private $lastReturned = null;
	
	/**
	 * The next node.
	 * @var Node
	 */
	private $next = null;
	
	/**
	 * The next index.
	 * @var int
	 */
	private $nextIndex = 0;
	
	/**
	 * The backing list.
	 * @var LinkedList
	 */
	private $list;
	
	/**
	 * Returns true if the iterator is descending.
	 * @var boolean
	 */
	private $isReverse = false;

	public function __construct($index = 0, LinkedList &$list) {
		$index = (int) $index;
		$this->list = $list;
		$this->next = ($index == $list->size()) ? null : $this->list->node( $index );
		$this->nextIndex = $index;
	}

	public function setReverse($reverse = false) {
		$this->isReverse = (boolean) $reverse;
	}

	public function hasNext() {
		return $this->nextIndex < $this->list->size();
	}

	public function next() {
		if (!$this->hasNext()) {
			throw new NoSuchElementException();
		}
		$this->lastReturned = $this->next;
		$this->next = $this->next->next;
		$this->nextIndex++;
		return $this->lastReturned->item;
	}

	public function hasPrevious() {
		return $this->nextIndex > 0;
	}

	public function previous() {
		if (!$this->hasPrevious()) {
			throw new NoSuchElementException();
		}
		$this->lastReturned = $this->next = ($this->next == null) ? $this->list->last() : $this->next->prev;
		$this->nextIndex--;
		return $this->lastReturned->item;
	}

	public function nextIndex() {
		return $this->nextIndex;
	}

	public function previousIndex() {
		return $this->nextIndex - 1;
	}

	public function remove() {
		if ($this->lastReturned == null) {
			throw new IllegalStateException();
		}
		$lastNext = $this->lastReturned->next;
		$this->list->unlink( $this->lastReturned );
		if ($this->next == $this->lastReturned) {
			$this->next = $lastNext;
		} else {
			$this->nextIndex++;
		}
		$this->lastReturned = null;
	}

	public function set($e) {
		if ($this->lastReturned == null) {
			throw new IllegalStateException();
		}
		$this->lastReturned->item = $e;
	}

	public function add($e) {
		$this->lastReturned = null;
		if ($this->next == null) {
			$this->list->linkLast( $e );
		} else {
			$this->list->linkBefore( $e, $this->next );
		}
		$this->nextIndex++;
	}
	
	/* ---------- \Iterator methods ---------- */
	public function rewind() {
		$this->lastReturned = null;
		if ($this->isReverse) {
			$this->next = $this->list->getLast();
			$this->nextIndex = $this->list->size() - 1;
		} else {
			$this->next = $this->list->node( 0 );
			$this->nextIndex = 0;
		}
	}

	public function current() {
		/* @var $e Node */
		$e = $this->list->node($this->nextIndex);
		return $e->item;
	}

	public function key() {
		return $this->nextIndex;
	}

	public function valid() {
		return $this->nextIndex >= 0 && $this->hasNext();
	}
}
?>