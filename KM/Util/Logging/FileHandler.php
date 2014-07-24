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

use KM\IO\BufferedOutputStream;
use KM\IO\File;
use KM\IO\FileOutputStream;
use KM\IO\IOException;
use KM\Lang\IllegalArgumentException;
use KM\Lang\System;
use KM\NIO\Channels\FileChannel;
use KM\NIO\File\FileAlreadyExistsException;
use KM\NIO\File\StandardOpenOption;
use KM\Util\HashMap;
use KM\Util\Logging\FileHandler\InitializationErrorManager;
use KM\Util\Logging\FileHandler\MeteredStream;
use KM\Util\Map;
use KM\Util\HashSet;

/**
 * FileHandler Class
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class FileHandler extends StreamHandler {
	
	/**
	 * the underlying metered stream.
	 * @var MeteredStream
	 */
	private $meter;
	
	/**
	 * Tells whether a file is to be opened in append mode.
	 * @var boolean
	 */
	private $append = false;
	
	/**
	 * The maximum number of bytes to write to any one file, or zero for no limit.
	 * @var int
	 */
	private $limit = 0;
	
	/**
	 * The number of files to use.
	 * @var int
	 */
	private $count = 1;
	
	/**
	 * The format pattern to use for log filename generation.
	 * @var string
	 */
	private $pattern;
	
	/**
	 * The name of the lock file.
	 * @var string
	 */
	private $lockFileName;
	
	/**
	 * The lock file channel.
	 * @var FileChannel
	 */
	private $lockFileChannel;
	
	/**
	 * An array of abstract filenames to use for logging.
	 * @var File[]
	 */
	private $files;
	
	/**
	 * The maximum number of lock files.
	 * @var int
	 */
	private static $MAX_LOCKS = 100;
	
	/**
	 * A collection of filenames with active locks.
	 * @var Map
	 */
	private static $locks;

	/**
	 * Static constructor
	 */
	public static function clinit() {
		self::$locks = new HashMap( '<string, string>' );
	}

	private function open(File $fname, $append) {
		$append = (boolean) $append;
		
		$len = 0;
		if ($append) {
			$len = $fname->length();
		}
		
		$fout = new FileOutputStream( $fname->getPath(), $append );
		$bout = new BufferedOutputStream( $fout );
		$this->meter = new MeteredStream( $bout, $len );
		$this->setOutputStream( $this->meter );
	}

	/**
	 * Configure a FileHandler from LogManager properties and/or default values.
	 */
	private function configure() {
		$manager = LogManager::getLogManager();
		$cname = $this->getClass()->getName();
		$this->pattern = $manager->getStringProperty( $cname . '.pattern', "%h/php%u.log" );
		$this->limit = $manager->getIntProperty( $cname . '.limit', 0 );
		if ($this->limit < 0) {
			$this->limit = 0;
		}
		$this->count = $manager->getIntProperty( $cname . '.count', 1 );
		if ($this->count <= 0) {
			$this->count = 1;
		}
		$this->append = $manager->getBooleanProperty( $cname . '.append', false );
		$this->setLevel( $manager->getLevelProperty( $cname . '.level', Level::$ALL ) );
		$this->setFilter( $manager->getFilterProperty( $cname . '.filter', null ) );
		$this->setFormatter( $manager->getFormatterProperty( $cname . '.formatter', new SimpleFormatter() ) );
		try {
			$this->setEncoding( $manager->getStringProperty( $cname . '.encoding', null ) );
		} catch ( \Exception $e ) {
			$this->setEncoding( null );
		}
	}

	/**
	 * Initialize a FileHandler to write to a set of files with optional append.
	 * When (approximately) the given limit has written to one file, another file will be opened.
	 * The output will cycle through a set of $count files.
	 *
	 * The FileHandler is configured based on LogManager properties (or their default values) except
	 * that the given $pattern argument is used as the filename pattern, the file limit is set to
	 * the $limit argument, and the file count is set to the given $count argument, and the append
	 * mode is set to the given $append argument. The count must be at least 1.
	 * @param string $pattern The pattern for naming the output file.
	 * @param int $limit The maximum number of bytes to write to any one file.
	 * @param int $count The number of files to use.
	 * @param boolean $append Specifies the append mode.
	 * @throws IllegalArgumentException
	 */
	public function __construct($pattern = null, $limit = 0, $count = 1, $append = false) {
		if ($limit < 0 || $count < 1 || (!empty( $pattern ) && strlen( $pattern ) < 1)) {
			throw new IllegalArgumentException();
		}
		$this->configure();
		if ($pattern !== null) {
			$this->pattern = (string) $pattern;
		}
		$this->limit = (int) $limit;
		$this->count = (int) $count;
		$this->append = (boolean) $append;
		$this->openFiles();
	}

	/**
	 * Open the set of output files based on the configured instance variables.
	 * @throws IllegalArgumentException
	 * @throws IOException
	 * @throws FileAlreadyExistsException
	 */
	private function openFiles() {
		$manager = LogManager::getLogManager();
		if ($this->count < 1) {
			throw new IllegalArgumentException( "file count = " . $this->count );
		}
		if ($this->limit < 0) {
			$this->limit = 0;
		}
		
		// We register our own ErrorManager during initialization so we can record exceptions.
		$em = new InitializationErrorManager();
		$this->setErrorManager( $em );
		
		// Create a lock file. This grants us exclusive access to our set of output files as long as
		// we are alive.
		$unique = -1;
		for(;;) {
			$unique++;
			if ($unique > self::$MAX_LOCKS) {
				throw new IOException( "Couldn't get lock for " . $this->pattern );
			}
			
			// Generate a lock file name from the $unique integer.
			$this->lockFileName = $this->generate( $this->pattern, 0, $unique )->getPath() . '.lck';
			// Now try to lock that filename.
			// We first check if we ourself already have the file locked.
			if (self::$locks->get( $this->lockFileName ) != null) {
				// We already own this lock for a different FileHandler object. Try again.
				continue;
			}
			
			try {
				$options = new HashSet( '\KM\NIO\File\StandardOpenOption' );
				$options->add( StandardOpenOption::CREATE() );
				$options->add( StandardOpenOption::WRITE() );
				$this->lockFileChannel = FileChannel::openNewFileChannel( $this->lockFileName, $options );
			} catch ( FileAlreadyExistsException $ix ) {
				// Try the next lock file name in the sequence.
				continue;
			}
			
			// Now try to lock the file
			$available = false;
			try {
				$available = $this->lockFileChannel->tryLock() != null;
				// We got the lock OK.
			} catch ( IOException $ix ) {
				// We got an IOException while trying to get the lock, This normally indicates that
				// locking is not supported on the target directory. We have to proceed without
				// getting a lock. Drop through.
				$available = true;
			}
			if ($available) {
				// We got the lock. Remember it.
				self::$locks->put( $this->lockFileName, $this->lockFileName );
				break;
			}
			
			// We failed to get the lock. Try the next file.
			$this->lockFileChannel->close();
		}
		
		$this->files = array();
		for($i = 0; $i < $this->count; $i++) {
			$this->files[$i] = $this->generate( $this->pattern, $i, $unique );
		}
		
		// Create the initial log file.
		if ($this->append) {
			$this->open( $this->files[0], true );
		} else {
			$this->rotate();
		}
		
		// Did we detect any exceptions during initialization?
		$ex = $em->lastException;
		if ($ex != null) {
			if ($ex instanceof IOException) {
				throw $ex;
			} else {
				throw new IOException( 'Exception: ' . $ex );
			}
		}
		
		// Install the normal error manager.
		$this->setErrorManager( new ErrorManager() );
	}

	/**
	 * Generates a filename based on a user-supplied pattern, generation number and an integer
	 * uniqueness suffix.
	 * @param string $pattern The pattern for naming the output file.
	 * @param int $generation The generation number to distinguish rotated logs.
	 * @param int $unique A unique number to resolve conflicts.
	 * @return \KM\IO\File The generated File.
	 */
	private function generate($pattern, $generation, $unique) {
		/* @var $file File */
		$file = null;
		$word = '';
		$sawg = false;
		$sawu = false;
		
		$i = 0;
		$pattern = (string) $pattern;
		while ( $i < strlen( $pattern ) ) {
			$ch = $pattern[$i];
			$i++;
			$ch2 = 0;
			if ($i < strlen( $pattern )) {
				$ch2 = strtolower( $pattern[$i] );
			}
			if ($ch == '/') {
				if ($file == null) {
					$file = new File( $word );
				} else {
					$file = new File( $file, $word );
				}
				$word = '';
				continue;
			} elseif ($ch == '%') {
				if ($ch2 == 't') {
					$tmpDir = System::getProperty( 'php.io.tmpdir' );
					if ($tmpDir == null) {
						$tmpDir = System::getProperty( 'user.home' );
					}
					$file = new File( $tmpDir );
					$i++;
					$word = '';
					continue;
				} elseif ($ch2 == 'h') {
					$file = new File( System::getProperty( 'user.dir' ), 'logs' );
					$i++;
					$word = '';
					continue;
				} elseif ($ch2 == 'g') {
					$word .= $generation;
					$sawg = true;
					$i++;
					continue;
				} elseif ($ch2 == 'u') {
					$word .= $unique;
					$sawu = true;
					$i++;
					continue;
				} elseif ($ch2 == '%') {
					$word .= '%';
					$i++;
					continue;
				}
			}
			$word = $word . $ch;
		}
		if ($this->count > 1 && !$sawg) {
			$word .= '.' . $generation;
		}
		if ($unique > 0 && !$sawu) {
			$word .= '.' . $unique;
		}
		if (strlen( $word ) > 0) {
			if ($file == null) {
				$file = new File( $word );
			} else {
				$file = new File( $file, $word );
			}
		}
		return $file;
	}

	/**
	 * Rotate the set of output files.
	 */
	private function rotate() {
		/* @var $f1 File */
		/* @var $f2 File */
		$oldLevel = $this->getLevel();
		$this->setLevel( Level::$OFF );
		
		parent::close();
		for($i = $this->count - 2; $i >= 0; $i--) {
			$f1 = $this->files[$i];
			$f2 = $this->files[$i + 1];
			if ($f1->exists()) {
				if ($f2->exists()) {
					$f2->delete();
				}
				$f1->renameTo( $f2 );
			}
		}
		try {
			$this->open( $this->files[0], false );
		} catch ( \Exception $e ) {
			// We don't want to throw an exception here, but we report the exception to any
			// registered ErrorManager.
			$this->reportError( null, $e, ErrorManager::OPEN_FAILURE );
		}
		$this->setLevel( $oldLevel );
	}

	/**
	 * Format and publish a LogRecord.
	 * @param LogRecord $record Description of the log event. A null record is silently ignores and
	 *        is not published.
	 * @see \KM\Util\Logging\StreamHandler::publish()
	 */
	public function publish(LogRecord $record) {
		if (!$this->isLoggable( $record )) {
			return;
		}
		parent::publish( $record );
		$this->flush();
		if ($this->limit > 0 && $this->meter->written >= $this->limit) {
			$this->rotate();
		}
	}

	/**
	 * Close all the files.
	 * @see \KM\Util\Logging\StreamHandler::close()
	 */
	public function close() {
		parent::close();
		if ($this->lockFileName == null) {
			return;
		}
		try {
			// Close the lock file channel (Which will also free any locks)
			$this->lockFileChannel->close();
		} catch ( \Exception $e ) {
			// Problems closing the stream. Punt.
		}
		self::$locks->remove( $this->lockFileName );
		
		// Unlink (delete) the lock file
		$f = new File( $this->lockFileName );
		$f->delete();
		
		$this->lockFileName = null;
		$this->lockFileChannel = null;
	}
}
?>