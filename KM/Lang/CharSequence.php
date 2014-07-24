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
namespace KM\Lang;

/**
 * A CharSequence if a readable sequence of char values. This interface provides
 * uniform, read-only access to many different kinds of char sequences. A char
 * value represents a character in the Basic Multilingual Plane (BMP). Refer to
 * Unicode Character Representation for details.
 *
 * @author Blair
 */
interface CharSequence
{

    /**
     * Returns the length of this character sequence. The length is the number
     * of 16-bit chars in the sequence.
     *
     * @return int THe number of chars in this sequence.
     */
    public function length();

    /**
     * Returns the char value at the specified index. An index ranges from zero
     * to length(). The first char value of the sequence is at index zero, as
     * for array indexing.
     *
     * @param int $index The index of the char value to be returned.
     * @return string The specified char value.
     */
    public function charAt($index);

    /**
     * returns a CharSequence that is a subsequence of this sequence. The
     * subsequence start with the char value at the specified index and end with
     * the char value at index $end - 1. The length (in chars) id the returned
     * sequence is $end - $start, so if $start = $end, then an empty sequence is
     * returned.
     *
     * @param int $start The start index, inclusive.
     * @param int $end The end index, exclusive.
     * @return \KM\Lang\CharSequence The specified subsequence.
     */
    public function subSequence($start, $end);
}
?>