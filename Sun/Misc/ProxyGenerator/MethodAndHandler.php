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
namespace Sun\Misc\ProxyGenerator;

use KM\Lang\Reflect\Method;
use KM\Lang\Reflect\InvocationHandler;

/**
 * MethodAndHandler Class
 *
 * @author Blair
 */
class MethodAndHandler
{

    /**
     * The reflected method.
     *
     * @var Method
     */
    private $method;

    /**
     * The method handler.
     *
     * @var InvocationHandler
     */
    private $handler;

    /**
     * Constructs a new instance of this class with the given
     * <code>Method</code> and <code>InvocationHandler</code> objects.
     *
     * @param Method $method The <code>Method</code> object representing the
     *            reflected method.
     * @param InvocationHandler $handler The <code>InvocationHandler</code>
     *            object representing the method hook.
     */
    public function __construct(Method $method, InvocationHandler $handler)
    {
        $this->method = $method;
        $this->handler = $handler;
    }

    /**
     * Returns the reflected method.
     *
     * @return \KM\Lang\Reflect\Method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the method invocation handler.
     *
     * @return \KM\Lang\Reflect\InvocationHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }
}
?>