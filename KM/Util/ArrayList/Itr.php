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

use KM\Lang\Object;
use KM\Util\Iterator;
use KM\Util\ArrayList;
use KM\Util\NoSuchElementException;
use KM\Lang\IllegalStateException;
use KM\Lang\IndexOutOfBoundsException;

/**
 * An optimized version of AbstractList.Itr
 *
 * @package KM\Util\ArrayList
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Itr extends Object implements Iterator {
	protected $list;
	protected $cursor = 0;
	protected $lastRet = -1;

	public function __construct(ArrayList $list) {
		$this->list = $list;
	}
	
	/* ---------- appIterator Methods ---------- */

	public function hasNext() {
		return $this->cursor != $this->list->size();
	}

	public function next() {
		$i = $this->cursor;
		if ($i >= $this->list->size()) {
			throw new NoSuchElementException();
		}
		$elementData = $this->list->toArray();
		$this->cursor = $i + 1;
		return $elementData[$this->lastRet = $i];
	}

	public function remove() {
		if ($this->lastRet < 0) {
			throw new IllegalStateException();
		}
		$this->list->removeAt( $this->lastRet );
		$this->cursor = $this->lastRet;
		$this->lastRet = -1;
	}
	
	/* ---------- \Iterator Methods ---------- */

	public function rewind() {
		$this->cursor = 0;
		$this->lastRet = -1;
	}

	public function current() {
		$this->lastRet = $this->cursor;
		return $this->list->elementData( $this->cursor );
	}

	public function key() {
		$this->lastRet = $this->cursor;
		return $this->cursor;
	}

	public function valid() {
		return ($this->cursor >= 0) && ($this->cursor < $this->list->size());
	}
}
?>