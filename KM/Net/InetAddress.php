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

use KM\IO\Serializable;
use KM\Lang\Clazz;
use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\Lang\System;
use KM\Net\InetAddress\InetAddressHolder;
use KM\Net\InetAddress\InetAddressImplFactory;
use KM\Net\InetAddressImpl;
use KM\Net\UnknownHostException;
use Sun\Net\SPI\NameService\DNS\DNSNameService;
use Sun\Net\SPI\NameService\NameService;
use Sun\Net\Util\IPAddressUtil;

/**
 * InetAddress Class
 *
 * @author Blair
 */
class InetAddress extends Object implements Serializable
{

    /**
     * Specify address family: Internet protocol, version 4.
     *
     * @var unknown
     */
    const IPv4 = 1;

    /**
     * Specify the address family: Internet protocol, version 6.
     *
     * @var int
     */
    const IPv6 = 2;

    /**
     * Specify address family preference
     *
     * @var boolean
     */
    public static $preferIPv6Address = false;

    /**
     * Used to store the serializable fields of InetAddress
     *
     * @var InetAddressHolder
     */
    protected $holder;

    /**
     * Returns the serializable fields of InetAddress
     *
     * @return \KM\Net\InetAddress\InetAddressHolder
     */
    public function holder()
    {
        return $this->holder;
    }

    /**
     * The name service provider.
     *
     * @var NameService
     */
    private static $nameService;

    /**
     * Used to store the best available host name
     *
     * @var string
     */
    private $canonicalHostName = null;

    /**
     *
     * @var InetAddress[]
     */
    protected static $unknownArray;

    /**
     *
     * @var InetAddressImpl
     */
    protected static $impl;

    /**
     * Static constructor
     */
    public static function clinit()
    {
        self::$impl = InetAddressImplFactory::create();
        self::$nameService = new DNSNameService();
        self::$unknownArray[0] = self::$impl->anyLocalAddress();
        self::$preferIPv6Address = System::getProperty('km.net.preferIPV6Addresses', false) == 'true' ? true : false;
    }

    /**
     * Creates an empty InetAddress.
     */
    public function __construct()
    {
        $this->holder = new InetAddressHolder();
    }

    /**
     * Utility routine to check if the InetAddress is an IP multicast address.
     *
     * @return boolean <code>true</code> if the InetAddress is an IP multicast
     *         address, <code>false</code> otherwise.
     */
    public function isMulticastAddress()
    {
        return false;
    }

    /**
     * Utility routine to check if the InetAddress is a wildcard address.
     *
     * @return boolean <code>true</code> if the InetAddress is a wildcard
     *         address, <code>false</code> otherwise.
     */
    public function isAnyLocalAddress()
    {
        return false;
    }

    /**
     * Utility routine to check if the InetAddress is a loopback address.
     *
     * @return boolean <code>true</code> if the InetAddress is a loopback
     *         address, <code>false</code> otherwise.
     */
    public function isLoopbackAddress()
    {
        return false;
    }

    /**
     * Utility routine to check if the InetAddress is a link local address.
     *
     * @return boolean <code>true</code> if the InetAddress is a link local
     *         address, <code>false</code> otherwise.
     */
    public function isLinkLocalAddress()
    {
        return false;
    }

    /**
     * Utility routine to check if the InetAddress is a site local address.
     *
     * @return boolean <code>true</code> if the InetAddress is a site local
     *         address, <code>false</code> otherwise.
     */
    public function isSiteLocalAddress()
    {
        return false;
    }

    /**
     * Utility routine to check if the multicast address has global scope.
     *
     * @return boolean <code>true</code> if the multicast address has global
     *         scope, <code>false</code> otherwise.
     */
    public function isMCGlobal()
    {
        return false;
    }

    /**
     * Utility routine to check if the multicast address has node scope.
     *
     * @return boolean <code>true</code> if the multicast address has node
     *         scope, <code>false</code> otherwise.
     */
    public function isMCNodeLocal()
    {
        return false;
    }

    /**
     * Utility routine to check if the multicast address has link scope.
     *
     * @return boolean <code>true</code> if the multicast address has link
     *         scope, <code>false</code> otherwise.
     */
    public function isMCLinkLocal()
    {
        return false;
    }

