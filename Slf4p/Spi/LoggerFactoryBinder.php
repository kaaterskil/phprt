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
namespace Slf4p\Spi;

use Slf4p\LoggerFactory;

/**
 * An internal interface which helps the static LoggerFactory class bind with
 * the appropriate LoggerFactory instance.
 *
 * @author Blair
 */
interface LoggerFactoryBinder
{

    /**
     * Return the instance of LoggerFactory that LoggerFactory should bind to.
     *
     * @return \Slf4p\LoggerFactory
     */
    public function getLoggerFactory();

    /**
     * The string form of the LoggerFactory object that this LoggerFactoryBinder
     * instance is intended to return. This method allows the developer to
     * interrogate this binder's intention which may be different from the
     * LoggerFactory instance it is able to yield in practice. The discrepancy
     * should only occur in case of errors.
     *
     * @return string The class name of the intended LoggerFactory instance.
     */
    public function getLoggerFactoryClassStr();
}
?>