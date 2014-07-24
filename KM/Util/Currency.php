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

use KM\IO\BufferedInputStream;
use KM\IO\DataInputStream;
use KM\IO\File;
use KM\IO\FileInputStream;
use KM\IO\IOException;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\IllegalArgumentException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\System;
use KM\Util\HashMap;
use KM\Util\HashSet;
use KM\Util\Map;
use KM\Util\Set;

/**
 * Represents a currency.
 * Currencies are identified by their ISO 4217 currency codes. The class is designed so that there
 * is never more than one Currency instance for any given currency. Therefore, there is no public
 * constructor. You obtain a currency instance by using the getInstance() method.
 *
 * Runtime currencies are supplied by calling the static init() method on bootstrap with a given
 * configuration array. Each configuration array element must be formatted as follows:
 * <code>
 * $currencyCode => array(currency name, $numericCode, $defaultFractionDigits);
 * </code>
 * where $currencyCode is the three-character ISO 4217 currency code, $numericCode is the ISO
 * 4217 numeric code, and $defaultFractionDigits is the number of decimal places for fractional
 * units, i.e. cents in US Dollars.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Currency extends Object implements Serializable {
	
	/**
	 * The ISO 4217 currency code of this currency.
	 * @var string
	 */
	private $currencyCode;
	
	/**
	 * The number of fractional digits for this currency.
	 * @Transient
	 * @var int
	 */
	private $defaultFractionDigits;
	
	/**
	 * The ISO 4217 numeric code of this currency.
	 * @Transient
	 * @var int
	 */
	private $numericCode;
	
	/**
	 * A Map of currency codes and their instances.
	 * @var Map
	 */
	private static $instances;
	
	/**
	 * A set of available currency instances.
	 * @var Set
	 */
	private static $available;
	
	// handy constants - must match definitions in GenerateCurrencyData
	// magic number
	private static $MAGIC_NUMBER = 0x43757244;
	// number of characters from A to Z
	private static $A_TO_Z = 26;
	// entry for invalid country codes
	private static $INVALID_COUNTRY_ENTRY = 0x007F;
	// entry for countries without currency
	private static $COUNTRY_WITHOUT_CURRENCY_ENTRY = 0x0080;
	// mask for simple case country entries
	private static $SIMPLE_CASE_COUNTRY_MASK = 0x0000;
	// mask for simple case country entry final character
	private static $SIMPLE_CASE_COUNTRY_FINAL_CHAR_MASK = 0x001F;
	// mask for simple case country entry default currency digits
	private static $SIMPLE_CASE_COUNTRY_DEFAULT_DIGITS_MASK = 0x0060;
	// shift count for simple case country entry default currency digits
	private static $SIMPLE_CASE_COUNTRY_DEFAULT_DIGITS_SHIFT = 5;
	// mask for special case country entries
	private static $SPECIAL_CASE_COUNTRY_MASK = 0x0080;
	// mask for special case country index
	private static $SPECIAL_CASE_COUNTRY_INDEX_MASK = 0x001F;
	// delta from entry index component in main table to index into special case tables
	private static $SPECIAL_CASE_COUNTRY_INDEX_DELTA = 1;
	// mask for distinguishing simple and special case countries
	private static $COUNTRY_TYPE_MASK;
	// mask for the numeric code of the currency
	private static $NUMERIC_CODE_MASK = 0x0003FF00;
	// shift count for the numeric code of the currency
	private static $NUMERIC_CODE_SHIFT = 8;
	// Currency data format version
	private static $VALID_FORMAT_VERSION = 1;
	
	// Class data: currency data obtained from currency.data file.
	// Purpose:
	// - determine valid country codes
	// - determine valid currency codes
	// - map country codes to currency codes
	// - obtain default fraction digits for currency codes
	//
	// sc = special case; dfd = default fraction digits
	// Simple countries are those where the country code is a prefix of the
	// currency code, and there are no known plans to change the currency.
	//
	// table formats:
	// - mainTable:
	// - maps country code to 32-bit int
	// - 26*26 entries, corresponding to [A-Z]*[A-Z]
	// - \u007F -> not valid country
	// - bits 18-31: unused
	// - bits 8-17: numeric code (0 to 1023)
	// - bit 7: 1 - special case, bits 0-4 indicate which one
	// 0 - simple country, bits 0-4 indicate final char of currency code
	// - bits 5-6: fraction digits for simple countries, 0 for special cases
	// - bits 0-4: final char for currency code for simple country, or ID of special case
	// - special case IDs:
	// - 0: country has no currency
	// - other: index into sc* arrays + 1
	// - scCutOverTimes: cut-over time in millis as returned by
	// System.currentTimeMillis for special case countries that are changing
	// currencies; Long.MAX_VALUE for countries that are not changing currencies
	// - scOldCurrencies: old currencies for special case countries
	// - scNewCurrencies: new currencies for special case countries that are
	// changing currencies; null for others
	// - scOldCurrenciesDFD: default fraction digits for old currencies
	// - scNewCurrenciesDFD: default fraction digits for new currencies, 0 for
	// countries that are not changing currencies
	// - otherCurrencies: concatenation of all currency codes that are not the
	// main currency of a simple country, separated by "-"
	// - otherCurrenciesDFD: decimal format digits for currencies in otherCurrencies, same order
	public static $formatVersion;
	public static $dataVersion;
	public static $mainTable;
	public static $scCutOverTimes;
	public static $scOldCurrencies;
	public static $scNewCurrencies;
	public static $scOldCurrenciesDFD;
	public static $scNewCurrenciesDFD;
	public static $scOldCurrenciesNumericCode;
	public static $scNewCurrenciesNumericCode;
	public static $otherCurrencies;
	public static $otherCurrenciesDFD;
	public static $otherCurrenciesNumericCode;

	/**
	 * Static constructor
	 */
	public static function clinit() {
		self::$A_TO_Z = ('Z' - 'A') + 1;
		self::$COUNTRY_TYPE_MASK = self::$SIMPLE_CASE_COUNTRY_MASK | self::$SPECIAL_CASE_COUNTRY_MASK;
		self::$instances = new HashMap( '<string, \KM\Util\Currency>' );
		
		$homeDir = System::getProperty( 'php.home' );
		$dataFile = $homeDir . File::$separator . 'lib' . File::$separator . 'currency.data';
		try {
			$dis = new DataInputStream( new BufferedInputStream( new FileInputStream( $dataFile ) ) );
			if ($dis->readInt() != self::$MAGIC_NUMBER) {
				trigger_error( 'currency data is possibly corrupted' );
			}
			self::$formatVersion = $dis->readInt();
			if (self::$formatVersion != self::$VALID_FORMAT_VERSION) {
				trigger_error( 'currency data format is incorrect' );
			}
			self::$dataVersion = $dis->readInt();
			self::$mainTable = self::readIntArray( $dis, (self::$A_TO_Z * self::$A_TO_Z) );
			
			$scCount = $dis->readInt();
			self::$scCutOverTimes = self::readLongArray( $dis, $scCount );
			self::$scOldCurrencies = self::readStringArray( $dis, $scCount );
			self::$scNewCurrencies = self::readStringArray( $dis, $scCount );
			self::$scOldCurrenciesDFD = self::readIntArray( $dis, $scCount );
			self::$scNewCurrenciesDFD = self::readIntArray( $dis, $scCount );
			self::$scOldCurrenciesNumericCode = self::readIntArray( $dis, $scCount );
			self::$scNewCurrenciesNumericCode = self::readIntArray( $dis, $scCount );
			
			$ocCount = $dis->readInt();
			self::$otherCurrencies = $dis->readUTF();
			self::$otherCurrenciesDFD = self::readIntArray( $dis, $ocCount );
			self::$otherCurrenciesNumericCode = self::readIntArray( $dis, $ocCount );
		} catch ( IOException $e ) {
			trigger_error( 'I/O error reading currency.data' );
		}
	}

	/**
	 * Constructs a currency instance.
	 * The constructor is private so that we can ensure that there is never more than one instance
	 * for a given currency.
	 * @param string $currencyCode The ISO 4217 code of the currency.
	 * @param int $defaultFractionDigits
	 * @param int $numericCode
	 */
	private function __construct($currencyCode, $defaultFractionDigits, $numericCode) {
		$this->currencyCode = (string) $currencyCode;
		$this->defaultFractionDigits = (int) $defaultFractionDigits;
		$this->numericCode = (int) $numericCode;
	}

	/**
	 * Returns the singleton currency instance for the given currency code.
	 * @param string $currencyCode The ISO 4217 code of the currency.
	 * @return \KM\Util\Currency THe currency instance for the given currency code.
	 * @throws NullPointerException if <code>currencyCode</code> is null.
	 * @throws IllegalArgumentException if <code>currencyCode</code> is not supported ISO 4217 code.
	 */
	public static function getInstance($currencyCode) {
		return self::getInstance0( $currencyCode, PHP_INT_MAX, 0 );
	}

	private static function getInstance0($currencyCode, $defaultFractionDigits, $numericCode) {
		/* @var $instance Currency */
		
		// Try to look up the currency code in the instances table, This does the null pointer check
		// as a side effect. If there already is an entry, the currencyCode must be valid.
		$instance = self::$instances->get( $currencyCode );
		if ($instance != null) {
			return $instance;
		}
		
		if ($defaultFractionDigits == PHP_INT_MAX) {
			// Currency code not internally generated. Need to verify first that a currency code
			// must have 3 characters and exist in the main table.
			if (strlen( $currencyCode ) != 3) {
				throw new IllegalArgumentException();
			}
			$char1 = $currencyCode[0];
			$char2 = $currencyCode[1];
			$tableEntry = self::getMainTableEntry( $char1, $char2 );
			if((($tableEntry & self::$COUNTRY_TYPE_MASK) == self::$SIMPLE_CASE_COUNTRY_MASK)
					&& ($tableEntry != self::$INVALID_COUNTRY_ENTRY)
					&& (ord($currencyCode[2]) - ord('A') == ($tableEntry & self::$SIMPLE_CASE_COUNTRY_FINAL_CHAR_MASK))) {
				$defaultFractionDigits = (($tableEntry & self::$SIMPLE_CASE_COUNTRY_DEFAULT_DIGITS_MASK) >> self::$SIMPLE_CASE_COUNTRY_DEFAULT_DIGITS_SHIFT);
				$numericCode = (($tableEntry & self::$NUMERIC_CODE_MASK) >> self::$NUMERIC_CODE_SHIFT);
			} else {
				if($currencyCode[2] == '-') {
					throw new IllegalArgumentException();
				}
				$index = strpos(self::$otherCurrencies, $currencyCode);
				if($index === false) {
					throw new IllegalArgumentException();
				}
				$defaultFractionDigits = self::$otherCurrenciesDFD[$index / 4];
				$numericCode = self::$otherCurrenciesNumericCode[$index / 4];
			}
		}
		
		$currencyVal = new Currency( $currencyCode, $defaultFractionDigits, $numericCode );
		$instance = self::$instances->put( $currencyCode, $currencyVal );
		return ($instance != null) ? $instance : $currencyVal;
	}

	/**
	 * Gets the set of available currencies.
	 * The returned set of currencies contains all of the available currencies, which may include
	 * currencies that represent obsolete ISO 4217 codes. The set can be modified without affecting
	 * the available currencies in the runtime.
	 * @return \KM\Util\Set The set of available currencies.
	 */
	public static function getAvailableCurrencies() {
		if (self::$available === null) {
			self::$available = new HashSet( '\KM\Util\Currency' );
			
			// Add simple currencies first
			for($c1 = 'A'; $c1 <= 'Z'; $c1++) {
				for($c2 = 'A'; $c2 <= 'Z' ; $c2++) {
					$tableEntry = self::getMainTableEntry( $c1, $c2 );
					if (($tableEntry & self::$COUNTRY_TYPE_MASK) == self::$SIMPLE_CASE_COUNTRY_MASK &&
						 $tableEntry != self::$INVALID_COUNTRY_ENTRY) {
						$finalChar = chr(
							(($tableEntry & self::$SIMPLE_CASE_COUNTRY_FINAL_CHAR_MASK) + 'A') );
						$defaultFractionDigits = ($tableEntry & self::$SIMPLE_CASE_COUNTRY_DEFAULT_DIGITS_MASK) >>
							 self::$SIMPLE_CASE_COUNTRY_DEFAULT_DIGITS_SHIFT;
						$numericCode = ($tableEntry & self::$NUMERIC_CODE_MASK) >> self::$NUMERIC_CODE_SHIFT;
						$sb = $c1 . $c2 . $finalChar;
						self::$available->add( self::getInstance0( $sb, $defaultFractionDigits,
							$numericCode ) );
					}
				}
			}
			// Now add other currencies
			$st = explode( '-', self::$otherCurrencies );
			foreach ( $st as $tok ) {
				self::$available->add( self::getInstance( $tok ) );
			}
		}
		return new HashSet('\KM\Util\Currency', self::$available);
	}

	/**
	 * Returns the ISO 4217 currency code of this currency.
	 * @return string
	 */
	public function getCurrencyCode() {
		return $this->currencyCode;
	}

	/**
	 * Gets the default number of fraction digits used with this currency.
	 * For example, the default number of fraction digits for the Euro is 2, while for the Japanese
	 * Yen it's 0. In the case of pseudo-currencies, such as IMF Special Drawing Rights, -1 is
	 * returned.
	 * @return int The default number of fraction digits used with this currency.
	 */
	public function getDefaultFractionDigits() {
		return $this->defaultFractionDigits;
	}

	/**
	 * Returns the ISO 4217 numeric code of this currency.
	 * @return int The ISO 4217 numeric code of this currency.
	 */
	public function getNumericCode() {
		return $this->numericCode;
	}

	/**
	 * Returns a string representation of this currency.
	 * @return string
	 * @see \KM\Lang\Object::__toString()
	 */
	public function __toString() {
		return $this->currencyCode;
	}

	/**
	 * Gets the main table entry for the country whose country code consists of char1 and char2.
	 * @param string $char1
	 * @param string $char2
	 * @throws IllegalArgumentException
	 * @return int
	 */
	private static function getMainTableEntry($char1, $char2) {
		if ($char1 < 'A' || $char1 > 'Z' || $char2 < 'A' || $char2 > 'Z') {
			throw new IllegalArgumentException();
		}
		return self::$mainTable[($char1 - 'A') * self::$A_TO_Z + ($char2 - 'A')];
	}

	private static function readIntArray(DataInputStream $dis, $count) {
		$ret = array_fill( 0, $count, null );
		for($i = 0; $i < $count; $i++) {
			$ret[$i] = $dis->readInt();
		}
		return $ret;
	}

	private static function readLongArray(DataInputStream $dis, $count) {
		$ret = array_fill( 0, $count, null );
		for($i = 0; $i < $count; $i++) {
			$ret[$i] = $dis->readLong();
		}
		return $ret;
	}

	private static function readStringArray(DataInputStream $dis, $count) {
		$ret = array_fill( 0, $count, null );
		for($i = 0; $i < $count; $i++) {
			$ret[$i] = $dis->readUTF();
		}
		return $ret;
	}
}
?>