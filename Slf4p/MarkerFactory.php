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
namespace Slf4p;

use Slf4p\Marker;

/**
 * Implementations of this interface are used to manufacture Markers instances.
 *
 * @author Blair
 */
interface MarkerFactory
{

    /**
     * Manufacture a marker instance by name. If the instance has been created
     * earlier, return the previously created instance> Null name values are not
     * allowed.
     *
     * @param string $name The name of the marker to be created.
     * @return Marker A Marker instance.
     */
    public function getMarker($name);

    /**
     * Checks if the marker with the given name already exists. If the name is
     * null, then false is returned.
     *
     * @param string $name Logger name to check for.
     * @return boolean True if the marker exists, false otherwise.
     */
    public function exists($name);

    /**
     * Detach an existing marker. Note that after a marker is detached, there
     * might still be 'dangling' references to the detached marker.
     *
     * @param string $name The name of the marker to detach.
     * @return boolean Whether the marker could be detached or not.
     */
    public function detachMarker($name);

    /**
     * Create a marker which is detached (even at birth) from this
     * MarkerFactory.
     *
     * @param string $name The marker name.
     * @return Marker A dangling marker,
     */
    public function getDetachedMarker($name);

    /**
     * Returns the reflection class of the MarkerFactory instance.
     *
     * @return \KM\Lang\Clazz
     */
    public function getClass();
}
?>