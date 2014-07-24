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
use KM\Util\AbstractCollection;
use KM\Util\Collection;
use KM\Util\Iterator;
use KM\Util\Map;

/**
 * The base values collection used in AbstractMap
 *
 * @package KM\Util\Map
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class BaseValuesCollection extends AbstractCollection {
	private $map;

	public function __construct(Map $map) {
		$this->map = $map;
	}
	
	public function getParameterType() {
		return $this->map->getValueParameterType();
	}

	public function getIterator() {
		return new BaseValuesIterator( $this->map->entrySet()->getIterator() );
	}

	public function size() {
		$this->map->size();
	}

	public function isEmpty() {
		return $this->map->isEmpty();
	}

	public function clear() {
		$this->map->clear();
	}

	public function contains($value = null) {
		return $this->map->containsValue( $value );
	}
}
?>