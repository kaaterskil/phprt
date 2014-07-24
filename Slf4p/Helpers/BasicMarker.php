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
use KM\Util\Collections;
use KM\Util\ListInterface;
use KM\Util\Vector;
use Slf4p\Marker;

/**
 * A simple implementation of the Marker interface,
 *
 * @author Blair
 */
class BasicMarker extends Object implements Marker
{

    private static $OPEN = '[ ';

    private static $CLOSE = ' ]';

    private static $SEP = ', ';

    /**
     * The marker name.
     *
     * @var string
     */
    private $name;

    /**
     * A collection of marker references.
     *
     * @var ListInterface
     */
    private $referenceList;

    /**
     * Constructs an instance of Marker with the given name.
     *
     * @param string $name The name of the marker.
     * @throws IllegalArgumentException if the given name is null.
     */
    public function __construct($name)
    {
        if (empty($name)) {
            throw new IllegalArgumentException('A marker name cannot be null');
        }
        $this->name = (string) $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function add(Marker $reference)
    {
        // No point in adding the reference multiple times.
        if ($this->contains($reference)) {
            return;
        }
        if ($reference->contains($this)) {
            // Avoid recursion. A potential reference should not be its future
            // parent.
            return;
        }
        if ($this->referenceList == null) {
            $this->referenceList = new Vector('\Slf4p\Marker');
        }
        $this->referenceList->add($reference);
    }

    public function hasReferences()
    {
        return ($this->referenceList != null) && ($this->referenceList->size() > 0);
    }

    public function hasChildren()
    {
        return $this->hasReferences();
    }

    public function getIterator()
    {
        if ($this->referenceList != null) {
            return $this->referenceList->getIterator();
        }
        return Collections::emptyList()->getIterator();
    }

    public function remove(Marker $referenceToRemove)
    {
        /* @var $m Marker */
        if ($this->referenceList == null) {
            return false;
        }
        $size = $this->referenceList->size();
        for ($i = 0; $i < $size; $i ++) {
            $m = $this->referenceList->get($i);
            if ($referenceToRemove->equals($m)) {
                $this->referenceList->removeAt($i);
                return true;
            }
        }
        return false;
    }

    public function contains($nameOrOther)
    {
        if (empty($nameOrOther)) {
            throw new IllegalArgumentException('Other cannot be null');
        }
        if ($nameOrOther instanceof Marker) {
            return $this->containsMarker($nameOrOther);
        }
        return $this->containsName($nameOrOther);
    }

    private function containsMarker(Marker $other)
    {
        /* @var $ref Marker */
        if ($this->equals($other)) {
            return true;
        }
        if ($this->hasReferences()) {
            $size = $this->referenceList->size();
            for ($i = 0; $i < $size; $i ++) {
                $ref = $this->referenceList->get($i);
                if ($ref->contains($other)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function containsName($name)
    {
        /* @var $ref Marker */
        $name = (string) $name;
        if ($this->name == $name) {
            return true;
        }
        if ($this->hasReferences()) {
            $size = $this->referenceList->size();
            for ($i = 0; $i < $size; $i ++) {
                $ref = $this->referenceList->get($i);
                if ($ref->contains($name)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function equals(Object $obj = null)
    {
        /* @var $that Marker */
        if ($obj == null) {
            return false;
        }
        if ($obj === $this) {
            return true;
        }
        if (! $obj instanceof Marker) {
            return false;
        }
        $that = $obj;
        return $this->name == $that->getName();
    }

    public function __toString()
    {
        /* @var $reference Marker */
        if (! $this->hasReferences()) {
            return $this->getName();
        }
        
        $sb = $this->getName();
        $sb .= ' ' . self::$OPEN;
        $it = $this->getIterator();
        while ($it->hasNext()) {
            $reference = $it->next();
            $sb .= $reference->getName();
            if ($it->hasNext()) {
                $sb .= self::$SEP;
            }
        }
        $sb .= self::$CLOSE;
        return $sb;
    }
}
?>