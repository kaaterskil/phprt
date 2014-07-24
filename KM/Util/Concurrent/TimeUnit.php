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
namespace KM\Util\Concurrent;

use KM\Lang\Enum;

/**
 * A {@code TimeUnit} represents time durations at a given unit of granularity
 * and provides utility methods to convert across units, and to perform timing
 * and delay operations in these units. A {@code TimeUnit} does not maintain
 * time information, but only helps organize and use time representations that
 * may be maintained separately across various contexts. A nanosecond is defined
 * as one thousandth of a microsecond, a microsecond as one thousandth of a
 * millisecond, a millisecond as one thousandth of a second, a minute as sixty
 * seconds, an hour as sixty minutes, and a day as twenty four hours.
 * <p>A {@code TimeUnit} is mainly used to inform time-based methods how a given
 * timing parameter should be interpreted. For example, the following code will
 * timeout in 50 milliseconds if the {@link java.util.concurrent.locks.Lock
 * lock} is not available:
 * <pre> {@code Lock lock = ...; if (lock.tryLock(50L, TimeUnit.MILLISECONDS))
 * ...}</pre>
 * while this code will timeout in 50 seconds: <pre> {@code Lock lock = ...; if
 * (lock.tryLock(50L, TimeUnit.SECONDS)) ...}</pre>
 * Note however, that there is no guarantee that a particular timeout
 * implementation will be able to notice the passage of time at the same
 * granularity as the given {@code TimeUnit}.
 *
 * @author Blair
 */
class TimeUnit extends Enum
{

    const NANOSECONDS = 'nanoseconds';

    const MICROSECONDS = 'microseconds';

    const MILLISECONDS = 'milliseconds';

    const SECONDS = 'seconds';

    const MINUTES = 'minutes';

    const HOURS = 'hours';

    const DAYS = 'days';

    protected static $c0 = 1;

    protected static $c1 = 1000;

    protected static $c2 = 1000000;

    protected static $c3 = 1000000000;

    protected static $c4 = 60000000000;

    protected static $c5 = 3600000000000;

    protected static $c6 = 86400000000000;

    protected static $MAX = PHP_INT_MAX;

    protected static function x($d, $m, $over)
    {
        if ($d > $over) {
            return PHP_INT_MAX;
        }
        if ($d < - $over) {
            return - PHP_INT_MAX;
        }
        return $d * $m;
    }

    public function toNanos($d)
    {
        switch ($this->getName()) {
            case self::NANOSECONDS:
                return $d;
            case self::MICROSECONDS:
                return self::x($d, self::$c1 / self::$c0, max(array(
                    self::$c1,
                    self::$c0
                )));
            case self::MILLISECONDS:
                return self::x($d, self::$c2 / self::$c0, max(array(
                    self::$c2,
                    self::$c0
                )));
            case self::SECONDS:
                return self::x($d, self::$c3 / self::$c0, max(array(
                    self::$c3,
                    self::$c0
                )));
            case self::MINUTES:
                return self::x($d, self::$c4 / self::$c0, max(array(
                    self::$c4,
                    self::$c0
                )));
            case self::HOURS:
                return self::x($d, self::$c5 / self::$c0, max(array(
                    self::$c5,
                    self::$c0
                )));
            case self::DAYS:
                return self::x($d, self::$c6 / self::$c0, max(array(
                    self::$c6,
                    self::$c0
                )));
        }
    }

    public function toMicros($d)
    {
        switch ($this->getName()) {
            case self::NANOSECONDS:
                return $d / (self::$c1 / self::$c0);
            case self::MICROSECONDS:
                return $d;
            case self::MILLISECONDS:
                return self::x($d, self::$c2 / self::$c1, max(array(
                    self::$c2,
                    self::$c1
                )));
            case self::SECONDS:
                return self::x($d, self::$c3 / self::$c1, max(array(
                    self::$c3,
                    self::$c1
                )));
            case self::MINUTES:
                return self::x($d, self::$c4 / self::$c1, max(array(
                    self::$c4,
                    self::$c1
                )));
            case self::HOURS:
                return self::x($d, self::$c5 / self::$c1, max(array(
                    self::$c5,
                    self::$c1
                )));
            case self::DAYS:
                return self::x($d, self::$c6 / self::$c1, max(array(
                    self::$c6,
                    self::$c1
                )));
        }
    }

