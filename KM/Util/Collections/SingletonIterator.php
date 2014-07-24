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
use KM\Util\Iterator;
use KM\Lang\NoSuchMethodException;
use KM\Lang\UnsupportedOperationException;

/**
 * SingletonIterator Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class SingletonIterator extends Object implements Iterator {
	private $e;
	private $hasNext = true;

	public function __construct($e) {
		$this->e = $e;
	}

	public function hasNext() {
		return $this->hasNext;
	}

	public function next() {
		if ($this->hasNext) {
			$this->hasNext = false;
			return $this->e;
		}
		throw new NoSuchMethodException();
	}

	public function remove() {
		throw new UnsupportedOperationException();
	}

	public function rewind() {
		$this->hasNext = true;
	}

	public function key() {
		return 0;
	}

	public function current() {
		return $this->e;
	}

	public function valid() {
		return ($this->hasNext == true);
	}
}
?>