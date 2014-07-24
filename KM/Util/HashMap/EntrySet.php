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

use KM\Util\AbstractSet;
use KM\Util\HashMap;
use KM\Util\Map\Entry;

/**
 * EntrySet Class
 *
 * @package KM\Util\HashMap
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class EntrySet extends AbstractSet {
	
	/**
	 * The backing map.
	 * @var HashMap
	 */
	private $map;

	public function __construct(HashMap $m) {
		parent::__construct( "\KM\Util\Map\Entry" );
		$this->map = $m;
	}

	public function size() {
		return $this->map->size();
	}

	public function clear() {
		$this->map->clear();
	}

	public function getIterator() {
		return new EntryIterator( $this->map );
	}

	public function contains($o = null) {
		/* @var $e Entry */
		/* @var $candidate Node */
		if ($o == null || !$o instanceof Entry) {
			return false;
		}
		$e = $o;
		$key = $e->getKey();
		$candidate = $this->map->getNode( $this->map->hash( $key ), $key );
		return $candidate != null && $candidate->equals($e);
	}

	public function remove($o = null) {
		/* @var $e Entry */
		if ($o instanceof Entry) {
			$e = $o;
			$key = $e->getKey();
			$value = $e->getValue();
			return $this->map->removeNode( $this->map->hash( $key ), $key, $value, true, true ) != null;
		}
		return false;
	}
}
?>