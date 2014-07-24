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
namespace KM\Net\InetAddress;

use KM\Lang\Object;

/**
 * InetAddressHolder Class
 *
 * @author Blair
 */
class InetAddressHolder extends Object
{

    /**
     * The hostname
     *
     * @var string
     */
    public $hostName;

    /**
     * Holds a 32-bit IPv4 address
     *
     * @var int
     */
    public $address;

    /**
     * Specifies the address family type, for instance '1' for IPv4 addresses
     * and '2' for IPv6 addresses.
     *
     * @var int
     */
    public $family;

    public function __construct($hostName = null, $address = null, $family = null)
    {
        if ($hostName != null && $address != null && $family != null) {
            $this->hostName = (string) $hostName;
            $this->address = (int) $address;
            $this->family = (int) $family;
        }
    }

    public function init($hostName, $family)
    {
        $this->hostName = (string) $hostName;
        if ($family != - 1) {
            $this->family = (int) $family;
        }
    }

    public function getHostName()
    {
        return $this->hostName;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getFamily()
    {
        return $this->family;
    }
}
?>