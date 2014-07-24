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

use KM\IO\Serializable;
use KM\Lang\Clazz;
use KM\IO\InvalidObjectException;

/**
 * This is the common base class of all enumeration types.
 *
 * @author Blair
 */
abstract class Enum extends Object implements Comparable, Serializable
{

    /**
     * The current called class name.
     *
     * @var string
     */
    private static $calledClass;

    /**
     * An associative array containing the textual name of the enum constant as
     * the key and the singleton instance of the constant as the value.
     *
     * @var Enum[]
     */
    protected static $enumConstantDirectory = array();

    /**
     * An array of enum instances.
     *
     * @var Enum[]
     */
    protected static $enumConstants = array();

    /**
     * Magic method that returns the enum constant matching the method name, or
     * null if there is no match.
     *
     * @param string $name the method name, being the name of the enum constant
     *            and not the value of the enum constant.
     * @param mixed $args the method arguments, which in this case are null
     * @throws IllegalArgumentException if the supplied name is not a valid
     *         constant name for this enum type.
     * @return \KM\Lang\Enum
     */
    public static function __callstatic($name, $args)
    {
        $directory = self::getEnumConstantDirectory();
        if (! isset($directory[$name])) {
            $format = 'No Enum constant {%s}';
            throw new IllegalArgumentException(sprintf($format, $name));
        }
        return $directory[$name];
    }

    /**
     * Returns the elements of this Enum class or an empty array.
     *
     * @return \KM\Lang\Enum[]
     */
    public static function getEnumConstants()
    {
        /* @var $result Enum[] */
        $values = self::getEnumConstantsShared();
        $result = array();
        foreach ($values as $value) {
            $result[] = $value;
        }
        return $result;
    }

    /**
     * Returns the elements of this Enum class or an empty array. Identical to
     * getEnumConstants() except that the result is not cloned and shared by all
     * callers. Note that the array returned by this method is created lazily on
     * first use.
     *
     * @return \KM\Lang\Enum[]
     */
    protected static function getEnumConstantsShared()
    {
        $calledClass = get_called_class();
        if (self::$enumConstants == null || count(self::$enumConstants) == 0 || self::$calledClass != $calledClass) {
            
            $clazz = new \ReflectionClass($calledClass);
            $constants = $clazz->getConstants();
            $className = $clazz->getName();
            
            $m = array();
            $i = 0;
            foreach ($constants as $key => $value) {
                $m[] = new $className($key, $value, $i ++);
            }
            self::$enumConstants = $m;
            self::$calledClass = $calledClass;
        }
        return self::$enumConstants;
    }

    /**
     * Returns a map from simple name to enum constant. Note that the map
     * returned by this method is created lazily on first use.
     *
     * @throws IllegalArgumentException if no constants exist
     * @return array
     */
    protected static function getEnumConstantDirectory()
    {
        $calledClass = get_called_class();
        if (self::$enumConstantDirectory == null || count(self::$enumConstantDirectory) == 0 ||
             self::$calledClass != $calledClass) {
            
            $universe = self::getEnumConstantsShared();
            if (empty($universe)) {
                throw new IllegalArgumentException('No enum constants found');
            }
            $m = array();
            foreach ($universe as $constant) {
                $name = $constant->getName();
                $m[$name] = $constant;
            }
            self::$enumConstantDirectory = $m;
        }
        return self::$enumConstantDirectory;
    }

