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

use KM\Lang\IllegalArgumentException;
use KM\Lang\Object;
use KM\Util\Iterator;

/**
 * Markers are named objects used to enrich log statements. Conforming logging
 * system implementations of slf4p determine how information conveyed by markers
 * are used, if at all. In particular, many conforming logging systems ignore
 * marker data.
 *
 * @author Blair
 */
interface Marker
{

    /**
     * THis constant represents any marker, including a null marker.
     *
     * @var string
     */
    const ANY_MARKER = '*';

    /**
     * This constant represents any non-null marker.
     *
     * @var string
     */
    const ANY_NON_NULL_MARKER = '+';

    /**
     * Returns the name of this Marker
     *
     * @return string
     */
    public function getName();

    /**
     * Add a reference to another Marker.
     *
     * @param Marker $reference The marker reference to remove.
     * @throws IllegalArgumentException if $reference is null.
     */
    public function add(Marker $reference);

    /**
     * Removes a marker reference.
     *
     * @param Marker $reference A Reference to another marker.
     * @return boolean True if the given reference could be found and removed,
     *         false otherwise.
     */
    public function remove(Marker $reference);

    /**
     * Returns true if this marker has references, false otherwise.
     *
     * @return boolean True if this marker has one or more references, false
     *         otherwise.
     */
    public function hasReferences();

    /**
     * Returns an iterator which can be used to iterate over the references of
     * this marker. An empty iterator is returned when this marker has no
     * references.
     *
     * @return Iterator over the references of this Marker.
     */
    public function getIterator();

    /**
     * Does this marker contain the given Marker or Marker name? Marker A is
     * defined to contain marker B, if A == B or if B is referenced by A, or if
     * B is referenced by any one of A's references (recursively).
     *
     * @param Marker|string $stringOrMarker THe string name of the marker or the
     *            marker itself to test for inclusion.
     * @return boolean Whether this marker contains the other marker.
     * @throws IllegalArgumentException if 'other' is null.
     */
    public function contains($nameOrOther);
}
?>