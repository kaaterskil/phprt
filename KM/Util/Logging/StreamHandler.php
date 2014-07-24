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
namespace KM\Util\Logging;

use KM\Lang\NullPointerException;
use KM\IO\IOException;
use KM\IO\OutputStream;
use KM\IO\OutputStreamWriter;
use KM\IO\Writer;
use KM\IO\UnsupportedEncodingException;

/**
 * StreamHandler Class
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class StreamHandler extends Handler {
	
	/**
	 * The output stream
	 * @var OutputStream
	 */
	private $output;
	
	/**
	 * Returns true if the header is written to the output stream, false otherwise.
	 * @var boolean
	 */
	private $doneHeader;
	
	/**
	 * The writer
	 * @var Writer
	 */
	protected $writer;

	/**
	 * Private method to configure a StreamHandler from LogManager properties and/or default values.
	 */
	private function configure() {
		$manager = LogManager::getLogManager();
		$cname = $this->getClass()->getName();
		
		$this->setLevel( $manager->getLevelProperty( $cname . '.level', Level::$INFO ) );
		$this->setFilter( $manager->getFilterProperty( $cname . '.filter', null ) );
		$this->setFormatter( $manager->getFormatterProperty( $cname . '.formatter', new SimpleFormatter() ) );
		try {
			$this->setEncoding( $manager->getStringProperty( $cname . '.encoding', null ) );
		} catch ( \Exception $e ) {
			try {
				$this->setEncoding( null );
			} catch ( \Exception $e ) {
				// Drop through
			}
		}
	}

	/**
	 * Creates a StreamHandler with the given Formatter and output stream.
	 * @param OutputStream $output
	 * @param Formatter $formatter
	 */
	public function __construct(OutputStream $out = null, Formatter $formatter = null) {
		$this->sealed = false;
		$this->configure();
		if ($formatter != null) {
			$this->setFormatter( $formatter );
		}
		if ($out != null) {
			$this->setOutputStream( $out );
		}
		$this->sealed = true;
	}

	/**
	 * Change the output stream.
	 * If there is a current output stream then the Formatter's tail string is written and the
	 * stream is flushed and closed, Then the output stream is replaced with the new output stream.
	 * @param OutputStream $out The new output stream. May not be null.
	 * @throws NullPointerException
	 */
	protected function setOutputStream(OutputStream $out) {
		if ($out == null) {
			throw new NullPointerException();
		}
		$this->flushAndClose();
		$this->output = $out;
		$this->doneHeader = false;
		$encoding = $this->getEncoding();
		if ($encoding === null) {
			$this->writer = new OutputStreamWriter( $out );
		} else {
			try {
				$this->writer = new OutputStreamWriter( $out, $encoding );
			} catch ( UnsupportedEncodingException $e ) {
				// This shouldn't happen. The setEncoding() method should have validated that the
				// encoding is OK.
				$msg = 'Unexpected exception ' . $e;
				throw new \ErrorException( $msg );
			}
		}
	}

	/**
	 * Format and publish a LogRecord.
	 *
	 * The StreamHandler first checks if there is an output stream and if the given LogRecord has at
	 * least the required log level. If not, it silently returns. If so, it calls any associated
	 * Filter to check if the record should be published. If so, it calls its Formatter to format
	 * the record and then writes the result to the current output stream.
	 *
	 * If this is the first LogRecord to be written to a given output stream, the Formatter's head
	 * string is written to the stream before the LogRecord is written.
	 * @param LogRecord $record Description of the log event, A null record is silently ignored and
	 *        is not published.
	 * @throws IOException
	 * @see \KM\Util\Logging\Handler::publish()
	 */
	public function publish(LogRecord $record) {
		if (!$this->isLoggable( $record )) {
			return;
		}
		$msg = '';
		try {
			$msg = $this->getFormatter()->format( $record );
		} catch ( \Exception $e ) {
			// We don't want to throw an exception here, but we report the exception to any
			// registered ErrorManager.
			$this->reportError( null, $e, ErrorManager::FORMAT_FAILURE );
			return;
		}
		
		try {
			if (!$this->doneHeader) {
				$this->writer->writeString( $this->getFormatter()
					->getHead( $this ) );
				$this->doneHeader = true;
			}
			$this->writer->writeString( $msg );
		} catch ( IOException $e ) {
			// We don't want to throw an exception here, but we report the exception to any
			// registered ErrorManager.
			$this->reportError( null, $e, ErrorManager::WRITE_FAILURE );
		}
	}

	/**
	 * Check if this Handler would actually log a given LogRecord.
	 * This method checks if the LogRecord has an appropriate level and whether it satisfies any
	 * Filter. IT will also return false if no output stream has been assigned Yet or the LogRecord
	 * is null.
	 * @param LogRecord $record
	 * @return boolean True if the LogRecord would be logged.
	 * @see \KM\Util\Logging\Handler::isLoggable()
	 */
	public function isLoggable(LogRecord $record) {
		if ($record == null) {
			return false;
		}
		return parent::isLoggable( $record );
	}

	/**
	 * Flush any buffered messages.
	 * @throws IOException
	 * @see \KM\Util\Logging\Handler::flush()
	 */
	public function flush() {
		if ($this->writer != null) {
			try {
				$this->writer->flush();
			} catch ( \Exception $e ) {
				// We don't want to throw an exception here, but we report the exception to any
				// registered ErrorManager.
				$this->reportError( null, $e, ErrorManager::FLUSH_FAILURE );
			}
		}
	}

	private function flushAndClose() {
		if ($this->writer != null) {
			try {
				if (!$this->doneHeader) {
					$this->writer->writeString($this->getFormatter()->getHead($this));
					$this->doneHeader = true;
				}
				$this->writer->writeString($this->getFormatter()->getTail($this));
				$this->writer->flush();
				$this->writer->close();
			} catch ( IOException $e ) {
				// We don't want to throw an exception here, but we report the exception to any
				// registered ErrorManager.
				$this->reportError( null, $e, ErrorManager::CLOSE_FAILURE );
			}
			$this->writer = null;
			$this->output = null;
		}
	}

	/**
	 * Close the current output stream.
	 * The Formatter's tail string is written to the stream before it is closed. In addition, if the
	 * Formatter's head string has not yet been written to the stream, it will be written before the
	 * tail string.
	 * @see \KM\Util\Logging\Handler::close()
	 */
	public function close() {
		$this->flushAndClose();
	}
}
?>