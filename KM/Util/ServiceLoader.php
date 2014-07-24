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
namespace KM\Util;

use KM\Lang\Clazz;
use KM\Lang\Object;
use KM\Util\HashMap;
use KM\Util\Map;
use KM\Util\ServiceLoader\LazyIterator;
use KM\Util\ServiceLoader\Itr;

/**
 * A simple service-provider loading facility.
 *
 * <p> A <i>service</i> is a well-known set of interfaces and (usually
 * abstract) classes. A <i>service provider</i> is a specific implementation
 * of a service. The classes in a provider typically implement the interfaces
 * and subclass the classes defined in the service itself. Service providers
 * can be installed in an implementation of the Java platform in the form of
 * extensions, that is, jar files placed into any of the usual extension
 * directories. Providers can also be made available by adding them to the
 * application's class path or by some other platform-specific means.
 *
 * <p> For the purpose of loading, a service is represented by a single type,
 * that is, a single interface or abstract class. (A concrete class can be
 * used, but this is not recommended.) A provider of a given service contains
 * one or more concrete classes that extend this <i>service type</i> with data
 * and code specific to the provider. The <i>provider class</i> is typically
 * not the entire provider itself but rather a proxy which contains enough
 * information to decide whether the provider is able to satisfy a particular
 * request together with code that can create the actual provider on demand.
 * The details of provider classes tend to be highly service-specific; no
 * single class or interface could possibly unify them, so no such type is
 * defined here. The only requirement enforced by this facility is that
 * provider classes must have a zero-argument constructor so that they can be
 * instantiated during loading.
 *
 * <p><a name="format"> A service provider is identified by placing a
 * <i>provider-configuration file</i> in the resource directory
 * <tt>META-INF/services</tt>.</a> The file's name is the fully-qualified <a
 * href="../lang/ClassLoader.html#name">binary name</a> of the service's type.
 * The file contains a list of fully-qualified binary names of concrete
 * provider classes, one per line. Space and tab characters surrounding each
 * name, as well as blank lines, are ignored. The comment character is
 * <tt>'#'</tt> (<tt>'&#92;u0023'</tt>,
 * <font style="font-size:smaller;">NUMBER SIGN</font>); on
 * each line all characters following the first comment character are ignored.
 * The file must be encoded in UTF-8.
 *
 * <p> If a particular concrete provider class is named in more than one
 * configuration file, or is named in the same configuration file more than
 * once, then the duplicates are ignored. The configuration file naming a
 * particular provider need not be in the same jar file or other distribution
 * unit as the provider itself. The provider must be accessible from the same
 * class loader that was initially queried to locate the configuration file;
 * note that this is not necessarily the class loader from which the file was
 * actually loaded.
 *
 * <p> Providers are located and instantiated lazily, that is, on demand. A
 * service loader maintains a cache of the providers that have been loaded so
 * far. Each invocation of the <code>iterator</code> method returns an
 * iterator that first yields all of the elements of the cache, in
 * instantiation order, and then lazily locates and instantiates any remaining
 * providers, adding each one to the cache in turn. The cache can be cleared
 * via the <code>reload reload</code> method.
 *
 * <p> Service loaders always execute in the security context of the caller.
 * Trusted system code should typically invoke the methods in this class, and
 * the methods of the iterators which they return, from within a privileged
 * security context.
 *
 * <p> Instances of this class are not safe for use by multiple concurrent
 * threads.
 *
 * <p> Unless otherwise specified, passing a <tt>null</tt> argument to any
 * method in this class will cause a <code>NullPointerException</code> to be
 * thrown.
 *
 *
 * <p><span style="font-weight: bold; padding-right: 1em">Example</span>
 * Suppose we have a service type <tt>com.example.CodecSet</tt> which is
 * intended to represent sets of encoder/decoder pairs for some protocol. In
 * this case it is an abstract class with two abstract methods:
 *
 * <blockquote><pre>
 * public abstract Encoder getEncoder(String encodingName);
 * public abstract Decoder getDecoder(String encodingName);</pre></blockquote>
 *
 * Each method returns an appropriate object or <tt>null</tt> if the provider
 * does not support the given encoding. Typical providers support more than
 * one encoding.
 *
 * <p> If <tt>com.example.impl.StandardCodecs</tt> is an implementation of the
 * <tt>CodecSet</tt> service then its jar file also contains a file named
 *
 * <blockquote><pre>
 * META-INF/services/com.example.CodecSet</pre></blockquote>
 *
 * <p> This file contains the single line:
 *
 * <blockquote><pre>
 * com.example.impl.StandardCodecs # Standard codecs</pre></blockquote>
 *
 * <p> The <tt>CodecSet</tt> class creates and saves a single service instance
 * at initialization:
 *
 * <blockquote><pre>
 * private static ServiceLoader&lt;CodecSet&gt; codecSetLoader
 * = ServiceLoader.load(CodecSet.class);</pre></blockquote>
 *
 * <p> To locate an encoder for a given encoding name it defines a static
 * factory method which iterates through the known and available providers,
 * returning only when it has located a suitable encoder or has run out of
 * providers.
 *
 * <blockquote><pre>
 * public static Encoder getEncoder(String encodingName) {
 * for (CodecSet cp : codecSetLoader) {
 * Encoder enc = cp.getEncoder(encodingName);
 * if (enc != null)
 * return enc;
 * }
 * return null;
 * }</pre></blockquote>
 *
 * <p> A <tt>getDecoder</tt> method is defined similarly.
 *
 *
 * <p><span style="font-weight: bold; padding-right: 1em">Usage Note</span> If
 * the class path of a class loader that is used for provider loading includes
 * remote network URLs then those URLs will be dereferenced in the process of
 * searching for provider-configuration files.
 *
 * <p> This activity is normal, although it may cause puzzling entries to be
 * created in web-server logs. If a web server is not configured correctly,
 * however, then this activity may cause the provider-loading algorithm to fail
 * spuriously.
 *
 * <p> A web server should return an HTTP 404 (Not Found) response when a
 * requested resource does not exist. Sometimes, however, web servers are
 * erroneously configured to return an HTTP 200 (OK) response along with a
 * helpful HTML error page in such cases. This will cause a
 * <code>ServiceConfigurationError</code> to be thrown when this class attempts
 * to parse the HTML page as a provider-configuration file. The best solution to
 * this problem is to fix the misconfigured web server to return the correct
 * response code (HTTP 404) along with the HTML error page.
 *
 * @author Blair
 */
