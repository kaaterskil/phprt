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
namespace KM\Util\ListHelpers;

use KM\Lang\IllegalStateException;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\Object;
use KM\Util\AbstractList;
use KM\Util\Iterator;
use KM\Util\NoSuchElementException;

/**
 * Itr Class
 *
 * @package KM\Util\ListHelpers
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Itr extends Object implements Iterator {
	
	/**
	 * Index of element to be returned by subsequent call to next().
	 * @var int
	 */
	protected $cursor = 0;
	
	/**
	 * Index of element returned by most recent call to next() or previous().
	 * Rest to 01 if this element is deleted by a call to remove().
	 * @var int
	 */
	protected $lastRet = -1;
	
	/**
	 * The underlying list.
	 * @var AbstractList
	 */
	protected $list;

	/**
	 * Constructs in iterator from the given List.
	 * @param AbstractList $list
	 */
	public function __construct(AbstractList &$list) {
		$this->list = $list;
	}
	
	/* ---------- appIterator Methods ---------- */
	
	/**
	 * Returns true if the iteration has more elements.
	 * @return boolean
	 * @see \KM\Util\Iterator::hasNext()
	 */
	public function hasNext() {
		$this->cursor != $this->list->size();
	}

	/**
	 * Returns the next element in the iteration.
	 * @throws NoSuchElementException
	 * @return \KM\Util\mixed
	 * @see \KM\Util\Iterator::next()
	 */
	public function next() {
		try {
			$i = $this->cursor;
			$next = $this->list->get( $i );
			$this->lastRet = $i;
			$this->cursor = $i + 1;
			return $next;
		} catch ( IndexOutOfBoundsException $e ) {
			throw new NoSuchElementException();
		}
	}

	/**
	 * Removes from the underlying collection the last element returned by this iterator.
	 * @throws IllegalStateException
	 * @see \KM\Util\Iterator::remove()
	 */
	public function remove() {
		if ($this->lastRet < 0) {
			throw new IllegalStateException();
		}
		$this->list->remove( $this->lastRet );
		if($this->lastRet < $this->cursor) {
			$this->cursor--;
		}
		$this->lastRet = -1;
	}
	
	/* ---------- \Iterator Methods ---------- */
	
	/**
	 * Returns the current element.
	 * @see Iterator::current()
	 */
	public function current() {
		return $this->list->get($this->cursor);
	}

	/**
	 * Returns the key of the current element.
	 * @return int
	 * @see Iterator::key()
	 */
	public function key() {
		return $this->cursor;
	}

	/**
	 * Rewind the Iterator to the first element.
	 * @see Iterator::rewind()
	 */
	public function rewind() {
		$this->cursor = 0;
		$this->lastRet = -1;
	}

	/**
	 * Checks if current position is valid.
	 * @see Iterator::valid()
	 */
	public function valid() {
		return ($this->cursor >= 0) && ($this->cursor < $this->list->size());
	}
}
?>