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

use KM\Lang\Object;
use KM\Util\Iterator;
use KM\Util\LinkedList;
/**
 * DescendingIterator Class
 *
 * @package		KM\Util\LinkedList
 * @author		Blair
 * @copyright	Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version		SVN $Id$
 */
class DescendingIterator extends Object implements Iterator {
	
	/**
	 * The backing listIterator
	 * @var ListItr
	 */
	private $itr;
	
	public function __construct(LinkedList $list) {
		$this->itr = new ListItr($list->size(), $list);
	}
	
	public function hasNext() {
		return $this->itr->hasPrevious();
	}
	
	public function next() {
		return $this->itr->previous();
	}
	
	public function remove() {
		$this->itr->remove();
	}
	
	public function rewind() {
		$this->itr->rewind();
	}
	
	public function current() {
		return $this->itr->current();
	}
	
	public function key() {
		return $this->itr->key();
	}
	
	public function valid() {
		return $this->itr->valid();
	}
}
?>