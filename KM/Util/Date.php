<?php
/**
 * Copyright (c) 2009-2014 Kaaterskil Management, LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace KM\Util;

use KM\IO\IOException;
use KM\IO\Serializable;
use KM\IO\Transient;
use KM\Lang\ClassCastException;
use KM\Lang\ClassNotFoundException;
use KM\Lang\Comparable;
use KM\Lang\IllegalArgumentException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;

/**
 * A wrapper class around the PHP representation of date and time.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Date extends Object implements \DateTimeInterface, Comparable,
	Serializable {

	/**
	 * Returns a Date object set to the beginning of this Unix epoch.
	 *
	 * @return \KM\Util\Date
	 */
	public static function BEGINNING_OF_TIME() {
		$bot = new self();
		$bot->setTimestamp( 0 );
		return $bot;
	}

	/**
	 * Returns a Date object set to the maximum PHP integer value.
	 *
	 * @return \KM\Util\Date
	 */
	public static function END_OF_TIME() {
		$eot = new self();
		$eot->setTimestamp( PHP_INT_MAX );
		return $eot;
	}

	/**
	 * Returns a Date object set to now, including hour, minute and seconds.
	 *
	 * @return \KM\Util\Date
	 */
	public static function NOW() {
		$now = new self();
		$now->setTimezone( new \DateTimeZone( date_default_timezone_get() ) );
		return $now;
	}

	/**
	 * Returns a Date object set to today, without the hour, minute or second.
	 *
	 * @return \KM\Util\Date
	 */
	public static function TODAY() {
		$today = new self();
		$today->setTime( 0, 0, 0 );
		$today->setTimezone( new \DateTimeZone( date_default_timezone_get() ) );
		return $today;
	}

	/**
	 * Returns new DateTime object formatted according to the specified format.
	 *
	 * @param string $format The format that the passed in string should be in.
	 * @param string $time String representing the time.
	 * @param \DateTimeZone $timeZone A DateTimeZone object representing the
	 *        desired time zone. If timeZone is omitted and time contains no
	 *        time zone, the current time zone will be used.
	 * @return \KM\Util\Date Returns a new Date instance or null on failure.
	 */
	public static function createFromFormat($format, $time,
		DateTimeZone $timeZone = null) {
		$dt = date_create_from_format( $format, $time, $timeZone );
		if ($dt === false) {
			return null;
		}
		$date = new self();
		$date->setTimestamp( $dt->getTimestamp() );
		return $date;
	}

	/**
	 * Returns an array of warnings and errors found while parsing a date/time
	 * string.
	 *
	 * @return array An array containing info about warnings and errors.
	 */
	public static function getLastErrors() {
		return date_get_last_errors();
	}
	
	/**
	 * The backing date object.
	 * @Transient
	 *
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * Constructs a new Date with the given <code>time</code> and
	 * <code>timeZone</code>.
	 * If <code>time</code> is not given or null, the current time will be set.
	 * If <code>timeZone</code> is null or not set, the current local time zone
	 * will be set.
	 *
	 * @param string $time String representing the time.
	 * @param \DateTimeZone $timeZone
	 */
	public function __construct($time = 'now', $timeZone = null) {
		$this->date = new \DateTime( $time, $timeZone );
	}
	
	/* ---------- DateTime wrapper methods ---------- */
	
	/**
	 * Resets the current date of the Date object to a different date.
	 *
	 * @param int $year Year of the date
	 * @param int $month Month of the date.
	 * @param int $day Day of the date.
	 * @throws IllegalArgumentException on failure.
	 */
	public function setDate($year, $month, $day) {
		$this->date->setDate( $year, $month, $day );
		if ($success === false) {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * Set a date according to the ISO 8601 standard - using weeks and day
	 * offsets rather than
	 * specific dates.
	 *
	 * @param int $year Year of the date.
	 * @param int $week Week of the date.
	 * @param int $day Offset from the first day of the week.
	 * @throws IllegalArgumentException on failure.
	 */
	public function setISODate($year, $week, $day = 1) {
		$success = $this->date->setISODate( $year, $week, $day );
		if ($success === false) {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * Resets the current time of the Date object to a different time.
	 *
	 * @param int $hour Hour of the time.
	 * @param int $minute Minute of the time.
	 * @param int $second Second of the time.
	 * @throws IllegalArgumentException on failure.
	 */
	public function setTime($hour, $minute, $second = 0) {
		$success = $this->date->setTime( $hour, $minute, $second );
		if ($success === false) {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * Returns the Unix timestamp representing the date.
	 *
	 * @return int The Unix timestamp representing the date.
	 */
	public function getTimestamp() {
		return $this->date->getTimestamp();
	}

	/**
	 * Sets the date and time based on an Unix timestamp.
	 *
	 * @param int $unixTimestamp Unix timestamp representing the date.
	 * @throws IllegalArgumentException on failure.
	 */
	public function setTimestamp($unixTimestamp) {
		$success = $this->date->setTimestamp( $unixTimestamp );
		if ($success === false) {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * Returns the time zone relative to this Date instance
	 *
	 * @return \DateTimeZone
	 */
	public function getTimeZone() {
		return $this->date->getTimezone();
	}

	/**
	 * Sets a new time zone for the Date object.
	 *
	 * @param \DateTimeZone $timeZone A <code>DateTimeZone</code> object
	 *        representing the desired time zone.
	 * @throws IllegalArgumentException on failure.
	 */
	public function setTimeZone(\DateTimeZone $timeZone) {
		$success = $this->date->setTimezone( $timeZone );
		if ($success === false) {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * Returns the time zone offset.
	 *
	 * @throws IllegalArgumentException on failure.
	 * @return int The time zone offset in seconds from UTC
	 */
	public function getOffset() {
		$result = $this->date->getOffset();
		if ($result === false) {
			throw new IllegalArgumentException();
		}
		return $result;
	}

	/**
	 * Adds an amount of days, months, years, hours, minutes and seconds to this
	 * date instance.
	 *
	 * @param \DateInterval $interval
	 * @throws IllegalArgumentException on failure.
	 */
	public function add(\DateInterval $interval) {
		$success = $this->date->add( $interval );
		if ($success === false) {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * Alter the timestamp of a Date object by incrementing or decrementing in a
	 * format accepted by <code>strtotime</code>.
	 *
	 * @param string $modify A date/time string.
	 * @throws IllegalArgumentException on failure.
	 */
	public function modify($modify) {
		$success = $this->date->modify( $modify );
		if ($success === false) {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * Subtracts an amount of days, months, years, hours, minutes and seconds
	 * from this Date object.
	 *
	 * @param \DateInterval $interval
	 * @throws IllegalArgumentException on failure.
	 */
	public function sub(\DateInterval $interval) {
		$success = $this->date->sub( $interval );
		if ($success === false) {
			throw new IllegalArgumentException();
		}
	}

	/**
	 * Returns the difference between this Date object and the given Date object
	 *
	 * @param Date $dt2 The date object to compare to.
	 * @param boolean $absolute Whether the interval should be forced to be
	 *        positive.
	 * @throws IllegalArgumentException on failure.
	 * @return \DateInterval The <code>DateInterval</code> object representing
	 *         the difference between the two dates.
	 */
	public function diff(Date $dt2, $absolute = false) {
		$obj = $dt2->date;
		$result = $this->date->diff( $obj, $absolute );
		if ($result === false) {
			throw new IllegalArgumentException();
		}
		return $result;
	}

	/**
	 * Returns date formatted according to given format
	 *
	 * @param string $format Format accepted by the PHP <code>date()</code>
	 *        function.
	 * @throws IllegalArgumentException on failure.
	 * @return string The date formatted according to the given format.
	 */
	public function format($format) {
		$result = $this->date->format( $format );
		if ($result === false) {
			throw new IllegalArgumentException();
		}
		return $result;
	}

	public function hashCode() {
		return $this->getTimestamp();
	}

	/**
	 * Returns a string representation of this Date object in the format YmdHis,
	 * which is acceptable for insertion by MySQL.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->format( 'Y-m-d H:i:s' );
	}
	
	/* ---------- Comparison methods ---------- */
	
	/**
	 * Compares two Dates for quality.
	 *
	 * The result is true if and only if the argument is not null and is a Date
	 * object that represents the same point in time, to the second, as this
	 * object. Thus, two Date objects are equal if and only if the
	 * getTimestamp() method returns the same value for both.
	 *
	 * @param Date $obj
	 * @return boolean
	 */
	public function equals($obj) {
		return (($obj instanceof Date) &&
			 ($this->getTimestamp() == $obj->getTimestamp()));
	}

	/**
	 * Returns true if this Date object is earlier than the given Date object.
	 *
	 * @param Date $anotherDate
	 * @return boolean
	 */
	public function isEarlierThan(Date $anotherDate) {
		return ($this->getTimestamp() < $anotherDate->getTimestamp());
	}

	/**
	 * Returns true if this Date object is earlier than or equal to the given
	 * Date object.
	 *
	 * @param Date $anotherDate
	 * @return boolean
	 */
	public function isEarlierOrEquals(Date $anotherDate) {
		return ($this->getTimestamp() <= $anotherDate->getTimestamp());
	}

	/**
	 * Returns true if this Date object is later than the given Date object.
	 *
	 * @param Date $anotherDate
	 * @return boolean
	 */
	public function isLaterThan(Date $anotherDate) {
		return ($this->getTimestamp() > $anotherDate->getTimestamp());
	}

	/**
	 * Returns true if this Date object is later than or equal to the given Date
	 * object.
	 *
	 * @param Date $anotherDate
	 * @return boolean
	 */
	public function isLaterOrEquals(Date $anotherDate) {
		return ($this->getTimestamp() >= $anotherDate->getTimestamp());
	}

	/**
	 * Compares two date objects for ordering.
	 *
	 * @param Object $val The date object to compare.
	 * @throws NullPointerException if the given value is null.
	 * @throws ClassCastException if the given value is not a Date instance.
	 * @return int The value 0 if the given Date is equal to this Date; a value
	 *         less than 0 if this Date is before the given Date; and a value
	 *         greater than 0 is this Date is after the given Date.
	 * @see \KM\Lang\Comparable::compareTo()
	 */
	public function compareTo(Object $val = null) {
		/* @var $that Date */
		if ($val === null) {
			throw new NullPointerException();
		}
		if (!$val instanceof Date) {
			throw new ClassCastException();
		}
		$that = $val;
		$ts1 = $this->getTimestamp();
		$ts2 = $that->getTimestamp();
		return ($ts1 < $ts2) ? -1 : ($ts1 == $ts2) ? 0 : 1;
	}
	
	/* ---------- Serialization Methods ---------- */
	
	/**
	 * Saves this Date instance to a stream (i.e.
	 * serializes it).
	 *
	 * @param \KM\IO\ObjectOutputStream $s
	 * @throws IOException if an I/O error occurs
	 */
	private function writeObject(\KM\IO\ObjectOutputStream $s) {
		// Write out any serialization
		$s->defaultWriteObject();
		
		// Write out timestamp
		$s->writeInt( $this->getTimestamp() );
	}

	/**
	 * Reconstitutes this date instance from a stream (i.e.
	 * deserializes it).
	 *
	 * @param \KM\IO\ObjectInputStream $s
	 * @throws IOException if an I/O error occurs
	 * @throws ClassNotFoundException
	 */
	private function readObject(\KM\IO\ObjectInputStream $s) {
		// Read in any serialization
		$s->defaultReadObject();
		
		// Read in timestamp
		$this->setTimestamp( $s->readInt() );
	}
}
?>