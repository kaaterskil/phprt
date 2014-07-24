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
namespace KM\Util\ServiceLoader;

use KM\Lang\Object;
use KM\Util\Iterator;
use KM\Util\ServiceLoader;
use KM\Lang\UnsupportedOperationException;

/**
 * Itr Class
 *
 * @package KM\Util\ServiceLoader
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Itr extends Object implements Iterator {
	
	/**
	 *
	 * @var Iterator
	 */
	private $knownProviders;
	
	/**
	 * The backing service loader.
	 * @var ServiceLoader
	 */
	private $loader;

	public function __construct(ServiceLoader $loader) {
		$this->knownProviders = $loader->providers->entrySet()->getIterator();
		$this->loader = $loader;
	}

	public function hasNext() {
		if ($this->knownProviders->hasNext()) {
			return true;
		}
		return $this->loader->getLookupIterator()->hasNext();
	}

	public function next() {
		if ($this->knownProviders->hasNext()) {
			return $this->knownProviders->next()->getValue();
		}
		return $this->loader->getLookupIterator()->next();
	}

	public function remove() {
		throw new UnsupportedOperationException();
	}

	public function current() {
		if($this->knownProviders->current()) {
			return $this->knownProviders->current();
		}
		return $this->loader->getLookupIterator()->current();
	}

	public function key() {
		if($this->knownProviders->key()) {
			return $this->knownProviders->key();
		}
		return $this->loader->getLookupIterator()->key();
	}

	public function rewind() {
		$this->knownProviders->rewind();
		$this->loader->getLookupIterator()->rewind();
	}

	public function valid() {
		if($this->knownProviders->valid()){
			return $this->knownProviders->valid();
		}
		return $this->loader->getLookupIterator()->valid();
	}
}
?>