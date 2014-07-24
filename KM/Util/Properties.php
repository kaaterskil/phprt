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

use KM\Util\HashMap;
use KM\Util\Iterator;
use KM\Util\Map;
use KM\Util\Set;
use KM\Lang\System;

/**
 * The Properties class represents a persistent set of properties.
 * The Properties can be saved to a stream or loaded from a stream. Each key and its
 * corresponding value in the property list is a string.
 * <p>
 * A property list can contain another property list as its "defaults"; this second property list is
 * searched if the property key is not found in the original property list.
 * <p>
 * Because {@code Properties} inherits from {@code HashMap}, the {@code put} and {@code putAll}
 * methods can be applied to a {@code Properties} object.
 * <p>
 * The {@link #load()} and {@link #store()} methods load and store properties from and to
 * a character based stream in a simple line-oriented format specified below.
 *
 * <pre>
 * &lt;!DOCTYPE properties SYSTEM "http://java.sun.com/dtd/properties.dtd"&gt;
 * </pre>
 * Note that the system URI (http://java.sun.com/dtd/properties.dtd) is <i>not</i> accessed when
 * exporting or importing properties; it merely serves as a string to uniquely identify the DTD,
 * which is:
 * <pre>
 * &lt;?xml version="1.0" encoding="UTF-8"?&gt;
 *
 * &lt;!-- DTD for properties --&gt;
 *
 * &lt;!ELEMENT properties ( comment?, entry* ) &gt;
 *
 * &lt;!ATTLIST properties version CDATA #FIXED "1.0"&gt;
 *
 * &lt;!ELEMENT comment (#PCDATA) &gt;
 *
 * &lt;!ELEMENT entry (#PCDATA) &gt;
 *
 * &lt;!ATTLIST entry key CDATA #REQUIRED&gt;
 * </pre>
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Properties extends HashMap {
	
	/**
	 * A property list that contains default values for any keys not found in this property list.
	 * @var Properties
	 */
	private $defaults;

	/**
	 * Constructs an empty property list with the given defaults.
	 * @param Properties $defaults
	 */
	public function __construct(Properties $defaults = null) {
		parent::__construct( '<string, string>' );
		$this->defaults = $defaults;
	}

	/**
	 * Searches for the property with the specified key in this property list.
	 * If the key is not found in this property list, the default property list, and its defaults,
	 * recursively, are then checked. The method returns the default value argument if the property
	 * is not found.
	 * @param string $key
	 * @return string
	 */
	public function getProperty($key, $defaultValue = null) {
		$key = (string) $key;
		$oval = parent::get( $key );
		$sval = strlen( $oval ) > 0 ? $oval : null;
		$val = ($sval == null && $this->defaults != null) ? $this->defaults->getProperty( $key ) : $sval;
		return ($val == null) ? $defaultValue : $val;
	}

	public function setProperty($key, $value) {
		return $this->put( $key, $value );
	}

	/**
	 * Reads a property list (key and element pairs) from the input character stream in a simple
	 * line-oriented format.
	 * @param string $filename The filename of the properties document to load.
	 */
	public function load($filename) {
		$textProperties = file_get_contents($filename);
		$lines = explode( "\n", $textProperties );
		$key = '';
		
		$isWaitingOtherLine = false;
		foreach ( $lines as $i => $line ) {
			$line = str_replace( "\r", '', $line );
			if (empty( $line ) || (!$isWaitingOtherLine && strpos( $line, '#' ) === 0)) {
				continue;
			}
			if (!$isWaitingOtherLine) {
				$key = trim( substr( $line, 0, strpos( $line, '=' ) ) );
				$value = trim( substr( $line, strpos( $line, '=' ) + 1, strlen( $line ) ) );
			} else {
				$value .= $line;
			}
			
			// Check if line ends with a single backslash (\)
			if (strpos( $value, "\\" ) === strlen( $value ) - strlen( "\\" )) {
				$value = substr( $value, 0, strlen( $value ) - 1 ) . "\n";
				$isWaitingOtherLine = true;
			} else {
				$isWaitingOtherLine = false;
			}
			
			$this->setProperty( $key, $value );
			unset( $lines[$i] );
		}
	}

	/**
	 * Write this property list to the given filename or to the output stream if the given filename
	 * is null.
	 */
	public function store($filename = null) {
		$now = new Date();
		$newline = System::lineSeparator();
		$filename = ($fn = $filename) == null ? 'php://output' : (string) $fn;
		
		$h = @fopen( $filename, 'w' );
		fwrite( $h, '# ' . $now->format( 'r' ) . $newline );
		$iter = $this->keySet()->getIterator();
		while ( $iter->hasNext() ) {
			$key = $iter->next();
			$val = $this->get( $key );
			fwrite( $h, $key . '=' . $val . $newline );
		}
		fclose( $h );
	}

	/**
	 * Returns an enumeration of all the keys in this property list, including distinct keys in the
	 * default property list if a key of the same name has not already been found from the main
	 * properties list.
	 * @return \KM\Util\Iterator
	 */
	public function propertyNames() {
		$m = new HashMap();
		$this->enumerate( $m );
		return $m->keySet()->getIterator();
	}

	/**
	 * Returns a set of keys in this property list where the key and its corresponding value are
	 * strings, including distinct keys in the default property list if a key of the same name has
	 * not already been found from the main properties list.
	 * Properties whose key or value is not of type <tt>String</tt> are omitted.
	 * <p>
	 * The returned set is not backed by the <tt>Properties</tt> object. Changes to this
	 * <tt>Properties</tt> are not reflected in the set, or vice versa.
	 * @return \KM\Util\Set
	 */
	public function stringPropertyNames() {
		$m = new HashMap();
		$this->enumerateStringProperties( $m );
		return $m->keySet();
	}

	/**
	 * Enumerates all key/value pairs in the specified map.
	 * @param Map $m
	 */
	private function enumerate(Map $m) {
		/* @var $iter Iterator */
		/* @var $e Map\Entry */
		if ($this->defaults != null) {
			$this->defaults->enumerate( $m );
		}
		$iter = $this->keySet()->getIterator();
		while ( $iter->hasNext() ) {
			$key = $iter->next();
			$m->put( $key, $this->get( $key ) );
		}
	}

	/**
	 * Enumerates all key/value pairs in the specified map and omits the property if the key or
	 * value is not a string.
	 * @param Map $m
	 */
	private function enumerateStringProperties(Map $m) {
		if ($this->defaults != null) {
			$this->defaults->enumerateStringProperties( $m );
		}
		$iter = $this->keySet->getIterator();
		while ( $iter->hasNext() ) {
			$key = $iter->next();
			$value = $this->get( $key );
			if (is_string( $key ) && is_string( $value )) {
				$m->put( $key, $value );
			}
		}
	}
}
?>