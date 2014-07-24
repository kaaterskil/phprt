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

use KM\Lang\IllegalArgumentException;

/**
 * Unchecked exception thrown when a string that is not a legal charset name is used as such.
 *
 * @package KM\NIO\Charset
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class IllegalCharsetNameException extends IllegalArgumentException {
	
	/**
	 * The illegal charset name.
	 * @var string
	 */
	private $charsetName;

	/**
	 * Constructs an instance of this class.
	 * @param string $charsetName The illegal charset name.
	 */
	public function __construct($charsetName) {
		parent::__construct( $charsetName );
		$this->charsetName = (string) $charsetName;
	}

	/**
	 * Returns the illegal charset name.
	 * @return string
	 */
	public function getCharsetName() {
		return $this->charsetName;
	}
}
?>