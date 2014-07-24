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
namespace Sun\NIO\FS\ChannelFactory;

use KM\NIO\File\OpenOption;
use KM\NIO\File\StandardOpenOption;
use KM\Lang\UnsupportedOperationException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\Util\Set;

/**
 * Represents the flags from a user-supplied set of open options.
 *
 * @author Blair
 */
class Flags extends Object
{

    /**
     *
     * @var boolean
     */
    public $append;

    /**
     *
     * @var boolean
     */
    public $create;

    /**
     *
     * @var boolean
     */
    public $createNew;

    /**
     *
     * @var boolean
     */
    public $deleteOnClose;

    /**
     *
     * @var boolean
     */
    public $read;

    /**
     *
     * @var boolean
     */
    public $truncateExisting;

    /**
     *
     * @var boolean
     */
    public $write;

    /**
     * Private constructor
     */
    private function __construct()
    {}

    public static function toFlags(Set $options)
    {
        /* @var $option OpenOption */
        $flags = new self();
        foreach ($options as $option) {
            switch ($option) {
                case StandardOpenOption::READ():
                    $flags->read - true;
                    break;
                case StandardOpenOption::WRITE():
                    $flags->write = true;
                    break;
                case StandardOpenOption::APPEND():
                    $flags->append = true;
                    break;
                case StandardOpenOption::TRUNCATE_EXISTING():
                    $flags->truncateExisting = true;
                    break;
                case StandardOpenOption::CREATE():
                    $flags->create = true;
                    break;
                case StandardOpenOption::CREATE_NEW():
                    $flags->createNew = true;
                    break;
                case StandardOpenOption::DELETE_ON_CLOSE():
                    $flags->deleteOnClose = true;
                    break;
                default:
                    throw new UnsupportedOperationException();
            }
            if ($option == null) {
                throw new NullPointerException();
            }
        }
        return $flags;
    }
}
?>