    /**
     * Utility routine to check if the multicast address has site scope.
     *
     * @return boolean <code>true</code> if the multicast address has site
     *         scope, <code>false</code> otherwise.
     */
    public function isMCSiteLocal()
    {
        return false;
    }

    /**
     * Utility routine to check if the multicast address has organization scope.
     *
     * @return boolean <code>true</code> if the multicast address has
     *         organization scope, <code>false</code> otherwise.
     */
    public function isMCOrgLocal()
    {
        return false;
    }

    /**
     * Gets the host name for this IP address. If this InetAddress was created
     * with a host name, this host name will be remembered and returned;
     * otherwise, a reverse name lookup will be performed and the result will be
     * returned based on the system configured name lookup service. If a lookup
     * of the name service is required, call
     * <code>getCanonicalHostName()</code>.
     *
     * @return string The host name for this IP address.
     */
    public function getHostName()
    {
        if ($this->holder->getHostName() == null) {
            $this->holder->hostName = self::getHostFromNameService($this);
        }
        return $this->holder->getHostName();
    }

    /**
     * Gets the fully qualified domain name for this IP address. Best effort
     * method, meaning we may not be able to return the FQDN depending on the
     * underlying system configuration.
     *
     * @return string The fully qualified domain name for this IP address.
     */
    public function getCanonicalHostName()
    {
        if ($this->canonicalHostName == null) {
            $this->canonicalHostName = self::getHostFromNameService($this);
        }
        return $this->canonicalHostName;
    }

    /**
     * Returns the hostname for this address.
     *
     * @param InetAddress $addr
     * @return string The host name for this IP address.
     */
    private static function getHostFromNameService(InetAddress $addr)
    {
        $host = '';
        try {
            // First, look up the host name
            $host = self::$nameService->getHostByAddr($addr->getHostAddress());
            
            // Now get all the IP addresses for this hostname and make sure one
            // of these matches the
            // original IP address, We do this to try to prevent spoofing.
            $arr = self::getAllByName0($host);
            $ok = false;
            if (count($arr) > 0) {
                for ($i = 0; $i < count($arr); $i ++) {
                    $ok = $addr->equals($arr[$i]);
                }
            }
            if (! $ok) {
                $host = $addr->getHostAddress();
                return $host;
            }
        } catch (UnknownHostException $e) {
            $host = $addr->getHostAddress();
        }
        return $host;
    }

    /**
     * Returns the raw IP address of this <code>InetAddress</code> object. The
     * result is in network byte order: the highest order byte of the address is
     * in <code>getAddress()[0]</code>.
     *
     * @return string The raw IP address (byte array) of this object.
     */
    public function getAddress()
    {
        return null;
    }

    /**
     * Returns the IP address string in its textual presentation.
     *
     * @return string The raw IP address in a string format.
     */
    public function getHostAddress()
    {
        return null;
    }

    /**
     * Converts this IP address to a string. THe string returned is of the form:
     * hostname / literal IP address. If the hose name is unresolved, no reverse
     * name service lookup is performed. THe hostname part will be represented
     * by an empty string.
     *
     * @return string A string representation of this IP address.
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        $hostName = $this->holder->getHostName();
        return ($hostName != null ? $hostName : '') . '/' . $this->getHostAddress();
    }

    /**
     * Creates an InetAddress based on the provided host name and IP address. No
     * name service is checked for the validity of the address. <p> The host
     * name can either be a machine name, such as "<code>java.sun.com</code>",
     * or a textual representation of its IP address. <p> No validity checking
     * is done on the host name either. <p> If addr specifies an IPv4 address an
     * instance of Inet4Address will be returned; otherwise, an instance of
     * Inet6Address will be returned. <p> IPv4 address byte array must be 4
     * bytes long and IPv6 byte array must be 16 bytes long
     *
     * @param string $host The specified host
     * @param string $addr (byte array) The raw IP address in network byte
     *            order.
     * @throws UnknownHostException if IP address is of illegal length.
     * @return \KM\Net\InetAddress An InetAddress object created from the raw IP
     *         address.
     */
    public static function getByAddress($host, $addr)
    {
        if (! empty($host) && $host[0] == '[') {
            if ($host[strlen($host) - 1] == ']') {
                $host = substr($host, 1, strlen($host) - 1);
            }
        }
        if (! empty($addr)) {
            if (strlen($addr) == Inet4Address::INADDRSZ) {
                return new Inet4Address($host, $addr);
            } elseif (strlen($addr) == Inet6Address::INADDRSZ) {
                $newAddr = IPAddressUtil::convertFromIPv4MappedAddress($addr);
                if (! empty($newAddr)) {
                    return new Inet4Address($host, $newAddr);
                } else {
                    return new Inet6Address($host, $addr);
                }
            }
        }
        throw new UnknownHostException('addr is of illegal length');
    }