    public function toMillis($d)
    {
        switch ($this->getName()) {
            case self::NANOSECONDS:
                return $d / (self::$c2 / self::$c0);
            case self::MICROSECONDS:
                return $d / (self::$c2 / self::$c1);
            case self::MILLISECONDS:
                return $d;
            case self::SECONDS:
                return self::x($d, self::$c3 / self::$c2, max(array(
                    self::$c3,
                    self::$c2
                )));
            case self::MINUTES:
                return self::x($d, self::$c4 / self::$c2, max(array(
                    self::$c4,
                    self::$c2
                )));
            case self::HOURS:
                return self::x($d, self::$c5 / self::$c2, max(array(
                    self::$c5,
                    self::$c2
                )));
            case self::DAYS:
                return self::x($d, self::$c6 / self::$c2, max(array(
                    self::$c6,
                    self::$c2
                )));
        }
    }

    public function toSeconds($d)
    {
        switch ($this->getName()) {
            case self::NANOSECONDS:
                return $d / (self::$c3 / self::$c0);
            case self::MICROSECONDS:
                return $d / (self::$c3 / self::$c1);
            case self::MILLISECONDS:
                return $d / (self::$c3 / self::$c2);
            case self::SECONDS:
                return $d;
            case self::MINUTES:
                return self::x($d, self::$c4 / self::$c3, max(array(
                    self::$c4,
                    self::$c3
                )));
            case self::HOURS:
                return self::x($d, self::$c5 / self::$c3, max(array(
                    self::$c5,
                    self::$c3
                )));
            case self::DAYS:
                return self::x($d, self::$c6 / self::$c3, max(array(
                    self::$c6,
                    self::$c3
                )));
        }
    }

    public function toMinutes($d)
    {
        switch ($this->getName()) {
            case self::NANOSECONDS:
                return $d / (self::$c4 / self::$c0);
            case self::MICROSECONDS:
                return $d / (self::$c4 / self::$c1);
            case self::MILLISECONDS:
                return $d / (self::$c4 / self::$c2);
            case self::SECONDS:
                return $d / (self::$c4 / self::$c3);
            case self::MINUTES:
                return $d;
            case self::HOURS:
                return self::x($d, self::$c5 / self::$c4, max(array(
                    self::$c5,
                    self::$c4
                )));
            case self::DAYS:
                return self::x($d, self::$c6 / self::$c4, max(array(
                    self::$c6,
                    self::$c4
                )));
        }
    }

    public function toHours($d)
    {
        switch ($this->getName()) {
            case self::NANOSECONDS:
                return $d / (self::$c5 / self::$c0);
            case self::MICROSECONDS:
                return $d / (self::$c5 / self::$c1);
            case self::MILLISECONDS:
                return $d / (self::$c5 / self::$c2);
            case self::SECONDS:
                return $d / (self::$c5 / self::$c3);
            case self::MINUTES:
                return $d / (self::$c5 / self::$c4);
            case self::HOURS:
                return $d;
            case self::DAYS:
                return self::x($d, self::$c6 / self::$c5, max(array(
                    self::$c6,
                    self::$c5
                )));
        }
    }

    public function toDays($d)
    {
        switch ($this->getName()) {
            case self::NANOSECONDS:
                return $d / (self::$c6 / self::$c0);
            case self::MICROSECONDS:
                return $d / (self::$c6 / self::$c1);
            case self::MILLISECONDS:
                return $d / (self::$c6 / self::$c2);
            case self::SECONDS:
                return $d / (self::$c6 / self::$c3);
            case self::MINUTES:
                return $d / (self::$c6 / self::$c4);
            case self::HOURS:
                return $d / (self::$c6 / self::$c5);
            case self::DAYS:
                return $d;
        }
    }
}
?>