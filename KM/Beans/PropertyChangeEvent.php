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
namespace KM\Beans;

use KM\Util\EventObject;
use KM\Lang\Object;

/**
 * A "PropertyChange" event gets delivered whenever a bean changes a "bound" or
 * "constrained" property. A PropertyChangeEvent object is sent as an argument
 * to the PropertyChangeListener and VetoableChangeListener methods. <P>
 * Normally PropertyChangeEvents are accompanied by the name and the old and new
 * value of the changed property. <P> Null values may be provided for the old
 * and the new values if their true values are not known. <P> An event source
 * may send a null object as the name to indicate that an arbitrary set of if
 * its properties have changed. In this case the old and new values should also
 * be null.
 *
 * @author Blair
 */
class PropertyChangeEvent extends EventObject
{

    /**
     * The name of the property that changed. May be null if not known.
     *
     * @var string
     */
    private $propertyName;

    /**
     * New value for property. May be null if not known.
     *
     * @var mixed
     */
    private $newValue;

    /**
     * Previous value for property. May be null if not known.
     *
     * @var mixed
     */
    private $oldValue;

    /**
     * Constructs a new PropertyChangedEvent instance.
     *
     * @param Object $source
     * @param string $propertyName
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public function __construct(Object $source, $propertyName, $oldValue, $newValue)
    {
        parent::__construct($source);
        $this->propertyName = (string) $propertyName;
        $this->newValue = $newValue;
        $this->oldValue = $oldValue;
    }

    /**
     * Returns the name of the property that was changed.
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Returns the new value for the property.
     *
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * Returns the old value for the property.
     *
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     * @see \KM\Util\EventObject::__toString()
     */
    public function __toString()
    {
        $sb = $this->getClass()->getName();
        $sb .= '[propertyName=' . $this->propertyName;
        $sb .= '; oldValue=' . $this->getOldValue();
        $sb .= '; newValue=' . $this->getNewValue();
        $sb .= '; source=' . $this->getSource();
        $sb .= ']';
        return $sb;
    }
}
?>