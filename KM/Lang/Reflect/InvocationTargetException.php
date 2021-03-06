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

use KM\IO\Serializable;

/**
 * InvocationTargetException is a checked exception that wraps an exception
 * thrown by an invoked method or constructor.
 *
 * @author Blair
 */
class InvocationTargetException extends \ReflectionException implements Serializable
{

    /**
     * The target exception
     *
     * @var \Exception
     */
    private $target;

    /**
     * Constructs an InvocationTargetException with the given target exception
     * and detail message.
     *
     * @param \Exception $target The given target exception.
     * @param string $message The detail message.
     */
    public function __construct(\Exception $target = null, $message = null)
    {
        parent::__construct($message, null, $target);
        $this->target = $target;
    }

    /**
     * Returns the thrown target exception.
     *
     * @return \Exception
     */
    public function getTarget()
    {
        return $this->target;
    }
}
?>