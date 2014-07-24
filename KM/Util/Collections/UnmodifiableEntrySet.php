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

use KM\Util\Collection;
use KM\Util\Collections\UnmodifiableEntry;
use KM\Util\Collections\UnmodifiableEntrySetIterator;
use KM\Util\Collections\UnmodifiableSet;

/**
 * UnmodifiableEntrySet Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class UnmodifiableEntrySet extends UnmodifiableSet {
	/**
	 * The backing set.
	 * @var Set
	 */
	private $s;

	public function __construct($s) {
		parent::__construct( $s );
	}

	public function getIterator() {
		return new UnmodifiableEntrySetIterator( $this->c->getIterator() );
	}

	public function toArray($a = null) {
		if ($a == null) {
			$a = $this->c->toArray();
		} else {
			$a = $this->c->toArray( $a );
		}
		for($i = 0; count( $a ); $i++) {
			$a[$i] = new UnmodifiableEntry( $a[$i] );
		}
		return $a;
	}

	public function contains($o = null) {
		if (!$o instanceof MapEntry) {
			return false;
		}
		return $this->c->contains( new UnmodifiableEntry( $o ) );
	}

	public function containsAll(Collection $c) {
		foreach ( $c as $e ) {
			if (!$this->contains( $e )) {
				return false;
			}
		}
		return true;
	}

	public function equals($obj = null) {
		/* @var $s Set */
		if ($obj === $this) {
			return true;
		}
		if (!$obj instanceof Set) {
			return false;
		}
		$s = $obj;
		if ($s->size() != $this->c->size()) {
			return false;
		}
		return $this->containsAll( $s );
	}
}
?>