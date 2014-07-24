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
namespace KM\Net;

use KM\Lang\Object;
use KM\Net\InetAddress;

/**
 * Inet4AddressImpl Class
 *
 * @author Blair
 */
class Inet6AddressImpl extends Object implements InetAddressImpl
{

    /**
     *
     * @var InetAddress
     */
    private $anyLocalAddress;

    /**
     *
     * @var InetAddress
     */
    private $loopbackAddress;

    public function getLocalHostName()
    {
        return $_SERVER['SERVER_NAME'];
    }

    public function lookupAllHostAddr($hostName)
    {
        return InetAddress::getAllByName($hostName);
    }

    public function getHostByAddr($addr)
    {
        $address = inet_ntop($addr);
        return gethostbyaddr($address);
    }

    public function anyLocalAddress()
    {
        if ($this->anyLocalAddress == null) {
            if (InetAddress::$preferIPv6Address) {
                $this->anyLocalAddress = new Inet6Address();
                $this->anyLocalAddress->holder()->hostName = '::';
            } else {
                $addr = new Inet4AddressImpl();
                $this->anyLocalAddress = $addr->anyLocalAddress();
            }
        }
        return $this->anyLocalAddress;
    }

    public function loopbackAddress()
    {
        if ($this->loopbackAddress == null) {
            if (InetAddress::$preferIPv6Address) {
                $loopback = inet_pton('::1');
                $this->loopbackAddress = new Inet6Address('localhost', $loopback);
            } else {
                $addr = new Inet4AddressImpl();
                $this->loopbackAddress = $addr->loopbackAddress();
            }
        }
        return $this->loopbackAddress;
    }
}
?>