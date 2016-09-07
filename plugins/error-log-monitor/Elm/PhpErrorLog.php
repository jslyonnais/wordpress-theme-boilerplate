<?php

class Elm_PhpErrorLog {
	private $filename;

	public function __construct($filename) {
		$this->filename = $filename;
	}

	/**
	 * Get an instance of this class that represents the PHP error log.
	 * The log filename is detected automatically.
	 *
	 * @static
	 * @return Elm_PhpErrorLog|WP_Error An instance of this log reader, or WP_Error if error logging is not configured properly.
	 */
	public static function autodetect() {
		$errorLoggingEnabled = ini_get('log_errors') && (ini_get('log_errors') != 'Off');
		$logFile = ini_get('error_log');

		//Check for common problems that could prevent us from displaying the error log.
		if ( !$errorLoggingEnabled ) {
			return new WP_Error(
				'log_errors_off',
				__('Error logging is disabled.', 'error-log-monitor')
			);
		} else if ( empty($logFile) ) {
			return new WP_Error(
				'error_log_not_set',
				__('Error log filename is not set.', 'error-log-monitor')
			);
		} else if ( (strpos($logFile, '/') === false) && (strpos($logFile, '\\') === false) ) {
			return new WP_Error(
				'error_log_uses_relative_path',
				sprintf(
					__('The current error_log value <code>%s</code> is not supported. Please change it to an absolute path.', 'error-log-monitor'),
					esc_html($logFile)
				)
			);
		} else if ( !is_readable($logFile) ) {
			if ( file_exists($logFile) ) {
				return new WP_Error(
					'error_log_not_accessible',
					sprintf (
						__('The log file <code>%s</code> exists, but is not accessible. Please check file permissions.', 'error-log-monitor'),
						esc_html($logFile)
					)
				);
			} else {
				return new WP_Error(
					'error_log_not_found',
					sprintf (
						__('The log file <code>%s</code> does not exist or is inaccessible.', 'error-log-monitor'),
						esc_html($logFile)
					)
				);
			}
		}

		return new self($logFile);
	}

	/**
	 * Read the last N lines from a PHP error log.
	 *
	 * @param int $lineCount How many lines to read.
	 * @param bool $skipEmptyLines
	 * @return array|WP_Error An array of ['timestamp' => ..., 'message' => ...] entries or an error.
	 */
	public function readLastLines($lineCount, $skipEmptyLines = false) {
		$lines = $this->readLastLinesFromFile($this->getFilename(), $lineCount, $skipEmptyLines);
		if ( $lines === null ) {
			return new WP_Error(
				'error_log_fopen_failed',
				sprintf(
					__('Could not open the log file "%s".', 'error-log-monitor'),
					esc_html($this->getFilename())
				)
			);
		}

		$lines = array_map(array($this, 'parseLogLine'), $lines);
		return $lines;
	}

	private function parseLogLine($line) {
		$line = rtrim($line);
		$timestamp = null;
		$message = $line;

		//Attempt to parse the timestamp, if any. Timestamp format can vary by server.
		//We expect log entries to be structured like this: "[date-and-time] error message".
		if ( (substr($line, 0, 1) === '[') &&  (strpos($line, ']') !== false) ) {
			list($parsedTimestamp, $remainder) = explode(']', $line, 2);
			$parsedTimestamp = strtotime(trim($parsedTimestamp, '[]'));
			if ( !empty($parsedTimestamp) ) {
				$timestamp = $parsedTimestamp;
				$message = $remainder;
			}
		}
		return compact('timestamp', 'message');
	}

	/**
	 * Clear the log.
	 * @return void
	 */
	public function clear() {
		$handle = fopen($this->filename, 'w');
		fclose($handle);
	}

	public function getFilename() {
		return $this->filename;
	}

	public function getFileSize() {
		return filesize($this->getFilename());
	}

	/**
	 * Read the last X lines from a file.
	 *
	 * @param string $filename
	 * @param int $lineCount How many lines to read.
	 * @param bool $skipEmptyLines
	 * @param int $bufferSizeInBytes Read buffer size. Defaults to 5 KB.
	 * @return array|null An array of text lines, or NULL if the file couldn't be opened.
	 */
	private function readLastLinesFromFile($filename, $lineCount, $skipEmptyLines = false, $bufferSizeInBytes = 5120) {
		$handle = fopen($filename, 'rb');
		if ( $handle === false ) {
			return null;
		}

		$lines = array();
		$remainder = '';

		//Start reading from the end of the file. Then move back towards the start
		//of the file, reading it in $bufferSizeInBytes blocks.
		fseek($handle, 0, SEEK_END);
		$position = ftell($handle);

		while ( (count($lines) < $lineCount) && ($position != 0) ) {
			//Since $position is an offset from the start of the file,
			//it's also equal to the total amount of remaining data.
			$bytesToRead = ($position > $bufferSizeInBytes) ? $bufferSizeInBytes : $position;
			$position = $position - $bytesToRead;
			fseek($handle, $position, SEEK_SET);
			$buffer = fread($handle, $bytesToRead);

			//We may have a partial line left over from the previous iteration.
			$buffer .= $remainder;

			$newLines = preg_split('@\r\n?|\n@', $buffer, -1, $skipEmptyLines ? PREG_SPLIT_NO_EMPTY : 0);

			//It's likely that we'll start reading in the middle of a line (unless we're at
			//the start of the file), so lets leave the first line for later.
			if ( $position != 0 ) {
				$remainder = array_shift($newLines);
			}

			//Add the new lines to the start of the list.
			$lines = array_merge($newLines, $lines);
		}

		fclose($handle);
		return array_slice($lines, -$lineCount);
	}
}