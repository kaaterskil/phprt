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
namespace KM\Util\ArrayObject;

use KM\Lang\Object;
use KM\Util\Iterator;
use KM\Util\ArrayObject;
use KM\Util\ArrayObject\Node;
use KM\Util\NoSuchElementException;
use KM\Lang\IllegalStateException;

/**
 * Itr Class
 *
 * @package KM\Util\ArrayObject
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Itr extends Object implements Iterator {
	
	private $arrObj;
	
	/**
	 * Pointer to the most recently returned node.
	 * @var Node
	 */
	private $lastRet = null;
	
	/**
	 * Pointer to the next node.
	 * @var Node
	 */
	private $next;
	
	/**
	 * Creates a new iterator with the given backing array object.
	 * @param ArrayObject $arrObj
	 */
	public function __construct(ArrayObject $arrObj) {
		$this->arrObj = $arrObj;
		$this->next = $arrObj->getFirst();
	}
	
	public function hasNext() {
		return $this->next != null;
	}
	
	public function next() {
		if(!$this->hasNext()) {
			throw new NoSuchElementException();
		}
		$this->lastRet = $this->next;
		$this->next = $this->next->next;
		return $this->lastRet->getValue();
	}
	
	public function remove() {
		if($this->lastRet == null) {
			throw new IllegalStateException();
		}
		$lastNext = $this->lastRet->next;
		$this->arrObj->remove($this->lastRet->getKey());
		if($this->next == $this->lastRet) {
			$this->next = $lastNext;
		}
		$this->lastRet = null;
	}
	
	public function rewind() {
		$this->next = $this->arrObj->getFirst();
	}
	
	public function current() {
		return $this->next->getValue();
	}
	
	public function key() {
		return $this->next->getKey();
	}
	
	public function valid() {
		return $this->next != null;
	}
}
?>