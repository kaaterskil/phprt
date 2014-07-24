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
namespace Sun\Net\SPI\NameService\DNS;

use KM\Lang\Object;
use KM\Net\InetAddress;
use KM\Net\UnknownHostException;
use Sun\Net\SPI\NameService\NameService;
use Sun\Net\Util\IPAddressUtil;

/**
 * A name service provider.
 *
 * @author Blair
 */
final class DNSNameService extends Object implements NameService
{

    /**
     * Returns a list of IPv4 addresses corresponding to a given Internet host
     * name.
     *
     * @param string $host The host name
     * @throws UnknownHostException if the host name could not be resolved.
     * @return \KM\Net\InetAddress[] An array of InetAddress objects
     *         representing the resolved IPv4 addresses corresponding to the
     *         given Internet host name.
     * @see \Sun\Net\SPI\NameService\NameService::lookupAllHostAddr()
     */
    public function lookupAllHostAddr($host)
    {
        $results = gethostbynamel($host);
        if (($results === false) || count($results) == 0) {
            // Sun developers attempt devolution of host name using the
            // nameservers and search lists provided by the
            // ResolverConfiguration. We'll try to avoid this assuming that the
            // PHP function just called is sufficiently powerful.
            throw new UnknownHostException();
        }
        
        $size = count($results);
        $addrs = array_fill(0, $size, null);
        $count = 0;
        for ($i = 0; $i < $size; $i ++) {
            $addrString = $results[$i];
            $addr = IPAddressUtil::textToNumericFormatV4($addrString);
            if (empty($addr)) {
                $addr = IPAddressUtil::textToNumericFormatV6($addrString);
            }
            if (! empty($addr)) {
                $addrs[$count ++] = InetAddress::getByAddress($host, $addr);
            }
        }
        if ($count == 0) {
            throw new UnknownHostException($host . ': no valid DNS records');
        }
        if ($count < $size) {
            $tmp = array_fill(0, $count, null);
            for ($i = 0; $i < $count; $i ++) {
                $tmp[$i] = $addrs[$i];
            }
            $addrs = $tmp;
        }
        return $addrs;
    }

    /**
     * Reverse lookup code. I.E: find a host name from an IP address. IPv4
     * addresses are mapped in the IN-ADDR.ARPA. top domain, while IPv6
     * addresses can be in IP6.ARPA or IP6.INT. In both cases the address has to
     * be converted into a dotted form.
     *
     * @param string $addr
     * @throws UnknownHostException
     * @return string
     * @see \Sun\Net\SPI\NameService\NameService::getHostByAddr()
     */
    public function getHostByAddr($addr)
    {
        $host = gethostbyaddr($addr);
        if (($host === false) || ($host == $addr)) {
            throw new UnknownHostException();
        }
        return $host;
    }
}
?>