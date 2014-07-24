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

use KM\Util\Iterator;

/**
 * This class provides a skeletal implementation of the Set interface to minimize the
 * effort required to implement this interface.
 *
 * The process of implementing a set by extending this class is identical to that of
 * implementing a Collection by extending AbstractCollection, except that all of the
 * methods and constructors in subclasses of this class must obey the additional
 * constraints imposed by the Set interface (for instance, the add method must not permit
 * addition of multiple instances of an object to a set).
 *
 * Note that this class does not override any of the implementations from the
 * AbstractCollection class. It merely adds implementations for equals and hashCode.
 *
 * @package KM\Util
 * @author Blair
 * @copyright Copyright (c) 2009-2013 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class AbstractSet extends AbstractCollection implements Collection, Set {

	/**
	 * Removes from this set all of its elements that are contained in the specified
	 * collection (optional operation).
	 * If the specified collection is also a set, this operation effectively modifies this
	 * set so that its value is the asymmetric set difference of the two sets.
	 *
	 * This implementation determines which is the smaller of this set and the specified
	 * collection, by invoking the size method on each. If this set has fewer elements,
	 * then the implementation iterates over this set, checking each element returned by
	 * the iterator in turn to see if it is contained in the specified collection. If it
	 * is so contained, it is removed from this set with the iterator's remove method. If
	 * the specified collection has fewer elements, then the implementation iterates over
	 * the specified collection, removing from this set each element returned by the
	 * iterator, using this set's remove method.
	 *
	 * Note that this implementation will throw an UnsupportedOperationException if the
	 * iterator returned by the iterator method does not implement the remove method.
	 *
	 * @param Collection $c
	 * @return boolean
	 * @see \KM\Util\AbstractCollection::removeAll()
	 */
	public function removeAll(Collection $c) {
		/* @var $i \KM\Util\Iterator */
		$modified = false;
		if ($this->size() > $c->size()) {
			for($i = $c->getIterator(); $i->hasNext();) {
				$modified |= $this->remove( $i->next() );
			}
		} else {
			for($i = $this->getIterator(); $i->hasNext();) {
				if ($c->contains( $i->next() )) {
					$i->remove();
					$modified = true;
				}
			}
		}
		return $modified;
	}
}
?>