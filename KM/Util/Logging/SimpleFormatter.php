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

use KM\Lang\System;
use KM\Util\Date;

/**
 * Print a brief summary of the {@code LogRecord} in a human readable
 * format.
 * The summary will typically be 1 or 2 lines.
 *
 * <p>
 * <a name="formatting">
 * <b>Configuration:</b></a>
 * The {@code SimpleFormatter} is initialized with the
 * <a href="../Formatter.html#syntax">format string</a>
 * specified in the {@code java.util.logging.SimpleFormatter.format}
 * property to {@linkplain #format format} the log messages.
 * This property can be defined
 * in the {@linkplain LogManager#getProperty logging properties}
 * configuration file
 * or as a system property. If this property is set in both
 * the logging properties and system properties,
 * the format string specified in the system property will be used.
 * If this property is not defined or the given format string
 * is {@linkplain java.util.IllegalFormatException illegal},
 * the default format is implementation-specific.
 *
 * @package KM\Util\Logging
 * @author Blair
 * @copyright Copyright (c) 2009-2014 Kaaterskil Management, LLC
 * @version SVN $Id$
 */
class SimpleFormatter extends Formatter {
	
	/**
	 * The default message format.
	 * @var string
	 */
	private $defaultFormat;

	/**
	 * Creates a SimpleFormatter instance.
	 */
	public function __construct() {
		parent::__construct();
		
		// Set the default format to: 'date [pid] source \n level message \n throwable'
		$this->defaultFormat = '%1$s [%2$d]: %3$s' . "\n" . '%5$-7s - %6$s%7$s' . "\n";
	}

	/**
	 * Format the given LogRecord.
	 * <p>
	 * The formatting can be customized by specifying the
	 * <a href="../Formatter.html#syntax">format string</a>
	 * in the <a href="#formatting">
	 * {@code java.util.logging.SimpleFormatter.format}</a> property.
	 * The given {@code LogRecord} will be formatted as if by calling:
	 * <pre>
	 * {@link String#format String.format}(format, date, source, logger, level, message, thrown);
	 * </pre>
	 * where the arguments are:<br>
	 * <ol>
	 * <li>{@code format} - the {@link java.util.Formatter
	 * java.util.Formatter} format string specified in the
	 * {@code java.util.logging.SimpleFormatter.format} property
	 * or the default format.</li>
	 * <li>{@code date} - a {@link Date} object representing
	 * {@linkplain LogRecord#getMillis event time} of the log record.</li>
	 * <li>{@code source} - a string representing the caller, if available;
	 * otherwise, the logger's name.</li>
	 * <li>{@code logger} - the logger's name.</li>
	 * <li>{@code level} - the {@linkplain Level#getLocalizedName
	 * log level}.</li>
	 * <li>{@code message} - the formatted log message
	 * returned from the {@link Formatter#formatMessage(LogRecord)}
	 * method. It uses {@link java.text.MessageFormat java.text}
	 * formatting and does not use the {@code java.util.Formatter
	 * format} argument.</li>
	 * <li>{@code thrown} - a string representing
	 * the {@linkplain LogRecord#getThrown throwable}
	 * associated with the log record and its backtrace
	 * beginning with a newline character, if any;
	 * otherwise, an empty string.</li>
	 * </ol>
	 *
	 * <p>Some example formats:<br>
	 * <ul>
	 * <li> {@code java.util.logging.SimpleFormatter.format="%4$s: %5$s [%1$tc]%n"}
	 * <p>This prints 1 line with the log level ({@code 4$}),
	 * the log message ({@code 5$}) and the timestamp ({@code 1$}) in
	 * a square bracket.
	 * <pre>
	 * WARNING: warning message [Tue Mar 22 13:11:31 PDT 2011]
	 * </pre></li>
	 * <li> {@code java.util.logging.SimpleFormatter.format="%1$tc %2$s%n%4$s: %5$s%6$s%n"}
	 * <p>This prints 2 lines where the first line includes
	 * the timestamp ({@code 1$}) and the source ({@code 2$});
	 * the second line includes the log level ({@code 4$}) and
	 * the log message ({@code 5$}) followed with the throwable
	 * and its backtrace ({@code 6$}), if any:
	 * <pre>
	 * Tue Mar 22 13:11:31 PDT 2011 MyClass fatal
	 * SEVERE: several message with an exception
	 * java.lang.IllegalArgumentException: invalid argument
	 * at MyClass.mash(MyClass.java:9)
	 * at MyClass.crunch(MyClass.java:6)
	 * at MyClass.main(MyClass.java:3)
	 * </pre></li>
	 * <li> {@code java.util.logging.SimpleFormatter.format="%1$tb %1$td, %1$tY %1$tl:%1$tM:%1$tS
	 * %1$Tp %2$s%n%4$s: %5$s%n"}
	 * <p>This prints 2 lines similar to the example above
	 * with a different date/time formatting and does not print
	 * the throwable and its backtrace:
	 * <pre>
	 * Mar 22, 2011 1:11:31 PM MyClass fatal
	 * SEVERE: several message with an exception
	 * </pre></li>
	 * </ul>
	 * <p>This method can also be overridden in a subclass.
	 * It is recommended to use the {@link Formatter#formatMessage}
	 * convenience method to localize and format the message field.
	 * @param LogRecord $record
	 * @return string
	 * @see \KM\Util\Logging\Formatter::format()
	 */
	public function format(LogRecord $record) {
		/* @var $e \Exception */
		// Format the date string
		list( $msec, $sec ) = explode( ' ', $record->getMillis() );
		$date = date( 'D M d Y H:i:s', intval( $sec ) );
		$date .= '.' . str_pad( round( $msec * 10000, 0 ), 4, '0', STR_PAD_LEFT );
		$date .= date( ' T P', $sec );
		
		// Format the source
		$source = '';
		if (!empty( $record->getSourceClassName() )) {
			$source = $record->getSourceClassName();
			if (!empty( $record->getSourceMethodName() )) {
				$source .= ' ' . $record->getSourceMethodName();
			}
		} else {
			$source = $record->getLoggerName();
		}
		
		$message = $this->formatMessage($record);
		
		// Format the exception, if any
		$throwable = '';
		if ($record->getThrown() != null) {
			if ($record->getThrown()->getMessage() != null) {
				$throwable .= "\n" . $record->getThrown()->getMessage();
			}
			$throwable .= "\n" . $record->getThrown()->getTraceAsString();
		}
		
		// Now format the message.
		$buffer = sprintf( $this->defaultFormat, $date, getmypid(), $source, $record->getLoggerName(),
			$record->getLevel()->getName(), $message, $throwable );
		return $buffer;
	}
}
?>