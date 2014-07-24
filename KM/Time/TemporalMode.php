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
namespace KM\Time;

use KM\Lang\Enum;

/**
 * Specifies the various temporal modes: <ul> <li>INSERT: Inserts a new
 * record.</li> <li>CORRECTION: Overwrites the current record. No future-dated
 * records exist. <li>UPDATE: Inserts a new record with the updated data. No
 * future-dated records exist. The original record is retained with an adjusted
 * effective end date set to the day before the new record.</li>
 * <li>UPDATE_CHANGE_INSERT: The same as UPDATE only when future-dated record
 * records exist.</li> <li>UPDATE_OVERRIDE: Inserts a new record with the
 * updated data, adjusting the effective dates of any past and future dated
 * records.</li> <li>PURGE: Purges all records for matching the object
 * identifier.</li> <li>DELETE: Deletes the current record only. No future-dated
 * records exist.</li> <li>FUTURE_CHANGE: Deletes the current and all
 * future-dated records.</li> <li>DELETE_NEXT_CHANGE: Deletes the next dated
 * record only.</li> </ul>
 *
 * @author Blair
 */
class TemporalMode extends Enum
{

    const INSERT = 'Insert';

    const CORRECTION = 'Correction';

    const UPDATE = 'Update: Keep history';

    const UPDATE_CHANGE_INSERT = 'Update: Insert record and keep future';

    const UPDATE_OVERRIDE = 'Update: Insert record and override future';

    const PURGE = 'Delete all records';

    const DELETE = 'Delete current record';

    const FUTURE_CHANGE = 'Delete current and future records';

    const DELETE_NEXT_CHANGE = 'Deletes next record';
}
?>