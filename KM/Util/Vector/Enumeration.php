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

use KM\Lang\Object;
use KM\Util\Enumeration as appEnumeration;
use KM\Util\NoSuchElementException;

/**
 * Enumeration Class
 *
 * @package		KM\Util\Vector
 * @author		Blair
 * @copyright	Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version		SVN $Id$
 */
class Enumeration extends Object implements appEnumeration {
	
	/**
	 * A reference to the underlying data array
	 * @var array
	 */
	private $elementData = array();
	
	/**
	 * A reference to the size of the underlying data array.
	 * @var int
	 */
	private $elementCount = 0;
	
	/**
	 * The pointer to the current element in the underlying data array.
	 * @var int
	 */
	private $count = 0;
	
	/**
	 * Constructs a new vector Enumeration with the given data and element count.
	 * @param array $data
	 * @param int $count
	 */
	public function __construct(array &$data, $count) {
		$this->elementData = $data;
		$this->elementCount = (int) $count;
	}
	
	public function hasMoreElements() {
		return $this->count < $this->elementCount;
	}
	
	public function nextElement() {
		if($this->count < $this->elementCount) {
			return $this->elementData[$this->count++];
		}
		throw new NoSuchElementException('Vector Enumeration');
	}
}
?>