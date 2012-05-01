<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Auth.php 19 2012-04-28 02:42:04Z kakalong $
 */

/**
 * Yan_Auth
 *
 * @category  Yan
 * @package   Yan_Auth
 */
class Yan_Auth
{
	protected static $_instance = null;

	/**
	 * storage of auth result
	 *
	 * @var Yan_Auth_Storage_Interface
	 */
	protected $_storage = null;

	/**
	 * Singleton pattern implementation makes "new" unavailable
	 */
	protected function __construct()
	{}

	/**
	 * Returns an instance of Yan_Auth
	 *
	 * Singleton pattern implementation
	 *
	 * @return Yan_Auth
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * get storage of auth result
	 *
	 * @return Yan_Auth_Storage_Interface
	 */
	public function getStorage()
	{
		if (null === $this->_storage) {
			include_once 'Yan/Auth/Storage/Session.php';
			$this->setStorage(new Yan_Auth_Storage_Session());
		}
		return $this->_storage;
	}

	/**
	 * set storage of auth result
	 *
	 * @param Yan_Auth_Storage_Interface $storage
	 */
	public function setStorage(Yan_Auth_Storage_Interface $storage)
	{
		$this->_storage = $storage;
	}

	/**
	 * Authenticates against the supplied adapter
	 *
	 * @param  Yan_Auth_Adapter_Interface $adapter
	 * @return Yan_Auth_Result
	 */
	public function authenticate(Yan_Auth_Adapter_Interface $adapter)
	{
		$result = $adapter->authenticate();

		if ($result->isValid()) {
			$this->getStorage()->write($result->getIdentity());
		}

		return $result;
	}

	/**
	 * Returns true if and only if an identity is available from storage
	 *
	 * @return boolean
	 */
	public function hasIdentity()
	{
		return !$this->getStorage()->isEmpty();
	}

	/**
	 * Returns the identity from storage or null if no identity is available
	 *
	 * @return mixed|null
	 */
	public function getIdentity()
	{
		$storage = $this->getStorage();

		if ($storage->isEmpty()) {
			return null;
		}

		return $storage->read();
	}

	/**
	 * Clears the identity from persistent storage
	 *
	 * @return void
	 */
	public function clearIdentity()
	{
		$this->getStorage()->clear();
	}
}