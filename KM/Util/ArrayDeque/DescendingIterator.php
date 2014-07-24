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
namespace KM\Util\ArrayDeque;

use KM\Lang\IllegalStateException;
use KM\Lang\Object;
use KM\Util\ArrayDeque;
use KM\Util\Iterator;
use KM\Util\NoSuchElementException;

/**
 * DescendingIterator Class
 *
 * @package KM\Util\ArrayDeque
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class DescendingIterator extends Object implements Iterator {
	
	/**
	 * Index of element to be returned by subsequent call to next.
	 * @var int
	 */
	private $cursor;
	
	/**
	 * Tail recorded at construction (also in remove) to stop iterator.
	 * @var int
	 */
	private $fence;
	
	/**
	 * Index of element returned by most recent call to next.
	 * Reset to -1 if element is deleted by a call to remove.
	 * @var int
	 */
	private $lastRef = -1;
	
	/**
	 * The backing deque
	 * @var ArrayDeque
	 */
	private $deque;

	public function __construct(ArrayDeque $deque) {
		$this->deque = $deque;
		$this->cursor = $this->deque->tail;
		$this->fence = $this->deque->head;
	}

	public function hasNext() {
		return $this->cursor != $this->fence;
	}

	public function next() {
		if ($this->cursor == $this->fence) {
			throw new NoSuchElementException();
		}
		$this->cursor = ($this->cursor - 1) & (count($this->deque->elements) - 1);
		$result = $this->deque->elements[$this->cursor];
		$this->lastRef = $cursor;
		return $result;
	}
	
	public function remove() {
		if($this->lastRef < 0) {
			throw new IllegalStateException();
		}
		if(!$this->deque->delete($this->lastRef)) {
			$this->cursor = ($this->cursor + 1) & (count($this->deque->elements) - 1);
			$this->fence = $this->deque->head;
		}
		$this->lastRef = -1;
	}
	
	public function rewind() {
		$this->cursor = $this->deque->tail;
	}
	
	public function key() {
		return $this->cursor;
	}
	
	public function current() {
		return $this->deque->elements[$this->cursor];
	}
	
	public function valid() {
		return $this->hasNext();
	}
}
?>