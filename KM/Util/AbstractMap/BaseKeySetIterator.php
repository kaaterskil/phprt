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

namespace KM\Util\AbstractMap;

use KM\Lang\Object;
use KM\Util\Iterator;
use KM\Util\Set;

/**
 * The iterator found in the base key set.
 *
 * @package		KM\Util\Map
 * @author		Blair
 * @copyright	Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version		SVN $Id$
 */
class BaseKeySetIterator extends Object implements Iterator {
	
	/**
	 * The backing entry set iterator.
	 * @var Iterator
	 */
	private $i;
	
	/**
	 * Constructs an iterator with the given backing entry set iterator.
	 * @param Iterator $iterator
	 */
	public function __construct(Iterator $iterator) {
		$this->i = $iterator;
	}
	
	public function hasNext() {
		return $this->i->hasNext();
	}
	
	public function next() {
		/* @var $entry Entry */
		$entry = $this->i->next();
		return $entry->getKey();
	}
	
	public function remove() {
		$this->i->remove();
	}
	
	public function current() {
		/* @var $entry Entry */
		$entry = $this->i->current();
		return $entry->getKey();
	}
	
	public function key() {
		return $this->i->key();
	}
	
	public function rewind() {
		$this->i->rewind();
	}
	
	public function valid() {
		return $this->i->valid();
	}
}
?>