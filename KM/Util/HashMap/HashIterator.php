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
namespace KM\Util\HashMap;

use KM\Lang\IllegalStateException;
use KM\Lang\Object;
use KM\Util\HashMap;
use KM\Util\NoSuchElementException;
use KM\Util\Iterator;

/**
 * HashIterator Class
 *
 * @package KM\Util\HashMap
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class HashIterator extends Object {
	
	/**
	 * The next entry to return.
	 * @var Node
	 */
	protected $next = null;
	
	/**
	 * Current entry.
	 * @var Node
	 */
	protected $current = null;
	
	/**
	 * Current slot.
	 * @var int
	 */
	protected $index;
	
	/**
	 * The backing map
	 * @var HashMap
	 */
	protected $map;

	public function __construct(HashMap $m) {
		$this->map = $m;
		$t = $m->getTable();
		$this->index = 0;
		if ($t != null && $m->size() > 0) {
			do {
			} while ( $this->index < count( $t ) && ($this->next = $t[$this->index++]) == null );
		}
	}

	public function hasNext() {
		return $this->next != null;
	}

	public function nextNode() {
		/* @var $e Node */
		$e = $this->next;
		if ($e == null) {
			throw new NoSuchElementException();
		}
		$this->current = $e;
		if (($this->next = $e->getNext()) == null && ($t = $this->map->getTable()) != null) {
			do {
			} while ( $this->index < count( $t ) && ($this->next = $t[$this->index++]) == null );
		}
		return $e;
	}

	public function remove() {
		/* @var $p Node */
		$p = $this->current;
		if ($p == null) {
			throw new IllegalStateException();
		}
		$this->current = null;
		$key = $p->getKey();
		$this->map->removeNode( $this->map->hash( $key ), $key, null, false, false );
	}

	public function rewind() {
		$this->index = 0;
	}

	public function key() {
		return ($key = $this->current->getKey()) != null ? $key : null;
	}

	public function current() {
		$t = $this->map->getTable();
		return ($current = $t[$this->index]) != null ? $current : null;
	}

	public function valid() {
		return $this->index >= 0 && $this->index < $this->map->size();
	}
}
?>