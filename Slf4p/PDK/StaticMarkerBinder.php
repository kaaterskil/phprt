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
namespace Slf4p\PDK;

use KM\Lang\Object;
use Slf4p\Helpers\BasicMarkerFactory;
use Slf4p\MarkerFactory;
use Slf4p\Spi\MarkerFactoryBinder;

/**
 * The binding of GenericMarkerFactory class with an actual instance of
 * MarkerFactory is performed using information returned by this class.
 *
 * @author Blair
 */
class StaticMarkerBinder extends Object implements MarkerFactoryBinder
{

    /**
     * The singleton instance.
     *
     * @var StaticMarkerBinder
     */
    private static $instance;

    /**
     * Returns the singleton instance of this StaticMarkerBinder.
     *
     * @return \Slf4p\MarkerFactoryBinder
     */
    public static function getSingleton()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * The marker factory instance.
     *
     * @var MarkerFactory
     */
    protected $markerFactory;

    /**
     * Private constructor prevents direct instantiation.
     */
    private function __construct()
    {
        $this->markerFactory = new BasicMarkerFactory();
    }

    /**
     * Currently, this method always returns an instance of BasicMarkerFacTory.
     *
     * @return \Slf4p\MarkerFactory
     * @see \Slf4p\Spi\MarkerFactoryBinder::getMarkerFactory()
     */
    public function getMarkerFactory()
    {
        return $this->markerFactory;
    }

    /**
     * Currently, this method returns the class name of BasicMarkerFacTory.
     *
     * @return string
     * @see \Slf4p\Spi\MarkerFactoryBinder::getMarkerFactoryClassStr()
     */
    public function getMarkerFactoryClassStr()
    {
        return $this->markerFactory->getClass()->getName();
    }
}
?>