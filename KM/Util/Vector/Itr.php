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
namespace KM\Util\Vector;

use KM\Lang\IllegalStateException;
use KM\Lang\Object;
use KM\Util\Iterator;
use KM\Util\NoSuchElementException;
use KM\Util\Vector;

/**
 * An optimized version of AbstractList.Itr
 *
 * @package KM\Util\Vector
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Itr extends Object implements Iterator {
	
	/**
	 * The underlying vector.
	 * @var Vector
	 */
	protected $vector;
	
	/**
	 * Index of the next element to return.
	 * @var int
	 */
	protected $cursor = 0;
	
	/**
	 * Index of last element returned; -1 if no such.
	 * @var int
	 */
	protected $lastRet = -1;

	/**
	 * Constructs a new Iterator with the given underlying Vector.
	 * @param Vector $vector
	 */
	public function __construct(Vector $vector) {
		$this->vector = $vector;
	}

	public function hasNext() {
		return $this->cursor != $this->vector->size();
	}

	public function next() {
		$i = $this->cursor;
		if ($i >= $this->vector->size()) {
			throw new NoSuchElementException();
		}
		$this->cursor = $i + 1;
		return $this->vector->get( $this->lastRet = $i );
	}

	public function remove() {
		if ($this->lastRet == -1) {
			throw new IllegalStateException();
		}
		$this->vector->removeAtIndex( $this->lastRet );
		$this->cursor = $this->lastRet;
		$this->lastRet = -1;
	}
	public function rewind() {
		$this->cursor = 0;
		$this->lastRet = -1;
	}
	
	public function current() {
		return $this->vector->get($this->cursor);
	}
	
	public function key() {
		return $this->cursor;
	}
	
	public function valid() {
		return $this->cursor < $this->vector->size();
	}
}
?>