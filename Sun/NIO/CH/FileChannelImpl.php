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

use KM\IO\Closeable;
use KM\IO\IOException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\IndexOutOfBoundsException;
use KM\Lang\NullPointerException;
use KM\Lang\Object;
use KM\NIO\ByteBuffer;
use KM\NIO\Channels\ClosedChannelException;
use KM\NIO\Channels\FileChannel;
use KM\NIO\Channels\FileLock;
use KM\NIO\Channels\NonReadableChannelException;
use KM\NIO\Channels\NonWriteableChannelException;

/**
 * FileChannelImpl Class
 *
 * @author Blair
 */
class FileChannelImpl extends FileChannel
{

    /**
     * The file dispatcher
     *
     * @var FileDispatcher
     */
    private $nd;

    /**
     * The file pointer handle
     *
     * @var resource
     */
    private $fd;

    /**
     * Tells whether the channel is readable
     *
     * @var boolean
     */
    private $readable;

    /**
     * Tells whether the channel is writable
     *
     * @var boolean
     */
    private $writeable;

    /**
     * Tells whether the channel is in append mode
     *
     * @var unknown
     */
    private $append;

    /**
     * Required to prevent finalization of creating stream (immutable)
     *
     * @var Object
     */
    private $parent;

    /**
     * Lock for operations involving position and size
     *
     * @var Object
     */
    private $positionLock;

    /**
     * The lock on the file.
     *
     * @var FileLock
     */
    private $fileLock;

    protected function __construct($fd, $readable, $writeable, $append, Object $parent = null)
    {
        $this->fd = $fd;
        $this->readable = (boolean) $readable;
        $this->writeable = (boolean) $writeable;
        $this->append = (boolean) $append;
        $this->parent = $parent;
        $this->nd = new FileDispatcherImpl();
        $this->positionLock = new Object();
    }

    public function __destruct()
    {
        if ($this->fd != null) {
            $this->close();
        }
    }

    public static function open($fd, $readable, $writable, $append = false, Object $parent = null)
    {
        return new self($fd, $readable, $writable, $append, $parent);
    }

    private function ensureOpen()
    {
        if (! $this->isOpen()) {
            throw new ClosedChannelException();
        }
    }

    protected function implCloseChannel()
    {
        // Release and invalidate any locks that we still hold
        if ($this->fileLock != null) {
            if ($this->fileLock->isValid()) {
                $this->nd->release($this->fd);
                $this->fileLock->invalidate();
            }
        }
        $this->nd->preCLose($this->fd);
        if ($this->parent != null && $this->parent instanceof Closeable) {
            // CLose the resource via the parent stream's close method. The
            // parent will invoke our
            // close() method, which is defined in the superclass
            // AbstractChannel, but the isOpen()
            // logic in that method will prevent this method from being
            // reinvoked.
            $this->parent->close();
        } else {
            $this->nd->close($this->fd);
        }
    }

    public function read(ByteBuffer $dst)
    {
        $this->ensureOpen();
        if (! $this->readable) {
            throw new NonReadableChannelException();
        }
        $n = 0;
        if (! $this->isOpen()) {
            return 0;
        }
        do {
            $n = IOUtil::read($fd, $dst, - 1, $this->nd, $this->fileLock);
        } while (($n == IOStatus::INTERRUPTED) && $this->isOpen());
        assert(IOStatus::check($n));
        return IOStatus::normalize($n);
    }

    public function write(ByteBuffer $src)
    {
        $this->ensureOpen();
        if (! $this->writeable) {
            throw new NonWriteableChannelException();
        }
        $n = 0;
        if (! $this->isOpen()) {
            return 0;
        }
        do {
            $n = IOUtil::write($this->fd, $src, - 1, $this->nd, $this->positionLock);
        } while (($n == IOStatus::INTERRUPTED) && $this->isOpen());
        assert(IOStatus::check($n));
        return IOStatus::normalize($n);
    }

    public function getPosition()
    {
        $this->ensureOpen();
        $p = - 1;
        if (! $this->isOpen()) {
            return 0;
        }
        do {
            $p = $this->append ? $this->nd->size($this->fd) : $this->position0($this->fd, - 1);
        } while (($p == IOStatus::INTERRUPTED) && $this->isOpen());
        assert(IOStatus::check($p));
        return IOStatus::normalize($p);
    }

    public function setPosition($newPosition)
    {
        $this->ensureOpen();
        if ($newPosition < 0) {
            throw new IllegalArgumentException();
        }
        $p = - 1;
        if (! $this->isOpen()) {
            return null;
        }
        do {
            $p = $this->position0($this->fd, $newPosition);
        } while (($p == IOStatus::INTERRUPTED) && $this->isOpen());
        assert(IOStatus::check($p));
        return $this;
    }

