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
namespace KM\Beans;

use KM\Lang\Clazz;
use KM\Util\HashMap;
use KM\Util\Map;

/**
 * PersistenceDelegateFinder Class
 *
 * @package class_container
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class PersistenceDelegateFinder extends InstanceFinder {
	
	/**
	 * A registry of class types
	 * @var Map
	 */
	private $registry;

	public function __construct() {
		parent::__construct( PersistenceDelegate::clazz(), true, 'PersistenceDelegate', null );
		$this->registry = new HashMap( '\KM\Lang\Clazz, \KM\Beans\PersistenceDelegate' );
	}

	public function register(Clazz $type, PersistenceDelegate $delegate = null) {
		if ($delegate != null) {
			$this->registry->put( $type, $delegate );
		} else {
			$this->registry->remove( $type );
		}
	}

	public function find(Clazz $type = null) {
		$delegate = $this->registry->get( $type );
		return ($delegate != null) ? $delegate : parent::find( $type );
	}
}
?>