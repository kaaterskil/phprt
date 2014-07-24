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
namespace KM\Util;

use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\Object;

/**
 * The root class from which all event state objects shall be derived.
 * All Events are constructed with a reference to the object, the 'source', that is logically deemed
 * to be the object upon which the Event in question initially occurred.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class EventObject extends Object implements Serializable {
	
	/**
	 * The object on which the Event initially occurred.
	 * @Transient
	 * @var Object
	 */
	protected $source;

	/**
	 * Constructs a prototypical Event.
	 * @param Object $source The object on which the Event initially occurred,
	 */
	public function __construct(Object $source) {
		$this->source = $source;
	}

	/**
	 * Returns the object on which the Event initially occurred.
	 * @return Object
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * Returns a string representation of this EventObject.
	 * @return string
	 * @see \KM\Lang\Object::__toString()
	 */
	public function __toString() {
		return $this->getClass()->getName() . '[source=' . $this->source . ']';
	}
}
?>