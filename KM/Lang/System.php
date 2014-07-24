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
namespace KM\Lang;

use KM\Util\Collections;
use KM\Util\HashMap;
use KM\Util\Map;
use KM\Util\Properties;

/**
 * System Class
 *
 * @author Blair
 */
class System extends Object
{

    /**
     * The system properties
     *
     * @var Properties
     */
    private static $props;

    /**
     * The system line separator
     *
     * @var string
     */
    private static $lineSeparator;

    /**
     * Static constructor
     */
    public static function clinit()
    {
        self::initProperties();
        self::$lineSeparator = self::$props->getProperty('line.separator');
    }

    /**
     * Prevent instantiation
     */
    private function __construct()
    {}

    /**
     * Copies an array from the specified source array, beginning at the
     * specified position, to the specified position of the destination array. A
     * subsequence of array components are copied from the source array
     * referenced by <code>src</code> to the destination array referenced by
     * <code>dest</code>. The number of components copied is equal to the
     * <code>length</code> argument. The components at positions
     * <code>srcPos</code> through <code>srcPos+length-1</code> in the source
     * array are copied into positions <code>destPos</code> through
     * <code>destPos+length-1</code>, respectively, of the destination array.
     * <p> If the <code>src</code> and <code>dest</code> arguments refer to the
     * same array object, then the copying is performed as if the components at
     * positions <code>srcPos</code> through <code>srcPos+length-1</code> were
     * first copied to a temporary array with <code>length</code> components and
     * then the contents of the temporary array were copied into positions
     * <code>destPos</code> through <code>destPos+length-1</code> of the
     * destination array. <p> If <code>dest</code> is <code>null</code>, then a
     * <code>NullPointerException</code> is thrown. <p> If <code>src</code> is
     * <code>null</code>, then a <code>NullPointerException</code> is thrown and
     * the destination array is not modified. <p> Otherwise, if any of the
     * following is true, an <code>ArrayStoreException</code> is thrown and the
     * destination is not modified: <ul> <li>The <code>src</code> argument
     * refers to an object that is not an array. <li>The <code>dest</code>
     * argument refers to an object that is not an array. <li>The
     * <code>src</code> argument and <code>dest</code> argument refer to arrays
     * whose component types are different primitive types. <li>The
     * <code>src</code> argument refers to an array with a primitive component
     * type and the <code>dest</code> argument refers to an array with a
     * reference component type. <li>The <code>src</code> argument refers to an
     * array with a reference component type and the <code>dest</code> argument
     * refers to an array with a primitive component type. </ul> <p> Otherwise,
     * if any of the following is true, an
     * <code>IndexOutOfBoundsException</code> is thrown and the destination is
     * not modified: <ul> <li>The <code>srcPos</code> argument is negative.
     * <li>The <code>destPos</code> argument is negative. <li>The
     * <code>length</code> argument is negative. <li><code>srcPos+length</code>
     * is greater than <code>src.length</code>, the length of the source array.
     * <li><code>destPos+length</code> is greater than <code>dest.length</code>,
     * the length of the destination array. </ul> <p> Otherwise, if any actual
     * component of the source array from position <code>srcPos</code> through
     * <code>srcPos+length-1</code> cannot be converted to the component type of
     * the destination array by assignment conversion, an
     * <code>ArrayStoreException</code> is thrown. In this case, let
     * <b><i>k</i></b> be the smallest nonnegative integer less than length such
     * that <code>src[srcPos+</code><i>k</i><code>]</code> cannot be converted
     * to the component type of the destination array; when the exception is
     * thrown, source array components from positions <code>srcPos</code>
     * through <code>srcPos+</code><i>k</i><code>-1</code> will already have
     * been copied to destination array positions <code>destPos</code> through
     * <code>destPos+</code><i>k</I><code>-1</code> and no other positions of
     * the destination array will have been modified. (Because of the
     * restrictions already itemized, this paragraph effectively applies only to
     * the situation where both arrays have component types that are reference
     * types.)
     *
     * @param mixed $src The source array.
     * @param int $srcPos Starting position in the source array.
     * @param mixed $dest The destination array.
     * @param int $destPos Starting position in the destination array.
     * @param int $length The number of array elements to copy.
     */
    public static function arraycopy(array &$src, $srcPos, array &$dest, $destPos, $length)
    {
        $srcPos = (int) $srcPos;
        $destPos = (int) $destPos;
        $length = (int) $length;
        array_splice($dest, $destPos, $length, array_slice($src, $srcPos, $length));
    }

    /**
     * Returns the same hashcode for the given object as would be returned by
     * the method hashCode(). The hash code for the null reference is zero.
     *
     * @param object $x The object for which the hashCode is to be calculated.
     * @return string The hashCode.
     */
    public static function identityHashCode($x)
    {
        $hashCode = '0';
        if ($x != null) {
            if ($x instanceof Object) {
                $hashCode = $x->hashCode();
            } elseif (is_object($x)) {
                $hashCode = spl_object_hash($x);
            }
        }
        return $hashCode;
    }

