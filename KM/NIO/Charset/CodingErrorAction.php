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
namespace KM\NIO\Charset;

use KM\Lang\Enum;

/**
 * A typesafe enumeration for coding error actions.
 * Instance of this class are used to specify how malformed input and unmappable character errors
 * are to be handled by charset decoders and encoders.
 *
 * @package KM\NIO\Charset
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class CodingErrorAction extends Enum {
	
	/**
	 * Action indicating that a coding error is to be handled by dropping the erroneous input and
	 * resuming the coding operation.
	 * @var string
	 */
	const IGNORE = 'IGNORE';
	
	/**
	 * Action indicating that a coding error is to be handled by dropping the erroneous input,
	 * appending the coder's replacement value to the output buffer, and resuming the coding
	 * operation.
	 * @var string
	 */
	const REPLACE = 'REPLACE';
	
	/**
	 * Action indicating that a coding error is to be reported, either by returning a CoderResult
	 * object or by throwing aCharacterCodingException, whichever is appropriate for the method
	 * implementing the coding process.
	 * @var string
	 */
	const REPORT = 'REPORT';
}
?>