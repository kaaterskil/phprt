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

use KM\Lang\ClassCastException;
use KM\Lang\Comparable;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Text\FormatException;

/**
 * A class that represents an immutable universally unique identifier (UUID).
 *
 * @author Blair
 */
final class UUID extends Object implements Comparable
{

    /**
     * Generates a UUID string.
     *
     * @return string
     */
    public static function generateUUID()
    {
        $sec = time();
        $dec = microtime(true) % 1000000;
        $secHex = substr(dechex('000000' . $sec), 0, 6);
        $decHex = substr(dechex('00000' . $dec), 0, 5);
        $uuid = sprintf('%s%s-%s-%s-%s-%s%s', $decHex,
            dechex(rand(0x100, 0xfff)), dechex(rand(0x1000, 0xffff)),
            dechex(rand(0x1000, 0xffff)), dechex(rand(0x1000, 0xffff)), $secHex,
            dechex(rand(0x100000, 0xffffff)));
        return $uuid;
    }

    /**
     * Initializes a new UUID instance.
     *
     * @return \KM\Util\UUID
     */
    public static function instance()
    {
        return new self();
    }

    /**
     * Converts the string representation of a UUID to the equivalent UUID
     * structure.
     *
     * @param string $string
     * @return \KM\Util\UUID
     */
    public static function parse($string)
    {
        $string = self::formatUUID($string);
        return new self($string);
    }

    /**
     * Formats the given UUID string.
     *
     * @param string $string
     * @throws NullPointerException
     * @throws FormatException
     * @return string
     */
    private static function formatUUID($string = null)
    {
        if ($string === null) {
            throw new NullPointerException();
        }
        
        $patternD = '/^[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}$/';
        $patternB = '/^\{([a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12})\}$/';
        $patternP = '/^\(([a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12})\)$/';
        if (strlen($string) == 32) {
            $string = sprintf('%s-%s-%s-%s-%s', substr($string, 0, 8),
                substr($string, 8, 4), substr($string, 12, 4),
                substr($string, 16, 4), substr($string, 20));
        } elseif (preg_match($patternB, $string, $matchesB)) {
            $string = $matchesB[1];
        } elseif (preg_match($patternP, $string, $matchesP)) {
            $string = $matchesP[1];
        } elseif (!preg_match($patternD, $string)) {
            throw new FormatException();
        }
        return $string;
    }

    /**
     *
     * @var string
     */
    public $uuid;

    /**
     * Constructs a UUID with the optional given value.
     *
     * @param string $uuid The optional specified value of the UUID.
     * @see http://forums.sugarcrm.com/f6/developed-auto-generate-ids-guids-within-mysql-2895/
     */
    public function __construct($uuid = null)
    {
        if ($uuid === null) {
            $this->uuid = self::generateUUID();
        } else {
            $this->uuid = self::formatUUID($uuid);
        }
    }

    /**
     * Compares this instance to a specified object and returns an indication of
     * their relative values.
     *
     * @param Object $value
     * @throws NullPointerException
     * @throws ClassCastException
     * @return int
     * @see \KM\Lang\Comparable::compareTo()
     */
    public function compareTo(Object $value = null)
    {
        /* @var $other UUID */
        if ($value === null) {
            throw new NullPointerException();
        }
        if (!$value instanceof $this) {
            throw new ClassCastException();
        }
        $other = $value;
        if ($other->uuid == $this->uuid) {
            return 0;
        }
        return $this->uuid - $other->uuid ? 1 : -1;
    }

    /**
     * Returns a string representation of the value of this instance.
     *
     * @return string
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        return $this->uuid;
    }
}
?>