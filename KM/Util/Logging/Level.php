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

use KM\Lang\Object;
use KM\Lang\NullPointerException;
use KM\Util\HashMap;
use KM\Util\Map;

/**
 * The Level class defines a set of standard logging levels that can be used to control logging
 * output.
 * The logging Level objects are ordered and are specified by ordered integers. Enabling logging at
 * a given level also enables logging at all higher levels. Clients should normally use the
 * predefined Level constants such as Level.SEVERE. The levels in descending order are:
 * <ul>
 * <li>SEVERE (highest value)
 * <li>WARNING
 * <li>INFO
 * <li>CONFIG
 * <li>FINE
 * <li>FINER
 * <li>FINEST (lowest value)
 * </ul>
 * In addition there is a level OFF that can be used to turn off logging, and a level ALL that can
 * be used to enable logging of all messages.
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class Level extends Object {
	
	/**
	 * OFF is a special level that can be used to turn off logging.
	 * This level is initialized to PHP_INT_MAX.
	 * @var Level
	 */
	public static $OFF;
	
	/**
	 * SEVERE is a message level indicating a serious failure.
	 * In general, SEVERE messages should describe events that are of considerable important and
	 * which will prevent normal program execution. They should be reasonably intelligible to end
	 * users and to system administrators. This level is initialized to 1000.
	 * @var Level
	 */
	public static $SEVERE;
	
	/**
	 * WARNING is a message level indicating a potential problem.
	 * In general WARNING message should describe events that will be of interest to end users or
	 * system managers or which indicate potential problems. This level is initialized to 900.
	 * @var Level
	 */
	public static $WARNING;
	
	/**
	 * INFO is a message level for informational messages.
	 * Typically INFO messages will be written to the console or its equivalent. So the INFO level
	 * should only be used for reasonably significant messages that will make sense to end users and
	 * system administrators. This level is initialized to 800.
	 * @var Level
	 */
	public static $INFO;
	
	/**
	 * CONFIG is a message level for static configuration messages.
	 * CONFIG message are intended to provide a variety of static configuration information to
	 * assist in debugging problems that may be associated with particular configurations. For
	 * example, CONFIG messages might include CPU type, the graphics depth, the GUI look-and-feel,
	 * etc. This level is initialized to 700.
	 * @var Level
	 */
	public static $CONFIG;
	
	/**
	 * FINE is a message level providing tracing information.
	 * All of FINE, Finer and FINEST are intended for relatively detailed tracing. The exact meaning
	 * of the three levels will vary between subsystems but in general FINEST should be used for the
	 * most voluminous output, FINER for somewhat less detailed output, and FINE for the lowest
	 * volume and most important messages. In general, the FINE level should be used for information
	 * that will be broadly interesting to developers who do not have a specialized interest in the
	 * specific subsystem. FINE messages might include things like minor recoverable failures.
	 * Issues indicating potential performance problems are also worth logging as FINE. This level
	 * is initialized to 500.
	 * @var Level
	 */
	public static $FINE;
	
	/**
	 * FINER indicates a fairly detailed tracing message.
	 * By default,logging calls for entering, returning or throwing an exception are traced at this
	 * level. This level is initialized to 400.
	 * @var Level
	 */
	public static $FINER;
	
	/**
	 * FINEST indicates a highly detailed tracing message.
	 * This level is initialized to 300.
	 * @var Level
	 */
	public static $FINEST;
	
	/**
	 * ALL indicates that all messages should be logged.
	 * This level is initialized to -1000.
	 * @var Level
	 */
	public static $ALL;
	
	/**
	 * The map of levels.
	 * @var Map
	 */
	private static $known;

	/**
	 * Static constructor
	 */
	public static function clinit() {
		self::$known = new HashMap('<string, \KM\Util\Logging\Level>');
		self::$OFF = new Level( 'OFF', PHP_INT_MAX );
		self::$SEVERE = new Level( 'SEVERE', 1000 );
		self::$WARNING = new Level( 'WARNING', 900 );
		self::$INFO = new Level( 'INFO', 800 );
		self::$CONFIG = new Level( 'CONFIG', 700 );
		self::$FINE = new Level( 'FINE', 500 );
		self::$FINER = new Level( 'FINER', 400 );
		self::$FINEST = new Level( 'FINEST', 300 );
		self::$ALL = new Level( 'ALL', -1000 );
	}
	
	/**
	 * The non-localized name of the level.
	 * @var string
	 */
	private $name;
	
	/**
	 * The integer value of the level.
	 * @var int
	 */
	private $value;

	/**
	 * Constructs a new Level with the given name and value.
	 * @param string $name
	 * @param int $value
	 * @throws NullPointerException
	 */
	protected function __construct($name, $value) {
		if ($name == null) {
			throw new NullPointerException();
		}
		$this->name = (string) $name;
		$this->value = (int) $value;
		self::$known->put( $name, $this );
	}

	public function getName() {
		return $this->name;
	}

	public function intValue() {
		return $this->value;
	}

	public function equals(Object $obj = null) {
		if ($obj != null && $obj instanceof Level) {
			return $obj->value == $this->value;
		}
		return false;
	}

	public function hashCode() {
		return $this->value;
	}

	public static function findLevel($name) {
		if ($name == null) {
			throw new NullPointerException();
		}
		$level = self::$known->get( $name );
		if ($level != null) {
			return $level;
		}
		return null;
	}

	public function __toString() {
		return $this->name;
	}
}
?>