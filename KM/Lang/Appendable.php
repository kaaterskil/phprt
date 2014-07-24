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
 * An object to which <tt>char</tt> sequences and values can be appended. The
 * <tt>Appendable</tt> interface must be implemented by any class whose
 * instances are intended to receive formatted output. <p> The characters to be
 * appended should be valid Unicode characters as described in Unicode Character
 * Representation. Note that supplementary characters may be composed of
 * multiple 16-bit <tt>char</tt> values. <p> Since this interface may be
 * implemented by existing classes with different styles of error handling there
 * is no guarantee that errors will be propagated to the invoker.
 *
 * @author Blair
 */
interface Appendable
{

    /**
     * Appends the specified character sequence (or subsequence) to this
     * Appendable.
     *
     * @param CharSequence|string $csq The character sequence (or subsequence)
     *            to append. If $csq is null then the four characters "null" are
     *            appended to this Appendable.
     * @param int $start The index of the first character in the subsequence, or
     *            zero if none is given.
     * @param int $end The index of the character following the last character
     *            in the subsequence, or the length of the subsequence is none
     *            is given.
     * @return A reference to this Appendable.
     */
    public function append($csq = null, $start = 0, $end = null);

    /**
     * Appends the specified character to this Appendable.
     *
     * @param string $c The character to append.
     * @return A reference to this Appendable.
     */
    public function appendChar($c);
}
?>