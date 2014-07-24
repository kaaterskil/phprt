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
namespace Sun\Misc;

include 'KM\Lang\ClassLoader.php';

/**
 * Launcher Class
 *
 * @author Blair
 */
class Launcher
{

    /**
     * Our singleton instance
     *
     * @var Launcher
     */
    private static $launcher;

    /**
     * Returns the singleton instance of the launcher.
     *
     * @return \Sun\Misc\Launcher
     */
    public static function getLauncher()
    {
        if (self::$launcher === null) {
            self::$launcher = new self();
        }
        return self::$launcher;
    }

    /**
     * The application class loader
     *
     * @var AppClassLoader
     */
    private $loader;

    protected function __construct()
    {
        $this->loader = AppClassLoader::getAppClassLoader();
    }

    /**
     * Returns the class loader used to launch the main application.
     *
     * @return \Sun\Misc\Launcher\AppClassLoader
     */
    public function getClassLoader()
    {
        return $this->loader;
    }
}

class AppClassLoader extends \KM\Lang\ClassLoader
{

    private $urls;

    private $path;

    public static function getAppClassLoader()
    {
        $s = dirname(dirname(dirname(dirname(__FILE__))));
        $urls[0] = $s;
        return new self($urls, null);
    }

    public function __construct(array $urls)
    {
        parent::__construct();
        $this->urls = $urls;
        $this->path = $urls;
    }

    public function getUrls()
    {
        $returnValue = [];
        foreach ($this->urls as $url) {
            $returnValue[] = $url;
        }
        return $returnValue;
    }
}
?>