    /**
     * Determines the IP address of a host, given the host's name. <p> The host
     * name can either be a machine name, such as "<code>java.sun.com</code>",
     * or a textual representation of its IP address. If a literal IP address is
     * supplied, only the validity of the address format is checked. <p> For
     * <code>host</code> specified in literal IPv6 address, either the form
     * defined in RFC 2732 or the literal IPv6 address format defined in RFC
     * 2373 is accepted. IPv6 scoped addresses are also supported. <p> If the
     * host is <code>null</code> then an <code>InetAddress</code> representing
     * an address of the loopback interface is returned. See <a
     * href="http://www.ietf.org/rfc/rfc3330.txt">RFC&nbsp;3330</a>
     * section&nbsp;2 and <a
     * href="http://www.ietf.org/rfc/rfc2373.txt">RFC&nbsp;2373</a>
     * section&nbsp;2.5.3. </p>
     *
     * @param string $host The specified host or <code>null</code>.
     * @return \KM\Net\InetAddress An InetAddress object for the given host
     *         name.
     * @throws UnknownHostException if no IP address for the <code>host</code>
     *         could be found.
     */
    public static function getByName($host)
    {
        $result = self::getAllByName($host);
        return $result[0];
    }

    /**
     * Given the name of a host, returns an array of its IP addresses, based on
     * the configured name service on the system. <p> The host name can either
     * be a machine name, such as "<code>java.sun.com</code>", or a textual
     * representation of its IP address. If a literal IP address is supplied,
     * only the validity of the address format is checked. <p> For
     * <code>host</code> specified in <i>literal IPv6 address</i>, either the
     * form defined in RFC 2732 or the literal IPv6 address format defined in
     * RFC 2373 is accepted. A literal IPv6 address may also be qualified by
     * appending a scoped zone identifier or scope_id. <p> If the host is
     * <code>null</code> then an <code>InetAddress</code> representing an
     * address of the loopback interface is returned. See <a
     * href="http://www.ietf.org/rfc/rfc3330.txt">RFC&nbsp;3330</a>
     * section&nbsp;2 and <a
     * href="http://www.ietf.org/rfc/rfc2373.txt">RFC&nbsp;2373</a>
     * section&nbsp;2.5.3. </p>
     *
     * @param string $host The name of the host or <code>null</code>.
     * @return \KM\Net\InetAddress[] an array of all the IP addresses for a
     *         given host name.
     * @throws UnknownHostException if no IP address for the <code>host</code>
     *         could be found.
     */
    public static function getAllByName($host)
    {
        return self::doGetAllByName($host, null);
    }

    private static function doGetAllByName($host, InetAddress $reqAddr = null)
    {
        if (empty($host)) {
            $ret[0] = self::$impl->loopbackAddress();
            return $ret;
        }
        
        $isIPv6Expected = false;
        if ($host[0] == '[') {
            if (strlen($host) > 2 && $host[strlen($host) - 1] == ']') {
                $host = substr($host, 1, - 1);
                $isIPv6Expected = true;
            } else {
                throw new UnknownHostException($host . ': Invalid IPv6 address');
            }
        }
        return self::getAllByName0($host, $reqAddr);
    }

    /**
     * Returns the loopback address. The InetAddress returned will represent the
     * IPv4 loopback address, 127.0.0.1 or the IPv6 loopback address, ::1.
     *
     * @return \KM\Net\InetAddress The InetAddress loopback instance.
     */
    public static function getLoopbackAddress()
    {
        return self::$impl->loopbackAddress();
    }

    private static function getAllByName0($host, InetAddress $reqAddr = null)
    {
        /* @var $returnValue \KM\Net\InetAddress[] */
        $addresses = self::getAddressesFromNameService($host, $reqAddr);
        if ($addresses == self::$unknownArray) {
            throw new UnknownHostException($host);
        }
        
        $returnValue = [];
        foreach ($addresses as $address) {
            $returnValue[] = $address;
        }
        return $returnValue;
    }

