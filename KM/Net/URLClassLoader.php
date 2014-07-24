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

use KM\Lang\ClassLoader;
use Sun\Misc\URLClassPath;
use KM\IO\IOException;
use KM\Lang\Clazz;

/**
 * URLClassLoader Class
 *
 * @author Blair
 */
class URLClassLoader extends ClassLoader
{

    /**
     * The search path for classes and resources
     *
     * @var URLClassPath
     */
    public $ucp;

    public function __construct(array $urls, ClassLoader $parent = null)
    {
        parent::__construct($parent);
        $this->ucp = new URLClassPath($urls);
    }

    protected function findClass($name)
    {
        $path = str_replace(self::$NS_SEPARATOR, DIRECTORY_SEPARATOR, $name);
    }

    protected function defineClass($name)
    {
        $url = $this->class2filename($name, $directory);
        $i = strrpos($name, DIRECTORY_SEPARATOR);
        if ($i !== false) {
            $pkgName = substr($name, 0, $i);
            
            // Check if the package is already loaded
            if ($this->getPackage($pkgName) == null) {
                $this->definePackage($pkgName, $directory);
            }
        }
        
        // Now read the class bytes and define the class
        include $url;
        return $this->defineClass0($name);
    }
}
?>