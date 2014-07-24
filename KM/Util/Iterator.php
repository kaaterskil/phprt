<?php

/**
 * Kaaterskil Library
 *
 * PHP version 5.5
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KAATERSKIL MANAGEMENT, LLC BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Kaaterskil
 * @copyright   Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version     SVN $Id$
 */
namespace KM\Util;

use KM\Lang\IllegalStateException;
use KM\Lang\Object;
use KM\Lang\UnsupportedOperationException;
use KM\Util\NoSuchElementException;

/**
 * An iterator over a collection.
 * Iterator takes the place of Enumeration in the Java Collections Framework. Iterators
 * differ from enumerations in two ways:
 *
 * - Iterators allow the caller to remove elements from the underlying collection during
 * the
 * iteration with well-defined semantics.
 * - Method names have been improved.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface Iterator extends \Iterator {

	/**
	 * Returns true if the iteration has more elements.
	 * (In other words, returns true if next() would return an element rather than
	 * throwing an exception.)
	 * @return boolean
	 */
	public function hasNext();

	/**
	 * Returns the next element in the iteration.
	 * @throws NoSuchElementException
	 * @return mixed
	 */
	public function next();

	/**
	 * Removes from the underlying collection the last element returned by this iterator
	 * (optional operation).
	 * This method can be called only once per call to next(). The behavior of an iterator
	 * is unspecified if the underlying collection is modified while the iteration is in
	 * progress in any way other than by calling this method.
	 * @throws IllegalStateException
	 * @throws UnsupportedOperationException
	 * @return void
	 */
	public function remove();
}
?>