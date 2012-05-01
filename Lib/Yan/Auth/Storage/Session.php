<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Session.php 15 2012-04-23 11:33:00Z kakalong $
 */

require_once 'Yan/Auth/Storage/Interface.php';

require_once 'Yan/Session.php';

/**
 * Yan_Auth_Storage_Session
 *
 * @category  Yan
 * @package   Yan_Auth
 * @subpackage Storage
 */
class Yan_Auth_Storage_Session implements Yan_Auth_Storage_Interface
{
	/**
	 * Session object member
	 *
	 * @var mixed
	 */
	protected $_member;

	protected $_session;

	public function __construct($namespace = 'Yan_Auth', $member = 'storage')
	{
		Yan_Session::start();
		if (! isset($_SESSION[$namespace])) {
			$_SESSION[$namespace] = array();
		}
		$this->_session = & $_SESSION[$namespace];
		$this->_member = $member;
	}

	/**
	 * Defined by Yan_Auth_Storage_Interface
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return !isset($this->_session[$this->_member]);
	}

	/**
	 * Defined by Yan_Auth_Storage_Interface
	 *
	 * @return mixed
	 */
	public function read()
	{
		return isset($this->_session[$this->_member]) ? $this->_session[$this->_member] : null;
	}

	/**
	 * Defined by Yan_Auth_Storage_Interface
	 *
	 * @param  mixed $contents
	 * @return void
	 */
	public function write($contents)
	{
		$this->_session[$this->_member] = $contents;
	}

	/**
	 * Defined by Yan_Auth_Storage_Interface
	 *
	 * @return void
	 */
	public function clear()
	{
		unset($this->_session[$this->_member]);
	}
}