final class ServiceLoader extends Object implements \IteratorAggregate
{

    /**
     * Creates a new service loader for the given service type.
     *
     * @param Clazz $service The Class of the service type.
     * @return \KM\Util\ServiceLoader A new service loader.
     */
    public static function load(Clazz $service)
    {
        return new self($service);
    }

    /**
     * The Class representing the service to be loaded.
     *
     * @var Clazz
     */
    private $service;

    /**
     * Cached providers, in instantiation order.
     *
     * @var Map
     */
    public $providers;

    /**
     * The current lazy-lookup iterator
     *
     * @var LazyIterator
     */
    private $lookupIterator;

    /**
     * Clears this loader's provider cache so that all providers will be
     * reloaded.
     * After invoking this method, subsequent invocations of the iterator()
     * method will lazily look up and instantiate providers from scratch, just
     * as is done by a newly created loader.
     */
    public function reload()
    {
        $this->providers->clear();
        $this->lookupIterator = new LazyIterator($this->service, $this);
    }

    private function __construct(Clazz $service)
    {
        $this->providers = new HashMap('<string, \KM\Lang\Object>');
        $this->service = $service;
        $this->reload();
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function getLookupIterator()
    {
        return $this->lookupIterator;
    }

    /**
     * Parse the content of a given filename as a provider-configuration file.
     *
     * @param string $fname The filename for the configuration file to be
     *        parsed.
     * @throws IOException
     * @return \KM\Util\Iterator A possibly empty iterator that will yield the
     *         provider class
     *         names in the given configuration file that are not yet members of
     *         the returned set.
     */
    public function parse($fname)
    {
        $names = new ArrayList('string');
        
        $text = null;
        try {
            $text = file_get_contents($fname);
            if ($text === false) {
                throw new IOException();
            }
        } catch (IOException $e) {
            trigger_error('Error reading configuration file.');
        }
        
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = str_replace("\r", '', $line);
            $line = str_replace("\t", '', $line);
            $line = trim($line);
            if (empty($line) || (strpos($line, '#') === 0)) {
                continue;
            }
            $cname = $line;
            if (!$this->providers->containsKey($cname) &&
                 !$names->contains($cname)) {
                $names->add($cname);
            }
        }
        return $names->getIterator();
    }

    /**
     * Lazily loads the available providers of this loader's service.
     *
     * <p> The iterator returned by this method first yields all of the
     * elements of the provider cache, in instantiation order. It then lazily
     * loads and instantiates any remaining providers, adding each one to the
     * cache in turn.
     *
     * <p> To achieve laziness the actual work of parsing the available
     * provider-configuration files and instantiating providers must be done by
     * the iterator itself. Its <code>hasNext hasNext</code> and
     * <code>next</code> methods can therefore throw a
     * <code>ServiceConfigurationError</code> if a provider-configuration file
     * violates the specified format, or if it names a provider class that
     * cannot be found and instantiated, or if the result of instantiating the
     * class is not assignable to the service type, or if any other kind of
     * exception or error is thrown as the next provider is located and
     * instantiated. To write robust code it is only necessary to catch
     * <code>ServiceConfigurationError</code> when using a service iterator.
     *
     * <p> If such an error is thrown then subsequent invocations of the
     * iterator will make a best effort to locate and instantiate the next
     * available provider, but in general such recovery cannot be guaranteed.
     *
     * <blockquote><span>Design Note</span>
     * Throwing an error in these cases may seem extreme. The rationale for
     * this behavior is that a malformed provider-configuration file, like a
     * malformed class file, indicates a serious problem with the way the Java
     * virtual machine is configured or is being used. As such it is
     * preferable to throw an error rather than try to recover or, even worse,
     * fail silently.</blockquote>
     *
     * <p> The iterator returned by this method does not support removal.
     * Invoking its <code>remove()</code> method will
     * cause an <code>UnsupportedOperationException</code> to be thrown.
     *
     * @implNote When adding providers to the cache, the <code> Iterator</code>
     * processes resources in the order that the <code>
     * ClassLoader.getResources(String)</code> method finds the service
     * configuration files.
     *
     * @return \KM\Util\Iterator An iterator that lazily loads providers for
     *         this loader's service
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new Itr($this);
    }
}
?>