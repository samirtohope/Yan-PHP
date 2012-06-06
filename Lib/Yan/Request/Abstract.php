<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Request_Abstract
 *
 * @category  Yan
 * @package   Yan_Request
 */
abstract class Yan_Request_Abstract implements ArrayAccess
{

	/**
	 * Controller Name/Value
	 * @var string
	 */
	protected $_controller;

	/**
	 * Controller key for retrieving controller from params
	 * @var string
	 */
	protected $_controllerKey = 'controller';

	/**
	 * Action Name/Value
	 * @var string
	 */
	protected $_action;

	/**
	 * Action key for retrieving action from params
	 * @var string
	 */
	protected $_actionKey = 'action';

	/**
	 * params store data fron parsing or out-set
	 * @var array
	 */
	protected $_params = array();


	/**
	 * magic method
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($this->_params[$key]);
	}

	/**
	 * unset a key from param
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->_params[$key]);
	}

	/**
	 * set a key-value into param
	 *
	 * @param string $key
	 * @param mixed  $val
	 *
	 * @return void
	 */
	public function __set($key, $val)
	{
		$this->_params[$key] = $val;
	}

	/**
	 * Alias to __set()
	 *
	 * @param string $key
	 * @param mixed  $val
	 *
	 * @return void
	 */
	public function set($key, $val)
	{
		$this->__set($key, $val);
	}

	/**
	 * Access values contained in the superglobals as public members
	 * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
	 *
	 * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		return isset($this->_params[$key]) ? $this->_params[$key] : null;
	}

	/**
	 * Alias to __get
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->__get($key);
	}

	/**
	 * defined by ArrayAccess
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->__isset($key);
	}

	/**
	 * defined by ArrayAccess
	 *
	 * @param string $key
	 * @param mixed  $val
	 *
	 * @return void
	 */
	public function offsetSet($key, $val)
	{
		$this->__set($key, $val);
	}

	/**
	 * defined by ArrayAccess
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->__get($key);
	}

	/**
	 * defined by ArrayAccess
	 *
	 * @param string $key
	 */
	public function offsetUnset($key)
	{
		$this->__unset($key);
	}

	/**
	 * Set the controller key
	 *
	 * @param string $key
	 */
	public function setControllerKey($key)
	{
		$this->_controllerKey = (string)$key;
	}

	/**
	 * Set the action key
	 *
	 * @param string $key
	 */
	public function setActionKey($key)
	{
		$this->_actionKey = (string)$key;
	}

	/**
	 * Retrieve the controller name
	 *
	 * @return string
	 */
	public function getControllerName()
	{
		if (!$this->_controller) {
			$this->_controller = $this->__get($this->_controllerKey);
		}
		return $this->_controller;
	}

	/**
	 * Set the controller name to use
	 *
	 * @param string $value
	 */
	public function setControllerName($value)
	{
		$this->_controller = $value;
	}

	/**
	 * Retrieve the action name
	 *
	 * @return string
	 */
	public function getActionName()
	{
		if (!$this->_action) {
			$this->_action = $this->__get($this->_actionKey);
		}

		return $this->_action;
	}

	/**
	 * Set the action name
	 *
	 * @param string $value
	 */
	public function setActionName($value)
	{
		$this->_action = $value;
	}

	/**
	 * Retrieving a member of the param
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getParam($key = null, $default = null)
	{
		if (null === $key) {
			return $this->_params;
		}
		return isset($this->_params[$key]) ? $this->_params[$key] : $default;
	}

	/**
	 * Set params to request
	 *
	 * @param array $array
	 *
	 * @return Yan_Request_Abstract
	 */
	public function setParams(array $array)
	{
		$this->_params = $this->_params + (array)$array;

		foreach ($this->_params as $key => $value) {
			if (null === $value) {
				unset($this->_params[$key]);
			}
		}

		return $this;
	}
}
