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
namespace Sun\NIO\CH;

use KM\Lang\IllegalStateException;
use KM\NIO\Channels\ClosedChannelException;
use KM\NIO\Channels\FileLock;
use KM\NIO\Channels\FileChannel;

/**
 * FileLockImpl Class
 *
 * @author Blair
 */
class FileLockImpl extends FileLock
{

    private $valid = true;

    public function __construct(FileChannel $channel)
    {
        parent::__construct($channel);
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function invalidate()
    {
        $this->valid = false;
    }

    public function release()
    {
        $ch = $this->acquiredBy();
        if (! $ch->isOpen()) {
            throw new ClosedChannelException();
        }
        if ($this->valid) {
            if ($ch instanceof FileChannelImpl) {
                $ch->release($this);
            } else {
                throw new IllegalStateException();
            }
            $this->valid = false;
        }
    }
}
?>