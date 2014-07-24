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
namespace KM\NIO\File;

use KM\IO\IOException;

/**
 * Thrown when a file system operation fails on one or two files.
 * This class is the general class for file system exceptions.
 *
 * @package KM\NIO\File
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class FileSystemException extends IOException {
	
	/**
	 * The filename
	 * @var string
	 */
	protected $file;
	
	/**
	 * Another filename
	 * @var string
	 */
	private $other;

	/**
	 * Constructs a FileSystemException when up to two filenames fail, or there is additional
	 * information to explain the reason.
	 * @param string $file A string identifying the file or null if noT known.
	 * @param string $other A string identifying the other file or null if there isn't another file or
	 *        if not known.
	 * @param string $reason A reason message with additional information or null.
	 */
	public function __construct($file, $other = null, $reason = null) {
		$message = '';
		if ($file != null) {
			$message .= $file;
		}
		if ($other != null) {
			$message .= ' -> ' . $other;
		}
		if ($reason != null) {
			$message .= ': ' . $reason;
		}
		parent::__construct( $message );
		$this->file = (string) $file;
		$this->other = (string) $other;
	}

	/**
	 * Returns the other file used to create this exception.
	 * @return string
	 */
	public function getOtherFile() {
		return $this->other;
	}

	/**
	 * Returns the string explaining why the file system operation failed.
	 * @return string
	 */
	public function getReason() {
		return parent::getMessage();
	}
}
?>