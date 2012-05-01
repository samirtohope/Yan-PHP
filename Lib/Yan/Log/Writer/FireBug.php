<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: FireBug.php 15 2012-04-23 11:33:00Z kakalong $
 */

require_once 'Yan/Log/Writer.php';

require_once 'Helper/FirePHP/FirePHP.class.php';

/**
 * Yan_Log_Writer_FireBug
 *
 * @category   Yan
 * @package    Yan_Log
 * @subpackage Writer
 */
class Yan_Log_Writer_FireBug extends Yan_Log_Writer
{
	/**
	 * @var array of log priorities
	 */
	protected $_type = array(
		LOG_WARNING	=> FirePHP::WARN,
		LOG_NOTICE	=> FirePHP::INFO,
		LOG_INFO	=> FirePHP::INFO,
		LOG_DEBUG	=> FirePHP::LOG,
		LOG_ERR		=> FirePHP::ERROR,
		LOG_ALERT	=> FirePHP::ERROR,
		LOG_EMERG	=> FirePHP::ERROR
	);
	/**
	 * firephp instance
	 *
	 * @var FirePHP
	 */
	protected $_fb = null;

	public function __construct()
	{
		$this->_fb = FirePHP::getInstance(true);
	}

	public function append($event)
	{
		list($timestamp,$message,$priority,$priorityName) = $event;
		$priority = array_key_exists($priority,$this->_type)
			? $this->_type[$priority] : FirePHP::LOG;
		$this->_fb->fb($message, $priority);
	}
}