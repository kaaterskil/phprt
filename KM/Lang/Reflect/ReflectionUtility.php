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
namespace KM\Lang\Reflect;

use KM\Lang\ClassLoader;
use KM\Lang\Clazz;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Lang\Reflect\Field;
use KM\Lang\Void;
use KM\Lang\System;
use KM\Lang\ClassNotFoundException;
use KM\Lang\InitializerException;
use KM\Lang\InstantiationException;
use KM\Lang\IllegalArgumentException;

/**
 * ReflectionUtility Class
 *
 * @author Blair
 */
class ReflectionUtility
{

    /**
     * This class cannot be instantiated.
     */
    private function __construct()
    {}

    public static function cast(Object $object, Clazz $newClazz)
    {
        /* @var $fs Field */
		/* @var $fd Field */
		$destination = $newClazz->newInstanceWithoutConstructor();
        foreach ($object->getClass()->getFields() as $fs) {
            $fs->setAccessible(true);
            $name = $fs->getName();
            $value = $fs->getValue($object);
            if ($newClazz->hasProperty($name)) {
                $fd = $newClazz->getField($name);
                $fd->setAccessible(true);
                $fd->setValue($destination, $value);
            } else {
                $destination->$name = $value;
            }
        }
        return $destination;
    }

    /**
     * Returns the Type object representing the given type name.
     *
     * @param string $name The name of the primitive type of class to reflect.
     * @throws NullPointerException if the given <code>name</code> is
     *         <code>null</code>.
     * @throws IllegalArgumentException if the name is not a valid type.
     * @throws InstantiationException if the given <code>name</code> is a class
     *         name and an instantiation error of its given class object
     *         occurred.
     * @return \KM\Lang\Reflect\Type The <code>Type</code> object representing
     *         the given type name.
     */
    public static function typeFor($name)
    {
        if (empty($name)) {
            throw new NullPointerException();
        }
        
        $name = self::resolvePrimitiveName($name);
        if (PrimitiveType::isKnown($name)) {
            return PrimitiveType::value(strtoupper($name));
        } elseif (MixedType::isKnown($name)) {
            return MixedType::value(strtoupper($name));
        } elseif (ArrayType::isKnown($name)) {
            return new ArrayType($name);
        } elseif (strtolower($name) == 'void' or strtolower($name) == 'null') {
            return Void::clazz();
        }
        
        try {
            $name = self::resolveClassName($name);
            return Clazz::forName($name);
        } catch (InitializerException $e) {
            throw new InstantiationException($name);
        } catch (ClassNotFoundException $cnfe) {
            throw new IllegalArgumentException('class not found ' . $name, null,
                $cnfe);
        }
    }

    /**
     * Resolves alternative primitive names to their type.
     * More specifically, PHP accepts 'bool' and 'boolean' to designate a
     * boolean type, and 'int' and 'integer' to designate an integer type.
     *
     * <p>Use of the keyword 'array' is prohibited so the type name
     * 'array_type' will designate an array type. Names that include the
     * substrings 'array' or '[]' will be considered to designate an array
     * type.
     *
     * <p>The name 'unknown' will be considered a fallback to designate a string
     * type. The name 'number' will be considered a fallback to designate an
     * integer type.
     *
     * @param string $name The name to resolve.
     * @return string The resolved primitive name, or the original name if not
     *         a specified alternative name.
     */
    private static function resolvePrimitiveName($name)
    {
        if (strpos($name, 'bool') === 0) {
            $name = 'boolean';
        } elseif ((strpos($name, 'int') === 0)) {
            $name = 'integer';
        } elseif ($name == 'unknown') {
            $name = 'string';
        }
        return $name;
    }

