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
namespace Slf4p\Helpers;

use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\Util\HashMap;
use KM\Util\Map;
use Slf4p\Marker;
use Slf4p\MarkerFactory;

/**
 * An almost trivial implementation of the MarkerFactory interface which creates
 * BasicMarker instances.
 *
 * @author Blair
 */
class BasicMarkerFactory extends Object implements MarkerFactory
{

    /**
     * The backing collection of Markers mapped by their name.
     *
     * @var Map
     */
    private $markerMap;

    /**
     * Regular users should not create BAsicMarkerFactory instances. Marker
     * instances can be obtained using the static
     * GenericMarkerFactory::getMarker() method.
     */
    public function __construct()
    {
        $this->markerMap = new HashMap('<string, \Slf4p\Marker>');
    }

    public function getMarker($name)
    {
        /* @var $marker Marker */
        if ($name == null) {
            throw new IllegalArgumentException('Marker name cannot be null');
        }
        $name = (string) $name;
        $marker = $this->markerMap->get($name);
        if ($marker == null) {
            $marker = new BasicMarker($name);
            $oldMarker = $this->markerMap->put($name, $marker);
            if ($oldMarker != null) {
                $marker = $oldMarker;
            }
        }
        return $marker;
    }

    public function exists($name)
    {
        if (empty($name)) {
            return false;
        }
        $name = (string) $name;
        return $this->markerMap->containsKey($name);
    }

    public function detachMarker($name)
    {
        if (empty($name)) {
            return false;
        }
        $name = (string) $name;
        return ($this->markerMap->remove($name) != null);
    }

    public function getDetachedMarker($name)
    {
        return new BasicMarker($name);
    }
}
?>