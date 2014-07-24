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

use KM\Lang\ClassNotFoundException;
use KM\Lang\Clazz;
use KM\Lang\InstantiationException;
use KM\Lang\Package;
use KM\IO\IOException;

/**
 * ClassLoader Class
 *
 * @author Blair
 */
class ClassLoader
{

    /**
     * The default namespace separator.
     *
     * @var string
     */
    protected static $NS_SEPARATOR = '\\';

    /**
     * The singleton class loader
     *
     * @var ClassLoader
     */
    private static $instance;

    /**
     * Returns the singleton instance
     *
     * @return \KM\Lang\ClassLoader
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * The classes loaded by this class loader. The only purpose of this table
     * is to keep the classes from being garbage collected until the loader is
     * garbage collected.
     *
     * @var \ReflectionClass[]
     */
    private $classes = array();

    /**
     * The packages defined in this class loader.
     *
     * @var array
     */
    private $packages = array();

    /**
     * The include paths
     *
     * @var string[]
     */
    private $paths = array();

    /**
     * Invoked by the PHP autoload stack to record every loaded class with this
     * loader.
     *
     * @param string $clazz
     */
    public function addClass($clazz)
    {
        $size = count($this->classes);
        for ($i = 0; $i < $size; $i ++) {
            $c = $this->classes[$i];
            if ($c == $clazz) {
                return;
            }
        }
        $this->classes[] = $clazz;
    }

    protected function __construct()
    {
        $this->paths = explode(PATH_SEPARATOR, get_include_path());
        $this->registerAutoload();
    }

    public function __destruct()
    {
        $this->unregisterAutoload();
    }

    /**
     * Registers this class loader with the __autoload stack.
     *
     * @param string $methodName The method name to register for this class
     *            loader.
     */
    protected function registerAutoload($methodName = null)
    {
        if ($methodName == null) {
            $methodName = 'loadClass';
        }
        $methodName = (string) $methodName;
        spl_autoload_register(array(
            $this,
            $methodName
        ));
    }

    /**
     * Unregisters this class loader with the __autoload stack.
     *
     * @param string $methodName The method name to unregister for this class
     *            loader.
     */
    protected function unregisterAutoload($methodName = null)
    {
        if ($methodName == null) {
            $methodName = 'loadClass';
        }
        $methodName = (string) $methodName;
        spl_autoload_unregister(array(
            $this,
            $methodName
        ));
    }
    
    /* ---------- Class ---------- */
    
    /**
     * Loads the class with the specified binary name. This method searches for
     * classes in the same manner as the <code>loadClass(String, boolean)</code>
     * method. It is invoked by the PHP autoload stack to resolve class
     * references. Invoking this method is equivalent to invoking
     * <code>#loadClass(String, boolean)</code>.
     *
     * @param string $name The binary name of the class.
     * @return \KM\Lang\Clazz The resulting Clazz object.
     * @throws ClassNotFoundException if the class was not found.
     */
    public function loadClass($name)
    {
        return $this->loadClass0($name, false);
    }

    /**
     * Loads the class with the specified binary name. The default
     * implementation of this method searches for classes in the following
     * order: <ol> <li>Invoke <code>#findLoadedClass(String)</code> to check if
     * the class has already been loaded.</li> <li><p> Invoke the
     * <code>#findClass(String)</code> method to find the class.</li> </ol> <p>
     * If the class was found using the above steps, and the <tt>resolve</tt>
     * flag is true, this method will then invoke the
     * <code>#resolveClass(Class)</code> method on the resulting
     * <code>Class</code> object.
     *
     * @param string $name The binary name of the class
     * @param boolean $resolve If <code>true</code> then resolve the class.
     * @return \ReflectionClass The resulting <code>Clazz</code> object.
     * @throws ClassNotFoundException if the class could not be found.
     */
    protected function loadClass0($name, $resolve)
    {
        /* @var $c Clazz */
        $name = (string) $name;
        $resolve = (bool) $resolve;
        
        // First, check if the class has already been loaded.
        $c = $this->findLoadedClass($name);
        if ($c === null) {
            $c = $this->findClass($name);
        }
        if ($resolve) {
            $this->resolveClass($c);
        }
        return $c;
    }

    /**
     * Finds and loads the class with the specified name from the included
     * paths. All included paths are searched as needed until the class is
     * found.
     *
     * @param string $name The FQCN name of the class.
     * @throws ClassNotFoundException if the class could not be found.
     * @return \ReflectionClass The resulting class
     */
    protected function findClass($name)
    {
        if (! $this->checkName($name)) {
            return null;
        }
        $path = str_replace(self::$NS_SEPARATOR, DIRECTORY_SEPARATOR, $name) . '.php';
        $url = $this->getCodeSourceURL($path);
        if ($url != null) {
            try {
                return $this->defineClass($name, $url);
            } catch (IOException $e) {
                throw new ClassNotFoundException($name, $e);
            }
        } else {
            throw new ClassNotFoundException($name);
        }
    }

