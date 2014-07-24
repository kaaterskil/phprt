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
namespace KM\Time;

use KM\Lang\Object;
use KM\Lang\Comparable;
use KM\Lang\ClassCastException;
use KM\Lang\NullPointerException;
use KM\Util\Date;

/**
 * A date-based amount of time in the ISO-8601 calendar system. An object of
 * this class is considered temporal.
 *
 * @author Blair
 */
class Period extends Object implements Comparable
{

    /**
     * Returns a new Period starting with with the given $startDate and ending
     * at the end of time.
     *
     * @param Date $startDate
     * @return \KM\Time\Period
     */
    public static function startingOn(Date $startDate)
    {
        return new self($startDate, null);
    }

    /**
     * Returns a new Period starting with the beginning of time and ending with
     * the given $endDate.
     *
     * @param Date $endDate
     * @return \KM\Time\Period
     */
    public static function upTo(Date $endDate)
    {
        return new self(Date::BEGINNING_OF_TIME(), $endDate);
    }

    /**
     * Returns a new Date object representing the given number of days added to
     * the given date.
     *
     * @param Date $date
     * @param unknown $days
     */
    public static function addDays(Date $date, $days)
    {
        $oldTimestamp = $date->getTimestamp();
        $newTimestamp = $oldTimestamp + (intval($days) * 86400);
        $newDate = new Date();
        return $newDate->setTimestamp($newTimestamp);
    }

    /**
     * Returns the number of days (as an integer) between two given dates.
     *
     * @param Date $arg1
     * @param Date $arg2
     * @throws NullPointerException
     * @return number
     */
    public static function daysBetween(Date $arg1 = null, Date $arg2 = null)
    {
        if ($arg1 === null || $arg2 === null) {
            throw new NullPointerException();
        }
        $t1 = $arg1->getTimestamp();
        $t2 = $arg2->getTimestamp();
        return intval(($t2 - $t1) / 86400);
    }

    /**
     * Returns true if $arg2 is one day (86400 seconds) later than $arg1.
     *
     * @param Date $arg1
     * @param Date $arg2
     * @return boolean
     */
    public static function isOneDayBefore(Date $arg1 = null, Date $arg2 = null)
    {
        $d = self::daysBetween($arg1, $arg2);
        return ($d == 1);
    }
    
    /* ---------- Instance Variables ---------- */
    
    /**
     *
     * @var Date
     */
    private $startDate;

    /**
     *
     * @var Date
     */
    private $endDate;
    
    /* ---------- Constructor ---------- */
    
    /**
     * Constructs a new Period with the given start date and the given end date.
     * If $startDate is null, the Period will begin at the beginning of time. If
     * $endDate is null, the Period will end at the end of time.
     *
     * @param Date $startDate
     * @param Date $endDate
     */
    public function __construct(Date $startDate = null, Date $endDate = null)
    {
        $this->setStartDate($startDate);
        $this->setEndDate($endDate);
    }
    
    /* ---------- Getter / Setters ---------- */
    
    /**
     * Returns the end date.
     *
     * @return \KM\Util\Date
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Sets the end date.
     *
     * @param Date $endDate
     */
    public function setEndDate(Date $endDate = null)
    {
        if ($endDate == null) {
            $endDate = Date::END_OF_TIME();
        }
        $this->endDate = $endDate;
    }

    /**
     * Returns the start date.
     *
     * @return \KM\Util\Date
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Sets the start date.
     *
     * @param Date $startDate
     */
    public function setStartDate(Date $startDate = null)
    {
        if ($startDate == null) {
            $startDate = Date::BEGINNING_OF_TIME();
        }
        $this->startDate = $startDate;
    }
    
    /* ---------- Instance Methods ---------- */
    
    /**
     * Compares two objects for ordering. Returns a negative integer, zero, or a
     * positive integer as this Period is starts before, equal to, or later than
     * the specified Period.
     *
     * @param Object $o
     * @throws NullPointerException
     * @throws ClassCastException
     * @return number
     * @see \KM\Lang\Comparable::compareTo()
     */
    public function compareTo(Object $o = null)
    {
        /* @var $anotherPeriod Period */
        if ($o == null) {
            throw new NullPointerException();
        }
        if (! $o instanceof $this) {
            throw new ClassCastException();
        }
        $anotherPeriod = $o;
        if ($this->startDate->getTimestamp() > $anotherPeriod->startDate->getTimestamp()) {
            return 1;
        } elseif ($this->startDate->getTimestamp() < $anotherPeriod->startDate->getTimestamp()) {
            return - 1;
        }
        return 0;
    }

    /**
     * Checks if this Period is equal to another Period. Returns true if the
     * specified Period is equal to this instance, or if the start and end dates
     * of the given Period are equal to this instance's start and end dates.
     *
     * @param Object $o The object to check. Null returns false.
     * @return boolean True if this is equal to the other Period.
     * @see \KM\Lang\Object::equals()
     */
    public function equals(Object $o = null)
    {
        /* @var $other Period */
        if ($this === $o) {
            return true;
        }
        if ($o !== null && $o instanceof $this) {
            $other = $o;
            return (($this->startDate->getTimestamp() == $other->startDate->getTimestamp()) &&
                 ($this->endDate->getTimestamp() == $other->endDate->getTimestamp()));
        }
        return false;
    }

    /**
     * Returns true if the given Date object falls within this Period.
     *
     * @param Date $date
     * @return boolean
     */
    public function includes(Date $date = null)
    {
        if ($date === null) {
            return false;
        }
        return (($this->startDate->getTimestamp() <= $date->getTimestamp()) &&
             ($this->endDate->getTimestamp() >= $date->getTimestamp()));
    }

    /**
     * Returns true if the given PEriod falls within this Period.
     *
     * @param Period $other
     * @return boolean
     */
    public function includesPeriod(Period $other)
    {
        return (($this->includes($other->startDate)) && ($this->includes($other->endDate)));
    }

    /**
     * Returns true if the start or end dates of the given Period intersect with
     * this Period.
     *
     * @param Period $other
     * @return boolean
     */
    public function intersects(Period $other)
    {
        return (($this->includes($other->startDate) || $this->includes($other->endDate)) ||
             ($other->includes($this->startDate) || $other->includes($this->endDate)));
    }

    /**
     * Returns true if the date range of this Period is valid, i.e. that the
     * start date is less than or equal to the end date.
     *
     * @return boolean
     */
    public function isValid()
    {
        return ($this->startDate->getTimestamp() <= $this->endDate->getTimestamp());
    }

    /**
     * Returns true if the start and end dates of this Period are equal.
     *
     * @return boolean
     */
    public function isZero()
    {
        return ($this->endDate->getTimestamp() - $this->startDate->getTimestamp() === 0);
    }

    /**
     * Returns a string representation of this Period.
     *
     * @return string
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        $format = 'Period[startDate=%s,endDate=%s]';
        return sprintf($format, $this->startDate->format('Y-m-d H:i:s'), $this->endDate->format('Y-m-d H:i:s'));
    }
}
?>