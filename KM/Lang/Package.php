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

use KM\Lang\Clazz;

/**
 * Package Class
 *
 * @author Blair
 */
class Package extends Object
{

    /**
     * The name of this package.
     *
     * @var string
     */
    private $pkgName;

    /**
     * The location of the package.
     *
     * @var string
     */
    private $directory;

    /**
     * The loader for this package.
     *
     * @var ClassLoader
     */
    private $loader;

    /**
     * Constructs a Package instance with the specified information.
     *
     * @param string $name The name of the package.
     * @param string $directory The location of the package in the client file
     *            system.
     * @param ClassLoader $loader
     */
    public function __construct($name, $directory, ClassLoader $loader = null)
    {
        $this->pkgName = (string) $name;
        $this->directory = $directory;
        $this->loader = $loader;
    }

    /**
     * Return the name of this package.
     *
     * @return string
     */
    public function getName()
    {
        return $this->pkgName;
    }

    /**
     * Returns the location of the package in the client file system.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Returns
     *
     * @return string
     */
    public function getPath()
    {
        $pattern = array(
            '/',
            '\\'
        );
        $dir = $this->directory;
        
        $last = $dir[strlen($dir) - 1];
        if (in_array($last, $pattern)) {
            $dir[strlen($dir) - 1] = DIRECTORY_SEPARATOR;
        }
        return $dir . DIRECTORY_SEPARATOR . $this->pkgName . DIRECTORY_SEPARATOR;
    }

    public function isSealed()
    {
        return $this->directory != null;
    }

    /**
     * Finds a package by name in the <code>ClassLoader</code> instance. The
     * <code>ClassLoader</code> instance is used to find the package instance
     * corresponding to the named class.
     *
     * @param string $name A package name, for example, KM\Lang.
     * @return \KM\Lang\Package The package of the requested name. It may be
     *         null if no package information is available from the class
     *         loader.
     */
    public static function getPackage($name)
    {
        $cl = ClassLoader::getInstance();
        return $cl->getPackage($name);
    }

    /**
     * Get the package for the specified class. The class's class loader is used
     * to find the package instance corresponding to the specified class.
     *
     * @param Clazz $c The class to get the package of.
     * @return \KM\Lang\Package The package of the requested name. It may be
     *         null if no package information is available from the class
     *         loader.
     */
    public static function getPackageFromClazz(Clazz $c)
    {
        $name = $c->getName();
        $i = strpos($name, '\\');
        if ($i !== false) {
            $name = substr($name, 0, $i);
            $cl = ClassLoader::getInstance();
            return $cl->getPackage($name);
        } else {
            return null;
        }
    }
}
?>