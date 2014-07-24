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

/**
 * A Formatter provides support for formatting LogRecords.
 * <p>
 * Typically each logging Handler will have a Formatter associated with it. The Formatter takes a
 * LogRecord and converts it to a string.
 * <p>
 * Some formatters (such as the XMLFormatter) need to wrap head and tail strings around a set of
 * formatted records. The getHeader and getTail methods can be used to obtain these strings.
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
abstract class Formatter extends Object {

	/**
	 * Constructs a new Formatter.
	 */
	protected function __construct() {
	}

	/**
	 * Format the given log record and return the formatted string.
	 * The resulting formatted String will normally include a localized and formatted version of the
	 * LogRecord's message field. It is recommended to use the {@link Formatter#formatMessage}
	 * convenience method to localize and format the message field.
	 * @param LogRecord $record
	 * @return string The formatted log record
	 */
	public abstract function format(LogRecord $record);

	/**
	 * Return the header string for a set of formatted records.
	 * This base class returns an empty string, but this may be overridden by subclasses.
	 * @param Handler $h
	 * @return string
	 */
	public function getHead(Handler $h) {
		return '';
	}

	/**
	 * Return the tail string for a set of formatted records.
	 * This base class returns an empty string, but this may be overridden by subclasses.
	 * @param Handler $h
	 * @return string
	 */
	public function getTail(Handler $h) {
		return '';
	}

	/**
	 * Format the message string from a log record.
	 * This method is provided as a convenience for Formatter subclasses to use when they are
	 * performing formatting.
	 * <ul>
	 * <li> If there are no parameters, no formatter is used.
	 * <li> Otherwise, if the string contains %s or %d then <code>vsprintf()</code> is used to format
	 * the string.
	 * </ul>
	 * @param LogRecord $record
	 * @return string
	 */
	public function formatMessage(LogRecord $record) {
		$format = $record->getMessage();
		try {
			$parameters = $record->getParameters();
			if ($parameters == null || count( $parameters ) == 0) {
				return $format;
			}
			if (strpos( $format, '%' ) !== false) {
				return vsprintf( $format, $parameters );
			}
			return $format;
		} catch ( \Exception $e ) {
			return $format;
		}
	}
}
?>