    private static function getAddressesFromNameService($host, InetAddress $reqAddr = null)
    {
        /* @var $addresses \KM\Net\InetAddress[] */
		/* @var $ex UnknownHostException */
		$addresses = [];
        $ex = null;
        $success = false;
        
        try {
            $addresses = self::$nameService->lookupAllHostAddr($host);
            $success = true;
        } catch (UnknownHostException $uhe) {
            if (strtolower($host) == 'localhost') {
                $addresses[0] = self::$impl->loopbackAddress();
                $success == true;
            } else {
                $addresses = self::$unknownArray;
                $ex = $uhe;
            }
        }
        
        // More to do?
        if ($reqAddr != null && count($addresses) > 1 && ! $addresses[0]->equals($reqAddr)) {
            // Find it?
            $i = 1;
            for (; $i < count($addresses); $i ++) {
                if ($addresses[$i]->equals($reqAddr)) {
                    break;
                }
            }
            // Rotate
            if ($i < count($addresses)) {
                $tmp = null;
                $tmp2 = $reqAddr;
                for ($j = 0; $j < $i; $j ++) {
                    $tmp = $addresses[$j];
                    $addresses[$j] = $reqAddr;
                    $tmp2 = $tmp;
                }
                $addresses[$i] = $tmp2;
            }
        }
        
        if (! $success && $ex != null) {
            throw $ex;
        }
        return $addresses;
    }

    /**
     * Returns an {@code InetAddress} object given the raw IP address .
     *
     *
     *
     * The argument is in network byte order: the highest order byte of the
     * address is in {@code getAddress()[0]}. <p> This method doesn't block,
     * i.e. no reverse name service lookup is performed. <p> IPv4 address byte
     * array must be 4 bytes long and IPv6 byte array must be 16 bytes long
     * @param string $addr The raw IP address in network byte order.
     * @return \KM\Net\InetAddress an InetAddress object created from the raw IP
     *         address.
     * @throws UnknownHostException if IP address is of illegal length.
     */
    public static function getByByteAddress($addr)
    {
        return self::getByAddress(null, $addr);
    }

    /**
     * Returns the address of the local host. This is achieved by retrieving the
     * name of the host from the system, then resolving that name into an
     * <code>InetAddress</code>.
     *
     * @return \KM\Net\InetAddress
     */
    public static function getLocalHost()
    {
        /* @var $ret InetAddress */
        $local = self::$impl->getLocalHostName();
        if (strpos($local, 'localhost') !== false) {
            return self::$impl->loopbackAddress();
        }
        
        $ret = null;
        $localAddrs = [];
        try {
            $localAddrs = self::getAddressesFromNameService($local);
        } catch (UnknownHostException $e) {
            throw new UnknownHostException($local . ': ' . $e->getMessage());
        }
        $ret = $localAddrs[0];
        return $ret;
    }

    /**
     * Returns the InetAddress representing anyLocalAddress (typically 0.0.0.0
     * or ::0).
     *
     * @return \KM\Net\InetAddress
     */
    public static function anyLocalAddress()
    {
        return self::$impl->anyLocalAddress();
    }

    /**
     * Load an instantiate an underlying impl class.
     *
     * @param string $implName
     * @return \KM\Net\InetAddressImpl
     */
    public static function loadImpl($implName)
    {
        /* @var $impl InetAddressImpl */
        $impl = null;
        
        // Property impl.prefix will be prepended to the classname of the
        // implementation object we
        // instantiate. The default is an empty string.
        $prefix = System::getProperty('impl.prefix');
        try {
            $impl = Clazz::forName('\\KM\\Net\\' . $prefix . $implName)->newInstance();
        } catch (\ReflectionException $e) {
            $format = "Class not found \KM\Net\%s%s: \nCheck impl.prefix property in your properties file.";
            trigger_error(sprintf($format, $prefix, $implName));
        }
        
        if ($impl == null) {
            try {
                $impl = Clazz::forName($implName)->newInstance();
            } catch (\ReflectionException $e) {
                trigger_error('System property impl.prefix incorrect');
            }
        }
        return $impl;
    }
}
?>