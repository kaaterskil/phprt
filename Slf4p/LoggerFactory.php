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

/**
 * <code>ILoggerFactory</code> instances manufacture <code>Logger</code>
 * instances by name.
 * <p>Most users retrieve <code>Logger</code> instances through the static
 * <code>LoggerFactory#getLogger(String)</code> method. An instance of of this
 * interface is bound internally with <code>LoggerFactory</code> class at
 * compile time.
 *
 * @author Blair
 */
interface LoggerFactory
{

    /**
     * Return an appropriate <code>Logger</code> instance as specified by the
     * <code>name</code> parameter. If the name parameter is equal to
     * <code>Logger#ROOT_LOGGER_NAME</code>, that is the string value "ROOT"
     * (case insensitive), then the root logger of the underlying logging system
     * is returned. Null-valued name arguments are considered invalid. Certain
     * extremely simple logging systems, e.g. NOP, may always return the same
     * logger instance regardless of the requested name.
     *
     * @param string $name
     * @return \Slf4p\Logger
     */
    public function getLogger($name);
}
?>