<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Log/Writer.php';

/**
 * Yan_Log_Writer_Stream
 *
 * @category   Yan
 * @package    Yan_Log
 * @subpackage Writer
 */
class Yan_Log_Writer_Stream extends Yan_Log_Writer
{
	/**
	 * Holds the PHP stream to log to.
	 * @var null|stream
	 */
	protected $_stream = null;

	/**
	 * Class Constructor
	 *
	 * @param  streamOrUrl     Stream or URL to open as a stream
	 * @param  mode            Mode, only applicable if a URL is given
	 */
	public function __construct($streamOrUrl, $mode = 'a')
	{
		if (is_resource($streamOrUrl)) {
			if (get_resource_type($streamOrUrl) != 'stream') {
				require_once 'Yan/Log/Writer/Exception.php';
				throw new Yan_Log_Writer_Exception('Resource is not a stream');
			}

			$this->_stream = $streamOrUrl;
		} else {
			if (!$this->_stream = @fopen($streamOrUrl, $mode, false)) {
				require_once 'Yan/Log/Writer/Exception.php';
				throw new Yan_Log_Writer_Exception(
					"'$streamOrUrl' cannot be opened with mode '$mode'"
				);
			}
		}
	}

	/**
	 * format a message and add to the log.
	 *
	 * @param  array  $event  log data event
	 * @return void
	 */
	public function append($event)
	{
		list($timestamp, $message, $priority, $priorityName) = $event;
		$timestamp = date('Y-m-d H:i:s', $timestamp);
		$message = str_replace(array("\n", "\r"), '', $message);
		$line = sprintf('%s %s (%s): %s', $timestamp, $priorityName, $priority, $message);
		$this->_log[] = $line . PHP_EOL;
	}

	public function write()
	{
		$string = implode('', $this->_log);
		@fwrite($this->_stream, $string);
		@fclose($this->_stream);
	}
}