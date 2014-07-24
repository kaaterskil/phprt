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
namespace KM\IO;

use KM\IO\ObjectStreamClass;
use KM\Lang\Object;

/**
 * Context during upcalls from object stream to class-defined
 * readObject/writeObject methods. Holds object currently being deserialized and
 * descriptor for current class.
 * This context keeps track of the thread it was constructed on, and allows only
 * a single call of defaultReadObject, readFields, defaultWriteObject or
 * writeFields which must be invoked on the same thread before the class's
 * readObject/writeObject method has returned. If not set to the current thread,
 * the getObj method throws NotActiveException.
 *
 * @author Blair
 */
final class SerialCallbackContext extends Object
{

    /**
     *
     * @var Object
     */
    private $obj;

    /**
     *
     * @var ObjectStreamClass
     */
    private $desc;

    /**
     * The current process ID
     *
     * @var int
     */
    private $pid;

    public function __construct(Object $obj, ObjectStreamClass $desc)
    {
        $this->obj = $obj;
        $this->desc = $desc;
        $this->pid = getmypid();
    }

    public function getObj()
    {
        $this->checkAndSetUsed();
        return $this->obj;
    }

    public function getDesc()
    {
        return $this->desc;
    }

    private function checkAndSetUsed()
    {
        if ($this->pid != getmypid()) {
            throw new NotActiveException('not in readObject invocation or fields already read');
        }
        $this->pid = null;
    }

    public function setUsed()
    {
        $this->pid = null;
    }
}
?>