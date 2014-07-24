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
use KM\Util\MapEntry;

/**
 * UnmodifiableEntry Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class UnmodifiableEntry extends Object implements MapEntry {
	/**
	 * The backing entry.
	 * @var MapEntry
	 */
	private $e;

	public function __construct(MapEntry $e) {
		$this->e = $e;
	}

	public function getKey() {
		return $this->e->getKey();
	}

	public function getValue() {
		return $this->e->getValue();
	}

	public function setValue($value = null) {
		throw new UnsupportedOperationException();
	}

	public function hashCode() {
		return $this->e->hashCode();
	}

	public function equals($obj = null) {
		/* @var $t MapEntry */
		if ($this === $obj) {
			return true;
		}
		if (!$obj instanceof MapEntry) {
			return false;
		}
		$t = $obj;
		return Collections::eq( $this->e->getKey(), $t->getKey() ) &&
			 Collections::eq( $this->e->getValue(), $t->getValue() );
	}

	public function __toString() {
		return (string) $this->e;
	}
}
?>