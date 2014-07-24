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

use KM\Lang\Enum;

/**
 * StandardOpenOption Class
 *
 * @package KM\NIO\File
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class StandardOpenOption extends Enum {
	
	/**
	 * Open for reading only.
	 * @var string
	 */
	const READ = 'read';
	
	/**
	 * Open for writing only; place the file pointer at the beginning of the file..
	 * @var string
	 */
	const WRITE = 'write';
	
	/**
	 * Open for writing only; place the file pointer at the end of the file.
	 * @var string
	 */
	const APPEND = 'append';
	
	/**
	 * If the file already exists and it is opened for WRITE access, then its length is truncated to 0.
	 * @var string
	 */
	const TRUNCATE_EXISTING = 'truncateExisting';
	
	/**
	 * Create a new file if it does not exist.
	 * @var string
	 */
	const CREATE = 'create';
	
	/**
	 * Create a new file, failing if the file already exists.
	 * @var string
	 */
	const CREATE_NEW = 'createNew';
	
	/**
     * Delete on close.
	 * @var string
	 */
	const DELETE_ON_CLOSE = 'deleteOnClose';
}
?>