    /**
     * Defines a Class using the class bytes obtained from the specified URL.
     *
     * @param string $name The fully qualified binary name of the class.
     * @param string $url The URL (or filename) of the class.
     * @return \ReflectionClass The resulting class
     * @throws IOException if the URL could not be read.
     */
    protected function defineClass($name, $url)
    {
        // Read the class bytes and define the class
        $success = include $url;
        if (! $success) {
            throw new IOException($name);
        }
        
        // Invoke static class constructor
        $rc = new \ReflectionClass($name);
        if($rc->hasMethod('clinit')) {
            $m = $rc->getMethod('clinit');
            $m->setAccessible(true);
            $m->invoke(null);
        }
        
        // Check if the package is loaded
        $i = strrpos($name, self::$NS_SEPARATOR);
        if ($i !== false) {
            $pkgName = substr($name, 0, $i);
            if (! array_key_exists($pkgName, $this->packages)) {
                $dir = $this->getPackageDirectory($url, $name);
                try {
                    // $this->definePackage( $pkgName, $dir );
                } catch (IllegalArgumentException $e) {
                    // The package is already defined.
                    $format = 'Sealing violation: [%s] package is sealed';
                    throw new IllegalStateException(sprintf($format, $pkgName));
                }
            }
        }
        
        return $this->defineClass0($name);
    }

    private function defineClass0($cname)
    {
        // Native java code
        try {
            $clazz = new \ReflectionClass($cname);
            $this->addClass($cname);
            return $clazz;
        } catch (\ReflectionException $e) {
            throw new InstantiationException($e->getMessage(), null, $e);
        }
    }

    private function getCodeSourceURL($fname)
    {
        foreach ($this->paths as $path) {
            $url = trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $url .= trim($fname, DIRECTORY_SEPARATOR);
            if (file_exists($url)) {
                return $url;
            }
        }
        return null;
    }

    private function getPackageDirectory($url, $cname)
    {
        $pos = strpos($url, $cname);
        return ($pos !== false) ? substr($url, 0, $pos - 1) : $url;
    }

    /**
     * Returns the class with the given binary name if this loader has been
     * recorded by the PHP autoload stack as an initiating loader of a class
     * with that binary name. Otherwise <code>null</code> is returned.
     *
     * @param string $name The binary name of the class.
     * @return \ReflectionClass The <code>Clazz</code> object, or
     *         <code>null</code> if the class has not been loaded.
     */
    protected function findLoadedClass($name)
    {
        if (! $this->checkName($name)) {
            return null;
        }
        return $this->findLoadedClass0($name);
    }

    private function findLoadedClass0($name)
    {
        // Native java code
        $size = count($this->classes);
        for ($i = 0; $i < $size; $i ++) {
            $c = $this->classes[$i];
            if ($c == $name) {
                return new \ReflectionClass($name);
            }
        }
        return null;
    }

    protected function resolveClass(Clazz $c)
    {
        $this->resolveClass0($c);
    }

    private function resolveClass0(Clazz $c)
    {
        // TODO Native java code.
        return $c;
    }

    /**
     * Returns true if the name is null or has the potential to be a valid
     * binary name.
     *
     * @param string $name
     * @return boolean
     */
    private function checkName($name)
    {
        if ($name == null || strlen($name) == 0) {
            return false;
        }
        
        // Test for illegal characters
        // $pattern = '/[^a-zA-Z0-9\\/\\\\_.:-]/i';
        $pattern = '#^[^A-Z_\x7f-\xff][^a-zA-Z0-9_\x7f-\xff]*$#';
        if (preg_match($pattern, $name)) {
            return false;
        }
        return true;
    }
    
    /* ---------- Package ---------- */
    
    /**
     * Defines a package name in this class loader. This allows class loaders to
     * define the packages for their classes. Packages must be created before
     * the class is defined, and package names must be unique within a class
     * loader and cannot be resolved or changed once created.
     *
     * @param string $name The package name.
     * @param string $directory The location of the package in the file system
     *            (include_path).
     * @throws IllegalArgumentException if a package name duplicates an existing
     *         package.
     * @return \KM\Lang\Package The newly defined object.
     */
    public function definePackage($name, $directory)
    {
        $name = (string) $name;
        $pkg = $this->getPackage($name);
        if ($pkg != null) {
            throw new IllegalArgumentException($name);
        }
        $pkg = new Package($name, $directory, $this);
        $this->packages[$name] = $pkg;
        return $pkg;
    }

    /**
     * Returns a package that has been defined by this class loader.
     *
     * @param string $name The package name
     * @return \KM\Lang\Package The package corresponding to the given name or
     *         null if not found.
     */
    public function getPackage($name)
    {
        return (! array_key_exists($name, $this->packages)) ? null : $this->packages[$name];
    }

    /**
     * Returns all of the packages defined by this class loader.
     *
     * @return \KM\Lang\Package[] The array of Package objects defined by this
     *         class loader.
     */
    public function getPackages()
    {
        $map = array();
        foreach ($this->packages as $pkgName => $pkg) {
            $map[$pkgName] = $pkg;
        }
        return $map;
    }
}
?>