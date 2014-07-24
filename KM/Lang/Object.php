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

use KM\Entity\PersistentEntity;
use KM\Lang\Clazz;
use KM\Util\Date;

/**
 * Class Object is the root of the class hierarchy. Every class has Object as a
 * superclass.
 *
 * @author Blair
 */
class Object
{

    /**
     * Returns the runtime class of this <code>Object</code>.
     *
     * @return \KM\Lang\Clazz The <code>Class</code> object that represents the
     *         runtime class of this object.
     */
    public static final function clazz()
    {
        return Clazz::forName(get_called_class());
    }

    /**
     * Returns the runtime class of this Object.
     *
     * @return \KM\Lang\Clazz The <code>Class</code> object that represents the
     *         runtime class of this object.
     */
    public final function getClass()
    {
        return Clazz::forName(get_class($this));
    }

    /**
     * Returns a hash code value for the object.
     *
     * @return string A hash code value for this object.
     */
    public function hashCode()
    {
        return spl_object_hash($this);
    }

    /**
     * Indicates whether some other object is "equal to" this one. The
     * <code>equals</code> method implements an equivalence relation on non-null
     * object references:
     * <ul> <li>It is <i>reflexive</i>: for any non-null reference value
     * <code>x</code>, <code>x.equals(x)</code> should return <code>true</code>.
     * <li>It is <i>symmetric</i>: for any non-null reference values
     * <code>x</code> and <code>y</code>, <code>x.equals(y)</code> should return
     * <code>true</code> if and only if <code>y.equals(x)</code> returns
     * <code>true</code>. <li>It is <i>transitive</i>: for any non-null
     * reference values <code>x</code>, <code>y</code>, and <code>z</code>, if
     * <code>x.equals(y)</code> returns <code>true</code> and
     * <code>y.equals(z)</code> returns <code>true</code>, then
     * <code>x.equals(z)</code> should return <code>true</code>. <li>It is
     * <i>consistent</i>: for any non-null reference values <code>x</code> and
     * <code>y</code>, multiple invocations of <code>x.equals(y)</code>
     * consistently return <code>true</code> or consistently return
     * <code>false</code>, provided no information used in <code>equals</code>
     * comparisons on the objects is modified. <li>For any non-null reference
     * value <code>x</code>, <code>x.equals(null)</code> should return
     * <code>false</code>. </ul>
     * The <code>equals</code> method for class <code>Object</code> implements
     * the most discriminating possible equivalence relation on objects; that
     * is, for any non-null reference values <code>x</code> and <code>y</code>,
     * this method returns <code>true</code> if and only if <code>x</code> and
     * <code>y</code> refer to the same object (<code>x == y</code> has the
     * value <code>true</code>).
     * Note that it is generally necessary to override the <code>hashCode</code>
     * method whenever this method is overridden, so as to maintain the general
     * contract for the <code>hashCode</code> method, which states that equal
     * objects must have equal hash codes.
     *
     * @param Object $obj The reference object with which to compare.
     * @return boolean <code>True</code> if this object is the same as the obj
     *         argument, <code>false</code> otherwise.
     */
    public function equals(Object $obj = null)
    {
        return ($this === $obj);
    }

    /**
     * Returns a string representation of the object. In general, the toString
     * method returns a string that "textually represents" this object. The
     * result should be a concise but informative representation that is easy
     * for a person to read. It is recommended that all subclasses override this
     * method.
     *
     * @return string
     */
    public function __toString()
    {
        /* @var $property \ReflectionProperty */
        $clazz = $this->getClass();
        try {
            $result = $clazz->getShortName() . '@' . $this->hashCode() . '[';
            foreach ($clazz->getFields() as $property) {
                $property->setAccessible(true);
                
                $name = $property->getName();
                $value = 'null';
                
                $propertyValue = $property->get($this);
                if (! empty($propertyValue)) {
                    if (is_scalar($propertyValue)) {
                        $value = $propertyValue;
                    } elseif (is_array($propertyValue)) {
                        $value = print_r($propertyValue);
                    } elseif ($propertyValue instanceof PersistentEntity) {
                        $value = $propertyValue->getId();
                    } elseif ($propertyValue instanceof Date) {
                        $value = $propertyValue->format('Y-m-d H:i:s');
                    } elseif ($propertyValue instanceof \Traversable) {
                        $value = 'Collection-count(' . $propertyValue->size() . ')';
                    } else {
                        $value = (string) $propertyValue;
                    }
                }
                $result .= "\n\t" . $name . '=' . $value;
            }
            $result .= "\n]";
        } catch (\Exception $e) {
            $result = $e->getTraceAsString();
        }
        return $result;
    }
}
?>