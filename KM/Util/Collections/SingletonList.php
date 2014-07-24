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

use KM\IO\Serializable;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\Object;
use KM\Util\AbstractList;
use KM\Util\Collections;

/**
 * SingletonList Class
 *
 * @package KM\Util\Collections
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class SingletonList extends AbstractList implements Serializable {
	private $element;

	public function __construct(Object $obj) {
		parent::__construct('\KM\Lang\Object');
		$this->element = $obj;
	}

	public function getIterator() {
		return Collections::singletonIterator( $this->element );
	}

	public function size() {
		return 1;
	}

	public function contains($o = null) {
		return Collections::eq( $obj, $this->element );
	}

	public function get($index) {
		if ($index != 0) {
			throw new IndexOutOfBoundsException( 'Index: ' . $index . ', Size: 1' );
		}
		return $this->element;
	}
}
?>