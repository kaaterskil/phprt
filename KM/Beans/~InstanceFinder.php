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
use KM\Lang\Object;

/**
 * This is utility class that provides basic functionality to find an auxiliary class for a JavaBean
 * specified by its type.
 *
 * @package KM\Beans
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class InstanceFinder extends Object {
	private $type;
	private $allow;
	private $suffix;
	
	/**
	 * A list of package names
	 * @var string[]
	 */
	private $packages = [];

	public function __construct(Clazz $type, $allow, $suffix, array $packages) {
		$this->type = $type;
		$this->allow = (boolean) $allow;
		$this->suffix = (string) $suffix;
		$this->packages = $packages;
	}

	public function getPackages() {
		return $this->packages;
	}

	public function setPackages(array $packages = []) {
		if ($packages != null && count( $packages ) > 0) {
			$this->packages = $packages;
		} else {
			$this->packages = [];
		}
	}

	public function find(Clazz $type = null) {
		if ($type == null) {
			return null;
		}
		$name = $type->getName() . $this->suffix;
		$object = $this->instantiate( $type, $name );
		if ($object != null) {
			return $object;
		}
		if ($this->allow) {
			$object = $this->instantiate( $type, null );
			if ($object != null) {
				return $object;
			}
		}
		$index = strrpos( $name, '\\' );
		if ($index !== false) {
			$name = substr( $name, $index + 1 );
		}
		foreach ( $this->packages as $prefix ) {
			$object = $this->instantiateWithPrefix( $type, $prefix, $name );
			if ($object != null) {
				return $object;
			}
		}
		return null;
	}

	protected function instantiate(Clazz $type = null, $name = null) {
		if ($type != null) {
			try {
				if ($name != null) {
					$type = Clazz::forName( $name );
				}
				if ($this->type->isAssignableFrom( $type )) {
					return $type->newInstance();
				}
			} catch ( \Exception $e ) {
				// Ignore
			}
		}
		return null;
	}

	protected function instantiateWithPrefix(Clazz $type, $prefix, $name) {
		return $this->instantiate( $type, $prefix . '\\' . $name );
	}
}
?>