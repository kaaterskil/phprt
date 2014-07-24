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

use KM\Lang\Object;

/**
 * InvocationHandler is the interface implemented by the invocation handler of a
 * proxy instance.
 *
 * @author Blair
 */
interface InvocationHandler
{

    /**
     * Processes a method invocation on a proxy instance and returns the result.
     * This method will be invoked on an invocation handler when a method is
     * invoked on a proxy instance that it is associated with.
     *
     * @param Object $proxy The proxy instance that the method was invoked on.
     * @param Method $method The <code>Method</code> instance corresponding to
     *            the interface method invoked on the proxy instance. The
     *            declaring class of the <code>Method</code> object will be the
     *            interface that the method was declared in, which may be a
     *            super-interface of the proxy interface that the proxy class
     *            inherits the method through.
     * @param array $args An array of objects containing the values of the
     *            arguments passed in the method invocation on the proxy
     *            instance, or <code>null</code> if interface method takes no
     *            arguments.
     * @return mixed The value to return from the method invocation on the proxy
     *         instance. If the value returned by this method is
     *         <code>null</code> and the interface method's return type is
     *         primitive, then a <code>NullPointerException</code> will be
     *         thrown by the method invocation on the proxy instance. If the
     *         value returned by this method is otherwise not compatible with
     *         the interface method's declared return type as described above, a
     *         <code>ClassCastException</code> will be thrown by the method
     *         invocation on the proxy instance.
     * @throws \Exception The exception to throw from the method invocation on
     *         the proxy instance. The exception's type must be assignable
     *         either to any of the exception types declared in the
     *         <code>throws</code> clause of the interface method or to the
     *         unchecked exception types. If a checked exception is thrown by
     *         this method that is not assignable to any of the exception types
     *         declared in the <code>throws</code> clause of the interface
     *         method, then an <code>UndeclaredThrowableException</code>
     *         containing the exception that was thrown by this method will be
     *         thrown by the method invocation on the proxy instance.
     */
    public function invoke(Object $proxy, Method $method, array $args = null);
}
?>