<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Log
 *
 * @category  Yan
 * @package   Yan_Log
 */
class Yan_Log
{
	const ALERT = 1;
	const LOG = 2;
	const DEBUG = 3;
	const ERROR = 4;
	const WARN = 5;
	const NOTICE = 6;

	/**
	 * @var array of log priorities
	 */
	protected $_type = array(
		self::WARN => 'WARN',
		self::LOG => 'LOG',
		self::ERROR => 'ERROR',
		self::ALERT => 'ALERT',
		self::DEBUG => 'DEBUG',
		self::NOTICE => 'NOTICE'
	);

	/**
	 * @var array of Yan_Log_Writer
	 */
	protected $_writers = array();

	protected static $_instance = null;

	/**
	 * Class constructor.  Create a new Log
	 */
	protected function __construct()
	{
	}

	/**
	 * get a singleton log object
	 *
	 * @return Yan_Log
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * quick add log message to cache
	 */
	public static function log($msg, $priority)
	{
		self::getInstance()->append($msg, $priority);
	}

	/**
	 * Add a writer.  A writer is responsible for taking a log
	 * message and writing it out to storage.
	 *
	 * @param  Yan_Log_Writer $writer
	 * @return void
	 */
	public function addWriter(Yan_Log_Writer $writer)
	{
		$this->_writers[] = $writer;
		$str = str_pad(' Yan START ', 68, '*', STR_PAD_BOTH);
		$writer->append($this->_event($str, self::LOG));
		$writer->append($this->_event('REQUEST_URI:' . $_SERVER['PHP_SELF'], self::LOG));
	}

	/**
	 * append log to writers
	 *
	 * @param string $message
	 * @param string $priority
	 */
	public function append($msg, $priority)
	{
		if (!isset($this->_type[$priority])) {
			require_once 'Yan/Log/Exception.php';
			throw new Yan_Log_Exception('Bad log priority');
		}

		$event = $this->_event($msg, $priority);

		// send to each writer
		foreach ($this->_writers as $writer) {
			$writer->append($event);
		}
	}

	/**
	 * format a event
	 *
	 * @return array
	 */
	protected function _event($msg, $priority)
	{
		$priorityName = $this->_type[$priority];
		return array(time(), $msg, $priority, $priorityName);
	}

	/**
	 * Class destructor.  Shutdown log writers
	 *
	 * @return void
	 */
	public function __destruct()
	{
		foreach ($this->_writers as $writer) {
			try {
				$writer->write();
			} catch (Exception $e) {
			}
		}
	}
}