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
use KM\Util\AbstractList;
use KM\Util\ListIterator;
use KM\Util\NoSuchElementException;
use KM\Lang\IndexOutOfBoundsException;

/**
 * ListItr Class
 *
 * @package KM\Util\ListHelpers
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class ListItr extends Itr implements ListIterator {

	/**
	 * Constructs an iterator with the given index and underlying List.
	 * @param int $index
	 * @param array $table
	 */
	public function __construct($index = 0, AbstractList &$list) {
		parent::__construct($list);
		$this->cursor = (int) $index;
	}

	/**
	 * Returns true if this list iterator has more elements when traversing the list in
	 * the reverse direction.
	 * @return boolean
	 * @see \KM\Util\ListIterator::hasPrevious()
	 */
	public function hasPrevious() {
		return $this->cursor != 0;
	}

	/**
	 * Returns the previous element in the list and moves the cursor position backwards.
	 * This method may be called repeatedly to iterate through the list backwards, or
	 * intermixed with calls to next() to go back and forth. (Note that alternating calls
	 * to next and previous will return the same element repeatedly.)
	 * @throws NoSuchElementException
	 * @return mixed
	 * @see \KM\Util\ListIterator::previous()
	 */
	public function previous() {
		$i = $this->cursor - 1;
		try{
			$i = $this->cursor - 1;
			$previous = $this->list->get($i);
			$this->lastRet = $this->cursor = $i;
			return $previous;
		}catch(IndexOutOfBoundsException $e){
			throw new NoSuchElementException();
		}
	}

	/**
	 * Returns the index of the element that would be returned by a subsequent call to
	 * next().
	 * @return int
	 * @see \KM\Util\ListIterator::nextIndex()
	 */
	public function nextIndex() {
		return $this->cursor;
	}

	/**
	 * Returns the index of the element that would be returned by a subsequent call to
	 * previous().
	 * @return int
	 * @see \KM\Util\ListIterator::previousIndex()
	 */
	public function previousIndex() {
		return $this->cursor - 1;
	}

	/**
	 * Replaces the last element returned by next() or previous() with the specified
	 * element.
	 * @param mixed $e
	 * @throws IllegalStateException
	 * @see \KM\Util\ListIterator::set()
	 */
	public function set($e) {
		if ($this->lastRet < 0) {
			throw new IllegalStateException();
		}
		$this->list->set($this->lastRet, $e);
	}

	/**
	 * Inserts the specified element into the list.
	 * @param mixed $e
	 * @see \KM\Util\ListIterator::add()
	 */
	public function add($e) {
		$i = $this->cursor;
		$this->list->addAtIndex($i, $e);
		$this->lastRet = -1;
		$this->cursor = $i + 1;
	}
}
?>