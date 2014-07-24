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
namespace KM\Util\ServiceLoader;

use KM\Lang\Object;
use KM\Util\Iterator;
use KM\Lang\System;
use KM\IO\File;
use KM\IO\IOException;
use KM\Util\HashSet;
use KM\Util\ServiceLoader;
use KM\Util\NoSuchElementException;
use KM\IO\UnsupportedEncodingException;
use KM\Lang\UnsupportedOperationException;

/**
 * Inner class implementing fully-lazy provider lookup
 *
 * @package KM\Util\ServiceLoader
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class LazyIterator extends Object implements Iterator {
	
	/**
	 *
	 * @var \ReflectionClass
	 */
	protected $service;
	
	/**
	 * The backing service loader.
	 * @var ServiceLoader
	 */
	protected $loader;
	
	/**
	 *
	 * @var Iterator
	 */
	protected $configs = null;
	
	/**
	 *
	 * @var Iterator
	 */
	protected $pending = null;
	
	/**
	 * The current service name.
	 * @var string
	 */
	protected $currentName = null;
	
	/**
	 * The next service name.
	 * @var string
	 */
	protected $nextName = null;

	public function __construct(\ReflectionClass $service, ServiceLoader $loader) {
		$this->service = $service;
		$this->loader = $loader;
	}

	public function current() {
		return $this->currentService();
	}

	public function key() {
		return $this->pending->key();
	}

	public function rewind() {
		$this->nextName = null;
	}

	public function valid() {
		return $this->hasNext();
	}

	public function hasNext() {
		return $this->hasNextService();
	}

	public function next() {
		return $this->nextService();
	}

	public function remove() {
		throw new UnsupportedOperationException();
	}

	private function hasNextService() {
		if ($this->nextName != null) {
			return true;
		}
		if ($this->configs == null) {
			$fname = System::getProperty( 'user.dir' );
			if ($fname == null) {
				throw new IllegalStateException( "Can't find the user root directory" );
			}
			try {
				$configs = new HashSet( '\KM\IO\File' );
				$f = new File( $fname, 'services' );
				$f = new File( $f, str_replace('\\', '.', $this->service->getName()) );
				if($f->exists() && $f->isFile()) {
					$configs->add( $f );
				}
				$this->configs = $configs->getIterator();
			} catch ( IOException $ioe ) {
				trigger_error( 'Error locating configuration files' );
			}
		}
		while ( ($this->pending == null) || !$this->pending->hasNext() ) {
			if (!$this->configs->hasNext()) {
				return false;
			}
			$this->pending = $this->loader->parse( $this->configs->next()
				->getPath() );
		}
		$this->currentName = $this->nextName;
		$this->nextName = $this->pending->next();
		return true;
	}

	private function nextService() {
		if (!$this->hasNextService()) {
			throw new NoSuchElementException();
		}
		$cn = $this->nextName;
		$this->nextName = null;
		$c = null;
		try {
			$c = new \ReflectionClass( $cn );
		} catch ( \ReflectionException $e ) {
			$format = 'Provider "%s" not found';
			trigger_error( sprintf( $format, $cn ) );
		}
		if (!$c->isSubclassOf( $this->service->getName() )) {
			$format = 'Provider "%s" not a subtype';
			trigger_error( sprintf( $format, $cn ) );
		}
		try {
			$p = $c->newInstance();
			$this->loader->providers->put( $cn, $p );
			return $p;
		} catch ( \ReflectionException $e ) {
			$format = 'Provider "%s" could not be instantiated';
			trigger_error( sprintf( $format, $cn ) );
		}
	}

	private function currentService() {
		$cn = $this->currentName;
		$c = null;
		try {
			$c = new \ReflectionClass( $cn );
		} catch ( \Exception $e ) {
			$format = 'Provider "%s" not found';
			trigger_error( sprintf( $format, $cn ) );
		}
		if (!$c->isSubclassOf( $this->service->getName() )) {
			$format = 'Provider "%s" not a subtype';
			trigger_error( sprintf( $format, $cn ) );
		}
		try {
			$p = $c->newInstance();
			$this->loader->providers->put( $cn, $p );
			return $p;
		} catch ( \ReflectionException $e ) {
			$format = 'Provider "%s" could not be instantiated';
			trigger_error( sprintf( $format, $cn ) );
		}
	}
}
?>