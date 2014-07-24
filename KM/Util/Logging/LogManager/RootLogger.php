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
namespace KM\Util\Logging\LogManager;

use KM\Util\Logging\Handler;
use KM\Util\Logging\Logger;
use KM\Util\Logging\LogManager;
use KM\Util\Logging\LogRecord;
use KM\Util\Logging\Level;

/**
 * We use a subclass of Logger for the root logger so that we only instantiate the global handlers
 * when they are first needed.
 *
 * @package KM\Util\Logging\LogManager
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
final class RootLogger extends Logger {

	public function __construct(LogManager $manager) {
		parent::__construct( '', $manager );
		$this->setLevel( Level::$ALL );
	}

	public function logRecord(LogRecord $record) {
		// Make sure that the global handlers have been instantiated.
		$this->manager->initializeGlobalHandlers();
		parent::logRecord( $record );
	}

	public function addHandler(Handler $handler) {
		$this->manager->initializeGlobalHandlers();
		parent::addHandler( $handler );
	}

	public function removeHandler(Handler $handler) {
		$this->manager->initializeGlobalHandlers();
		parent::removeHandler( $handler );
	}

	public function getHandlers() {
		$this->manager->initializeGlobalHandlers();
		return parent::getHandlers();
	}
}
?>