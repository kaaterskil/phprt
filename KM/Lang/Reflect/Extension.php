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
namespace KM\Lang\Reflect;

use KM\Lang\Clazz;
use KM\Lang\InitializerException;
use KM\Lang\Object;
use KM\Util\HashMap;
use KM\Util\Map;

/**
 * The <code>Extension</code> class reports information about a PHP extension.
 * This class is a wrapper around a \ReflectionExtension object so that the
 * methods and signature of \KM\Lang\Object are implemented.
 *
 * @author Blair
 */
class Extension extends Object
{

    /**
     * The underlying reflection object.
     *
     * @var \ReflectionExtension
     */
    private $root;

    /**
     * The name of the PHP extension.
     *
     * @var string
     */
    private $name;

    /**
     * Creates a new Extension object with the given PHP extension name.
     *
     * @param string $name The name of the PHP extension to reflect.
     */
    public function __construct($name)
    {
        $name = (string) $name;
        $this->root = new \ReflectionExtension($name);
        $this->name = $name;
    }

    /**
     * Private method to prevent this object from being cloned. Reflection
     * objects cannot be cloned.
     */
    final private function __clone()
    {}

    /**
     * Exports a reflected extension. The output format of this method if the
     * same as the CLI argument.
     *
     * @param string $name The reflection to export.
     * @param boolean $return Setting to <code>true</code> will return the
     *            export; setting to <code>false</code> will emit it.
     * @return string If the <code>return</code> parameter is set to
     *         <code>true</code> the export is returned as a string; otherwise
     *         <code>null</code> is returned.
     */
    public static function export($name, $return = false)
    {
        return \ReflectionExtension::export($name, $return);
    }

    /**
     * Returns a Map of classes defined in the PHP extension represented by this
     * <code>Extension</code> object.
     *
     * @return \KM\Util\Map A Map of classes defined in the PHP extension
     *         represented by this <code>Extension</code> object. The class
     *         names are the map keys and <code>Clazz</code> objects
     *         representing the classes are the values. If no classes defined,
     *         an empty Map is returned.
     */
    public function getClasses()
    {
        $res = new HashMap('<string, \KM\Lang\Clazz>');
        foreach ($this->root->getClassNames() as $className) {
            try {
                $cl = Clazz::forName($className);
                $res->put($className, $cl);
            } catch (InitializerException $e) {
                trigger_error($e->getMessage());
            }
        }
        return $res;
    }

    /**
     * Returns the class names defined in the PHP extension represented by this
     * <code>Extension</code> object.
     *
     * @return array An array of class names as defined in the PHP extension
     *         represented by this <code>Extension</code> object. If no classes
     *         are defined, an array of length 0 is returned.
     */
    public function getClassNames()
    {
        return $this->root->getClassNames();
    }

    /**
     * REturns the constants for the PHP extension represented by this
     * <code>Extension</code> object.
     *
     * @return array An associative array with constant names as
     *         <code>keys</code> an their value as <code>values</code>.
     */
    public function getConstants()
    {
        return $this->root->getConstants();
    }

    /**
     * Returns required and conflicting dependencies of the PHP extension
     * represented by this <code>Extension</code> object.
     *
     * @return array An associative array with dependencies as <code>keys</code>
     *         and either <em>Required</em>, <em>Optional</em>, or
     *         <em>Conflicts</em> as the <code>values</code>.
     */
    public function getDependencies()
    {
        return $this->getDependencies();
    }

    /**
     * Returns a Map of <code>Procedure</code> objects for each function defined
     * in the extension represented by this <code>Extension</code> object, with
     * the keys being the function names. If no functions are defined, an empty
     * map is returned.
     *
     * @return \KM\Util\Map A map of Procedures defined in the extension
     *         represented by this <code>Extension</code> object, with the
     *         function names as the keys.
     */
    public function getFunctions()
    {
        /* @var $rf \ReflectionFunction */
        $res = new HashMap('<string, \KM\Lang\Reflect\Procedure>');
        foreach ($this->root->getFunctions() as $key => $rf) {
            try {
                $p = new Procedure($rf->name);
                $res->put($key, $p);
            } catch (\Exception $e) {
                trigger_error($e->getMessage());
            }
        }
        return $res;
    }

    /**
     * Returns the <code>ini</code> entries of the PHP extension represented by
     * this <code>Extension</code> object.
     *
     * @return array An associative array of the <code>ini</code> entries of the
     *         PHP extension represented by this <code>Extension</code> object,
     *         with the <code>ini</code> entries as <code>keys</code> and their
     *         defined values as <code>values</code>.
     */
    public function getINIEntries()
    {
        return $this->root->getINIEntries();
    }

    /**
     * Returns the name of the PHP extension represented by this
     * <code>Extension</code> object.
     *
     * @return string The name of the PHP extension represented by this
     *         <code>Extension</code> object.
     */
    public function getName()
    {
        return $this->root->getName();
    }

    /**
     * Returns the version of the PHP extension represented by this
     * <code>Extension</code> object.
     *
     * @return string The version of the PHP extension represented by this
     *         <code>Extension</code> object.
     */
    public function getVersion()
    {
        return $this->getVersion();
    }

    /**
     * Prints the <code>phpinfo()</code> snippet with information about the PHP
     * extension represented by this <code>Extension</code> object.
     *
     * @return string Information about the PHP extension represented by this
     *         <code>Extension</code> object.
     */
    public function info()
    {
        return $this->root->info();
    }

    /**
     * Tells whether the PHP extension represented by this
     * <code>Extension</code> object if persistent.
     *
     * @return boolean <code>True</code> if the PHP extension represented by
     *         this <code>Extension</code> object was loaded by
     *         <code>extension</code>, <code>false</code> otherwise.
     */
    public function isPersistent()
    {
        return $this->root->isPersistent();
    }

    /**
     * Tells whether the PHP extension represented by this
     * <code>Extension</code> object is temporary.
     *
     * @return boolean <code>True</code> if the PHP extension represented by
     *         this <code>Extension</code> object was loaded by
     *         <code>dl()</code>, <code>false</code> otherwise.
     */
    public function isTemporary()
    {
        return $this->root->isTemporary();
    }

    /**
     * Returns the exported extension as a string, in the same way as the static
     * method <code>export</code>.
     *
     * @return string The exported extension as a string.
     * @see \KM\Lang\Object::__toString()
     */
    public function __toString()
    {
        return (string) $this->root;
    }
}
?>