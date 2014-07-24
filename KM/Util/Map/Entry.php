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

namespace KM\Util\Map;

/**
 * A map entry (key-value pair).
 * The Map.entrySet method returns a collection-view of the map, whose elements are of
 * this class. The only way to obtain a reference to a map entry is from the iterator of
 * this collection-view. These Map.Entry objects are valid only for the duration of the
 * iteration; more formally, the behavior of a map entry is undefined if the backing map
 * has been modified after the entry was returned by the iterator, except through the
 * setValue operation on the map entry.
 *
 * @package		KM\Util\Map
 * @author		Blair
 * @copyright	Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version		SVN $Id$
 */
interface Entry {

	/**
	 * Returns the key corresponding to this entry.
	 * @return mixed
	 */
	public function getKey();

	/**
	 * Returns the value corresponding to this entry.
	 * If the mapping has been removed from the backing map (by the iterator's remove
	 * operation), the results of this call are undefined.
	 * @return mixed
	 */
	public function getValue();

	/**
	 * Replaces the value corresponding to this entry with the specified value (optional
	 * operation).
	 * (Writes through to the map.) The behavior of this call is undefined if the mapping
	 * has already been removed from the map (by the iterator's remove operation).
	 * @param mixed $value
	 */
	public function setValue($value = null);
}
?>