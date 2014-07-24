<?php

/**
 * Kaaterskil Library
 *
 * PHP version 5.5
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KAATERSKIL MANAGEMENT, LLC BE
 * LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
 * BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category Kaaterskil
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
namespace KM\Util;

/**
 * An object that implements the Enumeration interface generates a series of elements, one at a
 * time.
 * Successive calls to the <code>nextElement</code> method return successive elements of the series.
 * <p>
 * For example, to print all elements of a <tt>Vector&lt;E&gt;</tt> <i>v</i>:
 * <pre>
 * for (Enumeration&lt;E&gt; e = v.elements(); e.hasMoreElements();)
 * System.out.println(e.nextElement());</pre>
 * <p>
 * Methods are provided to enumerate through the elements of a vector, the keys of a hashtable, and
 * the values in a hashtable. Enumerations are also used to specify the input streams to a
 * <code>SequenceInputStream</code>.
 * <p>
 * NOTE: The functionality of this interface is duplicated by the Iterator interface. In addition,
 * Iterator adds an optional remove operation, and has shorter method names. New implementations
 * should consider using Iterator in preference to Enumeration.
 *
 * @package class_container
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
interface Enumeration {

	/**
	 * Tests if this enumeration contains more elements.
	 * @return boolean
	 */
	public function hasMoreElements();

	/**
	 * Returns the next element of this enumeration if this enumeration object has at least one more
	 * element to provide.
	 * @return mixed
	 */
	public function nextElement();
}
?>