    public function size()
    {
        $this->ensureOpen();
        $s = - 1;
        if (! $this->isOpen()) {
            return - 1;
        }
        do {
            $s = $this->nd->size($this->fd);
        } while (($s == IOStatus::INTERRUPTED) == $this->isOpen());
        assert(IOStatus::check($s));
        return IOStatus::normalize($s);
    }

    public function truncate($size)
    {
        $this->ensureOpen();
        if ($size < 0) {
            throw new IllegalArgumentException();
        }
        if ($size > $this->size()) {
            return $this;
        }
        if (! $this->writeable) {
            throw new NonWriteableChannelException();
        }
        $rv = - 1;
        $p = - 1;
        if (! $this->isOpen()) {
            return null;
        }
        // Get current position
        do {
            $p = $this->position0($this->fd, - 1);
        } while (($p == IOStatus::INTERRUPTED) && $this->isOpen());
        if (! $this->isOpen()) {
            return null;
        }
        assert($p >= 0);
        
        // Truncate file
        do {
            $rv = $this->nd->truncate($this->fd, $size);
        } while (($rv == IOStatus::INTERRUPTED) && $this->isOpen());
        if (! $this->isOpen()) {
            return null;
        }
        
        // Set position to size if greater than size
        if ($p > $size) {
            $p = $size;
        }
        do {
            $rv = $this->position0($this->fd, $p);
        } while (($rv == IOStatus::INTERRUPTED) && $this->isOpen());
        assert(IOStatus::check($rv));
        return $this;
    }

    public function readFromPosition(ByteBuffer $dst, $position)
    {
        if ($dst == null) {
            throw new NullPointerException();
        }
        if ($position < 0) {
            throw new IllegalArgumentException('negative position');
        }
        if (! $this->readable) {
            throw new NonReadableChannelException();
        }
        $n = 0;
        if (! $this->isOpen()) {
            return - 1;
        }
        do {
            $n = IOUtil::read($this->fd, $dst, $position, $this->nd, $this->positionLock);
        } while (($n == IOStatus::INTERRUPTED) && $this->isOpen());
        assert(IOStatus::check($n));
        return IOStatus::normalize($n);
    }

    public function writeFromPosition(ByteBuffer $src, $position)
    {
        if ($dst == null) {
            throw new NullPointerException();
        }
        if ($position < 0) {
            throw new IllegalArgumentException('negative position');
        }
        if (! $this->writeable) {
            throw new NonWriteableChannelException();
        }
        $n = 0;
        if (! $this->isOpen()) {
            return - 1;
        }
        do {
            $n = IOUtil::write($this->fd, $src, $position, $this->nd, $this->positionLock);
        } while (($n == IOStatus::INTERRUPTED) && $this->isOpen());
        assert(IOStatus::check($n));
        return IOStatus::normalize($n);
    }

    public function lock()
    {
        $this->ensureOpen();
        $fli = new FileLockImpl($this);
        $this->fileLock = $fli;
        if (! $this->isOpen()) {
            return null;
        }
        $n = 0;
        do {
            $n = $this->nd->lock($this->fd, true);
        } while (($n == FileDispatcher::INTERRUPTED) && $this->isOpen());
        if ($this->isOpen()) {
            if ($n == FileDispatcher::RET_EX_LOCK) {
                // This is for shared locks
            }
        }
        return $fli;
    }

    public function tryLock()
    {
        $this->ensureOpen();
        $fli = new FileLockImpl($this);
        $this->fileLock = $fli;
        $result = 0;
        try {
            $this->ensureOpen();
            $result = $this->nd->lock($this->fd, false);
        } catch (IOException $e) {
            $this->fileLock = null;
            throw $e;
        }
        if ($result == FileDispatcher::NO_LOCK) {
            $this->fileLock = null;
            return null;
        }
        if ($result == FileDispatcher::RET_EX_LOCK) {
            // This is for shared locks.
        }
        return $fli;
    }

    public function release(FileLockImpl $fli)
    {
        $this->ensureOpen();
        $this->nd->release($this->fd);
        $this->fileLock = null;
    }

    private function position0($fd, $offset)
    {
        if ($fd != null) {
            $offset = (int) $offset;
            if ($offset < 0) {
                $pos = @ftell($fd);
                if ($pos === false) {
                    throw new IOException();
                }
                return $pos;
            } else {
                $stat = @fstat($fd);
                if (! is_array($stat)) {
                    throw new IOException();
                }
                $size = $stat['size'];
                if ($offset > $size) {
                    throw new IOException();
                }
                $result = @fseek($fd, $offset, SEEK_SET);
                if ($result < 0) {
                    throw new IOException();
                }
                return $offset;
            }
        }
        throw new IOException();
    }
}
?>