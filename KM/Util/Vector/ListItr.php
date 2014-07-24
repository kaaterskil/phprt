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
use KM\Util\ListIterator;
use KM\Util\NoSuchElementException;
use KM\Util\Vector;

/**
 * An optimized version of AbstractList.ListItr
 *
 * @package KM\Util\Vector
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class ListItr extends Itr implements ListIterator {
	
	public function __construct($index, Vector $vector) {
		parent::__construct($vector);
		$this->cursor = $index;
	}
	
	public function hasPrevious() {
		return $this->cursor != 0;
	}
	
	public function nextIndex() {
		return $this->cursor;
	}
	
	public function previousIndex() {
		return $this->cursor - 1;
	}
	
	public function previous() {
		$i = $this->cursor - 1;
		if($i < 0) {
			throw new NoSuchElementException();
		}
		$this->cursor = $i;
		return $this->vector->get($this->lastRet = $i);
	}
	
	public function set($e) {
		if($this->lastRet == -1) {
			throw new IllegalStateException();
		}
		$this->vector->set($this->lastRet, $e);
	}
	
	public function add($e){
		$i = $this->cursor;
		$this->vector->add($e);
		$this->cursor = $i + 1;
		$this->lastRet = -1;
	}
}
?>