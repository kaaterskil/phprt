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

use KM\Time\Period;
use KM\Util\Date;
use KM\Util\UUID;

/**
 * Interface that singly temporal objects should implement. This adds start and
 * end date properties to the implementing class as well as methods to test
 * equality of the data only (without the time period) and clone the object
 * without persistent identity (primary key field).
 *
 * @author Blair
 */
interface Temporal
{

    /**
     * Returns the surrogate identifier (optional).
     *
     * @return \KM\Util\UUID string int
     */
    public function getSurrogateId();

    /**
     * Returns the effective end date for this temporal object.
     *
     * @return \KM\Util\Date
     */
    public function getEffectiveEndDate();

    /**
     * Sets the effective end date for this temporal object.
     *
     * @param \KM\Util\Date $effectiveEndDate
     */
    public function setEffectiveEndDate(Date $effectiveEndDate = null);

    /**
     * Returns the effective start date for this temporal object.
     *
     * @return \KM\Util\Date
     */
    public function getEffectiveStartDate();

    /**
     * Sets the effective start date for this temporal object.
     *
     * @param \KM\Util\Date $effectiveStartDate
     */
    public function setEffectiveStartDate(Date $effectiveStartDate = null);

    /**
     * Returns the effective period for this temporal object.
     */
    public function getEffectivePeriod();

    /**
     * Sets the effective period for this temporal object.
     *
     * @param \KM\Time\Period $period
     */
    public function setEffectivePeriod(Period $period);

    /**
     * Returns true of this temporal object is effective on the specified date.
     * More specifically, the method returns true if the specified date is
     * greater than or equal to the effective start date and less than or equal
     * to the effective end date.
     *
     * @param \KM\Util\Date $effectiveDate
     * @return boolean
     */
    public function isEffective(Date $effectiveDate);

    /**
     * Returns a new temporal object representing the same value as this object
     * but with the specified validity.
     *
     * @param \KM\Time\Period $newPeriod
     * @return Temporal
     */
    public function cloneWithNewEffectivePeriod(Period $newPeriod);
}
?>