    private static function initProperties(Properties $props = null)
    {
        $p = new Properties($props);
        
        $settings = parse_ini_file(PHP_LIBDIR . DIRECTORY_SEPARATOR . 'php.ini');
        foreach ($settings as $key => $value) {
            $p->setProperty($key, strval($value));
        }
        $p->setProperty('php.version', phpversion());
        $p->setProperty('php.home', dirname(dirname(dirname(__FILE__))));
        $p->setProperty('os.name', php_uname('s'));
        $p->setProperty('os.arch', php_uname('m'));
        $p->setProperty('os.version', php_uname('v'));
        $p->setProperty('file.separator', DIRECTORY_SEPARATOR);
        $p->setProperty('path.separator', PATH_SEPARATOR);
        $p->setProperty('line.separator', PHP_EOL);
        $p->setProperty('user.name', get_current_user());
        $p->setProperty('user.home',
            (strpos(PHP_OS, 'WIN') !== false) ? trim(exec('set homepath')) : trim(shell_exec('whereis php')));
        $p->setProperty('user.dir', realpath('')); // User working directory
        self::$props = $p;
    }

    /**
     * Returns the current system properties.
     *
     * @return \KM\Util\Properties
     */
    public static function getProperties()
    {
        return self::$props;
    }

    /**
     * Returns the system-dependent line separator string. It always returns the
     * same value - the initial value of the getProperty() system property
     * line.separator.
     * On UNIX systems, it returns "\n"; on Microsoft Windows systems, it
     * returns "\r\n".
     * @return string The system-dependent line separator string.
     */
    public static function lineSeparator()
    {
        return self::$lineSeparator;
    }

    /**
     * Sets the system properties to the Properties argument. The argument
     * becomes the current set of system properties for use by the
     * getProperties() method. Id the argument is null, then the current set of
     * system properties is replaced by the original configuration.
     *
     * @param Properties $props
     */
    public static function setProperties(Properties $props)
    {
        /* @var $es Map\Entry */
        if ($props == null) {
            $props = new Properties();
            self::initProperties($props);
        } else {
            foreach ($props->entrySet() as $es) {
                $key = $es->getKey();
                $newValue = $es->getValue();
                $oldValue = ini_get($key);
                if ($newValue != $oldValue) {
                    ini_set($key, $newValue);
                }
            }
        }
        self::$props = $props;
    }

    /**
     * Returns the system property indicated by the specified key.
     *
     * @param string $key The name of the system property.
     * @param string $default A default value.
     * @return string The string value of the system property.
     */
    public static function getProperty($key, $default = null)
    {
        self::checkKey($key);
        $key = (string) $key;
        return self::$props->getProperty($key, $default);
    }

    /**
     * Sets the system property indicated by the specified key.
     *
     * @param string $key The name of the system property.
     * @param string $newValue The value of the system property.
     * @return string The previous value of the system property or null if it
     *         did not have one.
     */
    public static function setProperty($key, $newValue)
    {
        self::checkKey($key);
        $key = (string) $key;
        $newValue = (string) $newValue;
        $oldValue = ini_get($key);
        if ($newValue != $oldValue) {
            ini_set($key, $newValue);
        }
        return self::$props->setProperty($key, $newValue);
    }

    /**
     * Removes the system property indicated by the specified key.
     *
     * @param string $key The name of the system property
     * @return string The previous string value of the system property or null
     *         if there was no property with that key.
     */
    public static function clearProperty($key)
    {
        self::checkKey($key);
        $key = (string) $key;
        $oldValue = ini_get($key);
        if (! empty($oldValue)) {
            ini_set($key, null);
        }
        self::$props->remove($key);
    }

    private static function checkKey($key)
    {
        if ($key == null) {
            throw new NullPointerException("key can't be null");
        }
        if ($key == '') {
            throw new IllegalArgumentException("key can't be empty");
        }
    }

    /**
     * Returns the value of the specified environment variable. If no
     * environment variable is specified an unmodifiable string map view of the
     * current system environment is returned.
     *
     * @param string $name
     * @return string The string value of the variable or null if the variable
     *         is not defined in the system environment.
     */
    public static function getenv($name = null)
    {
        if ($name == null) {
            return self::getenv0();
        }
        return getenv($name);
    }

    private static function getenv0()
    {
        $m = new HashMap();
        $vars = $_ENV;
        if (count($vars)) {
            foreach ($vars as $k => $v) {
                $m->put($k, $v);
            }
        }
        return Collections::unmodifiableMap($m);
    }

    /**
     * Runs the garbage collector.
     */
    public static function gc()
    {
        gc_collect_cycles();
    }
}
?>