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
namespace KM\Util\HashMap;

use KM\Util\Iterator;
use KM\Util\Map\Entry;

/**
 * EntryIterator Class
 *
 * @package KM\Util\HashMap
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class EntryIterator extends HashIterator implements Iterator {

	/**
	 * Returns the next node.
	 * @return \KM\Util\Map\Entry
	 * @see \KM\Util\Iterator::next()
	 */
	public final function next() {
		return $this->nextNode();
	}
}
?>