    /**
     * Returns an associative array with the enum values as a list of key =>
     * value pairs.
     *
     * @return string[]
     */
    public static function toArray()
    {
        /* @var $result string[] */
        $clazz = new \ReflectionClass(get_called_class());
        $constants = $clazz->getConstants();
        
        $result = array();
        foreach ($constants as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Returns the enum constant of the current class that matches the given
     * name.
     *
     * @param string $name
     * @throws NullPointerException if $name is null
     * @throws IllegalArgumentException if the specified Enum type has no
     *         constant with the specified $name.
     * @return \KM\Lang\Enum
     */
    public static function value($name)
    {
        $enumType = get_called_class();
        return self::valueOf($enumType, $name);
    }

    /**
     * Returns the enum constant of the specified enum type with the specified
     * name. The name must match exactly an identifier used to declare an enum
     * constant in this type. (Extraneous whitespace characters are not
     * permitted.)
     *
     * @param string $enumType
     * @param string $name
     * @throws NullPointerException if $enumType or $name is null
     * @throws IllegalArgumentException if the specified $enumType is not a
     *         valid class name or does not represent an Enum type, or if the
     *         specified Enum type has no constant with the specified $name.
     * @return \KM\Lang\Enum
     */
    public static function valueOf($enumType, $name)
    {
        /* @var $result Enum */
        if (empty($enumType)) {
            throw new NullPointerException('Enum type is null');
        }
        if (empty($name)) {
            throw new NullPointerException('Name is null');
        }
        try {
            $clazz = new \ReflectionClass($enumType);
            if (! $clazz->isSubclassOf('\KM\Lang\Enum')) {
                throw new IllegalArgumentException('Passed type is not an Enum type');
            }
            
            $method = $clazz->getMethod('getEnumConstantDirectory');
            $method->setAccessible(true);
            $directory = $method->invoke(null); // Null passed for static
                                                // method
            if (! isset($directory[$name])) {
                throw new IllegalArgumentException('No Enum constant ' . $enumType . '.' . $name);
            }
            return $directory[$name];
        } catch (\ReflectionException $re) {
            throw new IllegalArgumentException($re->getMessage(), $re->getCode(), $re);
        }
    }

    /**
     * The name of this enum constant, as declared in the enum declaration. Most
     * programmers should use the {@link #toString} method rather than accessing
     * this field.
     *
     * @var string
     */
    private $name;

    /**
     * The value of the enum constant.
     *
     * @var mixed
     */
    private $value;

    /**
     * The ordinal of this enumeration constant (its position in the enum
     * declaration, where the initial constant is assigned an ordinal of zero).
     * Most programmers will have no use for this field. It is designed for use
     * by sophisticated enum-based data structures.
     *
     * @var int
     */
    private $ordinal;

    /**
     * Sole constructor. Programmers should not invoke this constructor.
     *
     * @param string $name The name of the Enum constant.
     * @param mixed $value The value of the Enum constant.
     * @param int $ordinal
     */
    protected function __construct($name, $value, $ordinal)
    {
        $this->name = $name;
        $this->ordinal = (int) $ordinal;
        $this->value = $value;
    }

    /**
     * Returns the name of this enum constant, exactly as declared in its enum
     * declaration. <b>Most programmers should use the <code>toString</code>
     * method in preference to this one, as the toString method may return a
     * more user-friendly name.</b> This method is designed primarily for use in
     * specialized situations where correctness depends on getting the exact
     * name, which will not vary from release to release.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the ordinal of this enumeration constant (its position in its
     * enum declaration, where the initial constant is assigned an ordinal of
     * zero).
     *
     * @return int
     */
    public function getOrdinal()
    {
        return $this->ordinal;
    }

    /**
     * The value of the enumeration constant.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the name of this enum constant, as contained in the declaration.
     * This method may be overridden, though it typically isn't necessary or
     * desirable. An enum type should override this method when a more
     * "programmer-friendly" string form exists.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Returns true if the specified object is equal to this enum constant.
     *
     * @param Object $other
     * @return boolean
     */
    public function equals(Object $other = null)
    {
        if (! $other instanceof Enum) {
            return false;
        }
        return $this === $other;
    }

    /**
     * Compares this enum with the specified object for order. Returns a
     * negative integer, zero, or a positive integer as this object is less
     * than, equal to, or greater than the specified object. Enum constants are
     * only comparable to other enum constants of the same enum type. The
     * natural order implemented by this method is the order in which the
     * constants are declared.
     *
     * @param Object $o The object to compare to.
     * @throws ClassCastException If the specified object is not a Enum.
     * @return int
     * @see \KM\Lang\Comparable::compareTo()
     */
    public function compareTo(Object $o = null)
    {
        /* @var $other Enum */
        /* @var $self Enum */
        if ($o === null) {
            return 1;
        }
        $self = $this;
        $other = $o;
        // Optimize with native code
        if ((get_class($self) != get_class($other)) && (get_called_class($self) != get_called_class($other))) {
            throw new ClassCastException();
        }
        return $self->ordinal - $other->ordinal;
    }

    /**
     * Returns the reflection class object corresponding to this enum constant's
     * enum type. The value returned by this method may differ from the one
     * returned by the getClass() method for enum constants.
     *
     * @return \KM\Lang\Clazz The Class object corresponding to this enum
     *         constant's enum type
     */
    public function getDeclaredClass()
    {
        return Clazz::forName(get_called_class());
    }
    
    /*
     * Prevent default serialization
     */
    private function writeObject(\KM\IO\ObjectOutputStream $outputStream)
    {
        throw new InvalidObjectException('cannot serialize enum');
    }

    private function readObject(\KM\IO\ObjectInputStream $inputStream)
    {
        throw new InvalidObjectException('cannot deserialize enum');
    }
}
?>