    /**
     * Attempts to resolve the given class name.
     * If <code>name</code> is prefixed with the root namespace identifier '\',
     * the value is immediately returned. Otherwise, the method will compare the
     * given name with PHP's declared class names and return the first matching
     * name. If no match is found, a ClassNotFoundException is thrown.
     *
     * @param string $name The class name to resolve.
     * @throws ClassNotFoundException if no match is found.
     * @return string The fully qualified class name.
     */
    private static function resolveClassName($name)
    {
        // Test for FQCN.
        if ($name[0] == '\\') {
            return $name;
        }
        foreach (get_declared_classes() as $c) {
            if (strpos(strtolower($c), strtolower($name)) !== false) {
                return $c;
            }
        }
        throw new ClassNotFoundException(
            'could not resolve class name: ' . $name);
    }

    /**
     * Returns the Type of the given value.
     *
     * @param mixed $value The primitive value or object to reflect.
     * @throws NullPointerException if the given <code>value</code> is
     *         <code>null</code>.
     * @return \KM\Lang\Reflect\Type
     */
    public static function typeForValue($value)
    {
        /* @var $obj Object */
        if ($value === null) {
            throw new NullPointerException();
        }
        if ($value instanceof Object) {
            $obj = $value;
            return $obj->getClass();
        } elseif (is_object($value)) {
            return Clazz::forName(get_class($value));
        } elseif (is_array($value)) {
            $ct = PrimitiveType::STRING();
            foreach ($value as $v) {
                if ($v != null) {
                    $ct = self::typeForValue($v);
                    break;
                }
            }
            return new ArrayType($ct->getTypeName() . '[]');
        }
        return self::typeFor(gettype($value));
    }

    /**
     * Returns an array of <code>Type</code> objects from the given array or
     * comma-delimited list of parameter types.
     * The given comma-delimited list
     * may be enclosed in angle brackets or not.
     *
     * @param mixed $typeParameters An array of string values or a
     *        comma-delimited string of values with or without enclosing
     *        angle brackets.
     * @param int $numRequired THe number of required type parameters.
     * @return \KM\Lang\Reflect\Type[] An array of <code>Type</code> objects
     *         representing the types defined by the generic declaration.
     * @throws GenericSignatureFormatError if the number of given type
     *         parameters does not equal the required number of type parameters.
     */
    public static function parseTypeParameters($typeParameters, $numRequired = 1)
    {
        if (empty($typeParameters)) {
            return array();
        }
        
        $types = self::parseTypeParameters0($typeParameters);
        if (count($types) != $numRequired) {
            throw new GenericSignatureFormatError();
        }
        
        $returnValue = array();
        foreach ($types as $type) {
            $returnValue[] = self::typeFor(trim($type));
        }
        return $returnValue;
    }

    private static function parseTypeParameters0($typeParameters)
    {
        $types = [];
        if (is_string($typeParameters)) {
            $types = explode(',', trim($typeParameters, '< >'));
        } elseif (is_array($typeParameters)) {
            $size = count($typeParameters);
            $a = [];
            for ($i = 0; $i < $size; $i++) {
                $a[] = self::parseTypeParameters0($typeParameters[$i]);
            }
            $types = $a;
        } elseif (is_object($typeParameters)) {
            if ($typeParameters instanceof Type) {
                $sb = $typeParameters->getTypeName();
            } else {
                $sb = get_class($typeParameters);
            }
            $types[] = $sb;
        } else {
            $types[] = $typeParameters;
        }
        return $types;
    }

    public static function printModifiersIfNonZero($modifierMask)
    {
        return ($modifierMask == 0) ? '' : implode(' ',
            \Reflection::getModifierNames($modifierMask)) . ' ';
    }

    public static function getParameterNames(array $paramters)
    {
        /* @var $parameter Parameter */
        $names = array();
        foreach ($paramters as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof Clazz) {
                $typeName = $type->getShortName();
            } else {
                $typeName = $type->getTypeName();
            }
            $names[] = $typeName . ' $' . $parameter->getName();
        }
        return '(' . implode(', ', $names) . ')';
    }
}
?>