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
namespace Sun\Net\Util;

use KM\Lang\Object;

/**
 * IPAddressUtil Class
 *
 * @author Blair
 */
class IPAddressUtil extends Object
{

    /**
     * Utility constructor
     */
    private function __construct()
    {}

    /**
     * Converts IPv4 address in its textual presentation form into its numeric
     * binary form.
     *
     * @param string $src A string representing an IPv4 address in standard
     *            format.
     * @return string A byte array representing the IPv4 numeric address.
     */
    public static function textToNumericFormatV4($src)
    {
        return self::textToNumericFormat($src);
    }

    /**
     * Convert IPv6 presentation level address to network order binary form.
     *
     * @param string $src A string representing an IPv6 address in textual
     *            format.
     * @return string A byte array representing the IPv6 numeric address.
     */
    public static function textToNumericFormatV6($src)
    {
        return self::textToNumericFormat($src);
    }

    /**
     *
     * @param string $src A string representing an IPv4 address in textual
     *            format.
     * @return boolean indicating whether <code>src</code> is an IPv4 literal
     *         address.
     */
    public static function isIPv4LiteralAddress($src)
    {
        $isValid = filter_var($src, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if ($isValid !== false) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $src A string representing an IPv6 address in textual
     *            format
     * @return boolean indicating whether <code>src</code> is an IPv6 literal
     *         address.
     */
    public static function isIPv6LiteralAddress($src)
    {
        $isValid = filter_var($src, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        if ($isValid !== false) {
            return true;
        }
        return false;
    }

    /**
     * Convert IPv4 mapped address to IPv4 address. Both input and returned
     * value are in network order binary form.
     *
     * @param string $addr A string representing an IPv4 mapped address in
     *            textual format.
     * @return string A byte array representing the IPv4 numeric address.
     */
    public static function convertFromIPv4MappedAddress($addr)
    {
        return self::textToNumericFormat($addr);
    }

    private static function textToNumericFormat($src)
    {
        // Strip out netmask if there is one. inet_pton() does not recognize
        // netmask notation (e.g: "1.2.3.4/24" or "1:2::3:4/64") per PHP manual.
        $cx = strpos($src, '/');
        if ($cx !== false) {
            $src = substr($src, 0, $cx);
        }
        
        // Convert address to packed format
        $res = inet_pton($src);
        if ($res === false) {
            return null;
        }
        return $res;
    }
}
?>