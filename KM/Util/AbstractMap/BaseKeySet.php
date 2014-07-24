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
use KM\Util\AbstractSet;
use KM\Util\Map;
use KM\Util\Iterator;

/**
 * <p>This implementation returns a set that subclasses {@link AbstractSet}.
 * The subclass's iterator method returns a "wrapper object" over this map's <tt>entrySet()</tt>
 * iterator. The <tt>size</tt> method delegates to this map's <tt>size</tt> method and the
 * <tt>contains</tt> method delegates to this map's <tt>containsKey</tt> method.
 *
 * <p>The set is created the first time this method is called, and returned in response to all
 * subsequent calls.
 *
 * @package KM\Util\Map
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class BaseKeySet extends AbstractSet {
	private $map;

	public function __construct(Map $map) {
		$this->map = $map;
	}
	
	public function getParameterType() {
		return $this->map->getKeyParameterType();
	}

	public function getIterator() {
		return new BaseKeySetIterator( $this->map->keySet()->getIterator() );
	}

	public function size() {
		return $this->map->size();
	}

	public function isEmpty() {
		return $this->map->isEmpty();
	}

	public function clear() {
		$this->map->clear();
	}

	public function contains($key = null) {
		return $this->map->containsKey( $key );
	}
}
?>