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

use KM\Lang\NegativeArraySizeException;
use KM\Lang\Object;
use KM\Lang\System;
use KM\Lang\Comparable;

class Arrays extends Object {

	/**
	 * Suppresses default constructor, ensuring non-instantiability.
	 */
	private function __construct() {
	}

	/**
	 * Sorts the specified array of objects into ascending order, according to the <code>Comparable
	 * natural ordering</code> of its elements.
	 * All elements in the array must implement the <code>Comparable</code> interface. Furthermore,
	 * all elements in the array must be <i>mutually comparable</i> (that is,
	 * <code>e1.compareTo(e2)</code> must not throw a <code>ClassCastException</code> for any
	 * elements <code>e1</code> and <code>e2</code> in the array).
	 * @param array $a
	 */
	public static function sort(array &$a) {
		usort( $a, [
			'\KM\Util\Arrays',
			'comparableSort'
		] );
	}

	private static function comparableSort(Comparable $e1, Comparable $e2) {
		return $e1->compareTo( $e2 );
	}

	/**
	 * Copies the specified array, truncating or padding with nulls (if necessary) so the copy has
	 * the specified length.
	 * For all indices that are valid in both the original array and the copy, the two arrays will
	 * contain identical values. For any indices that are valid in the copy but not the original,
	 * the copy will contain null. Such indices will exist is and only if the specified length is
	 * greater than that of the original array.
	 * @param array $original The array to be copied.
	 * @param int $newLength The length of the copy to be returned.
	 * @throws NegativeArraySizeException if $newLEngth is negative.
	 * @return array
	 */
	public static function copyOf(array $original, $newLength) {
		$newLength = (int) $newLength;
		if ($newLength < 0) {
			throw new NegativeArraySizeException();
		}
		
		$copy = array();
		if ($newLength > 0) {
			$copy = array_fill( 0, $newLength, null );
		}
		$originalLength = count( $original );
		System::arraycopy( $original, 0, $copy, 0, min( array(
			$originalLength,
			$newLength
		) ) );
		return $copy;
	}
}
?>