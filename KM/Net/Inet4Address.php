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

use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;

/**
 * Inet4Address Class
 *
 * @author Blair
 */
final class Inet4Address extends InetAddress
{

    const INADDRSZ = 4;

    public function __construct($hostName = null, $addr = null)
    {
        parent::__construct();
        if ($hostName == null && $addr == null) {
            $this->holder->hostName = null;
            $this->holder->address = 0;
            $this->holder->family = self::IPv4;
        } else {
            $this->holder->hostName = $hostName;
            $this->holder->family = self::IPv4;
            if ($addr != null) {
                if (is_string($addr) && strlen($addr) == self::INADDRSZ) {
                    $val = unpack('N', $addr);
                    $this->holder->address = (int) $val[1];
                } elseif (is_int($addr)) {
                    $this->holder->address = $addr;
                }
            }
        }
    }

    public function isMulticastAddress()
    {
        return (($this->holder()->getAddress() & 0xf0000000) == 0xe0000000);
    }

    public function isAnyLocalAddress()
    {
        return $this->holder->getAddress() == 0;
    }

    public function isLoopbackAddress()
    {
        $byteAddr = $this->holder->getAddress();
        return (($byteAddr >> 24) & 0xff) == 127;
    }

    public function isLinkLocalAddress()
    {
        // link-local unicast in IPv4 (169.254.0.0/16) defined in "Documenting
        // Special Use IPv4
        // Address Blocks that have been Registered with IANA" by Bill Manning
        // draft-manning-dsua-06.txt
        $byteAddr = $this->holder->getAddress();
        return ((($byteAddr >> 24) & 0xff) == 169) && ((($byteAddr >> 16) & 0xff) == 254);
    }

    public function isSiteLocalAddress()
    {
        // refer to RFC 1918
        // 10/8 prefix
        // 172.16/12 prefix
        // 192.168/16 prefix
        $address = $this->holder->getAddress();
        return ((($address >> 24) & 0xff) == 10) ||
             (((($address >> 24) & 0xff) == 172) && ((($address >> 16) & 0xff) == 16)) ||
             (((($address >> 24) & 0xff) == 192) && ((($address >> 16) & 0xff) == 168));
    }

    public function isMCGlobal()
    {
        // 224.0.1.0 to 238.255.255.255
        $address = $this->holder->getAddress();
        return ((($address >> 24) & 0xff) >= 224 && (($address >> 24) & 0xff) <= 238) && ! ((($address >> 24) & 0xff) ==
             224 && (($address >> 16) & 0xff) == 0 && (($address >> 8) & 0xff) == 0);
    }

    public function isMCNodeLocal()
    {
        return false;
    }

    public function isMCLinkLocal()
    {
        // 224.0.0/24 prefix and ttl == 1
        $address = $this->holder->getAddress();
        return ((($address >> 24) & 0xff) == 224) && ((($address >> 16) & 0xff) == 0) && ((($address >> 8) & 0xff) == 0);
    }

    public function isMCSiteLocal()
    {
        // 239.255/16 prefix or ttl < 32
        $address = $this->holder->getAddress();
        return ((($address >> 24) & 0xff) == 239) && ((($address >> 16) & 0xff) == 255);
    }

    public function isMCOrgLocal()
    {
        // 239.192 - 239.195
        $address = $this->holder->getAddress();
        return ((($address >> 24) & 0xff) == 239) && ((($address >> 16) & 0xff) >= 192) &&
             ((($address >> 16) & 0xff) <= 195);
    }

    /**
     * Returns the raw IP address of this <code>Inet4Address</code> object. The
     * result is in network byte order: The highest order byte of the address is
     * in <code>getAddress()[0]</code>.
     *
     * @return string The raw (byte array) IP address of this object.
     * @see \KM\Net\InetAddress::getAddress()
     */
    public function getAddress()
    {
        $address = $this->holder->getAddress();
        return pack('N', $address);
    }

    /**
     * Returns the IP address string in textual presentation form.
     *
     * @return string The raw IP address in a string form.
     * @see \KM\Net\InetAddress::getHostAddress()
     */
    public function getHostAddress()
    {
        return self::numericToTextFormat($this->getAddress());
    }

    /**
     * Returns a hash code for this IP address.
     *
     * @return int
     * @see \KM\Lang\Object::hashCode()
     */
    public function hashCode()
    {
        return $this->holder->getAddress();
    }

    /**
     * Compares this object against the specified object. The result is
     * <code>true</code> if and only if the argument is not <code>null</code>
     * and it represents the same IP address as this object. <p> Two instances
     * of <code>InetAddress</code> represent the same IP address if the length
     * of the byte arrays returned by <code>getAddress</code> is the same for
     * both, and each of the array components is the same for the byte arrays.
     *
     * @param Object $obj The object to compare against.
     * @return boolean <code>true</code> if the objects are the same,
     *         >code>false</code> otherwise.
     * @see \KM\Lang\Object::equals()
     */
    public function equals(Object $obj = null)
    {
        /* @var $that Inet4Address */
        if (($obj != null) && ($obj instanceof Inet4Address)) {
            $that = $obj;
            return $that->holder->getAddress() == $this->holder->getAddress();
        }
        return false;
    }

    /**
     * Converts the IPv4 binary address into a string suitable for presentation.
     *
     * @param string $src A byte array representing an IPv4 numeric address
     * @return string representing the Iv4 address in textual representation
     *         format.
     */
    public static function numericToTextFormat($src)
    {
        return inet_ntop($src);
    }
}
?>