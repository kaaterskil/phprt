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
namespace KM\Util\Collections;

use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\Util\ListIterator;

/**
 * UnmodifiableListIterator Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class UnmodifiableListIterator extends Object implements ListIterator {
	/**
	 * The backing iterator.
	 * @var ListIterator
	 */
	private $i;

	public function __construct(ListIterator $i) {
		$this->i = $i;
	}

	public function hasNext() {
		return $this->i->hasNext();
	}

	public function next() {
		return $this->i->next();
	}
	
	public function hasPrevious() {
		return $this->i->hasPrevious();
	}
	
	public function previous() {
		return $this->i->previous();
	}
	
	public function nextIndex() {
		return $this->i->nextIndex();
	}
	
	public function previousIndex() {
		return $this->i->previousIndex();
	}

	public function remove() {
		throw new UnsupportedOperationException();
	}
	
	public function set($e) {
		throw new UnsupportedOperationException();
	}
	
	public function add($e) {
		throw new UnsupportedOperationException();
	}
}
?>