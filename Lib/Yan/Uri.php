<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Uri.php 19 2012-04-28 02:42:04Z kakalong $
 */

/**
 * Yan_Uri
 *
 * @category  Yan
 * @package   Yan_Uri
 */
class Yan_Uri
{
	/**
	 * Scheme of this URI (http, ftp, etc.)
	 *
	 * @var string
	 */
	protected $_scheme = '';

	/**
	 * HTTP username
	 *
	 * @var string
	 */
	protected $_username = '';

	/**
	 * HTTP password
	 *
	 * @var string
	 */
	protected $_password = '';

	/**
	 * HTTP host
	 *
	 * @var string
	 */
	protected $_host = '';

	/**
	 * HTTP post
	 *
	 * @var string
	 */
	protected $_port = '';

	/**
	 * HTTP part
	 *
	 * @var string
	 */
	protected $_path = '';

	/**
	 * HTTP query
	 *
	 * @var string
	 */
	protected $_query = '';

	/**
	 * HTTP fragment
	 *
	 * @var string
	 */
	protected $_fragment = '';

	/**
	 * create uri object from original uri string
	 *
	 * @param string $uri
	 */
	public function __construct($uri)
	{
		$uri = explode(':', $uri, 2);

		$this->_scheme = strtolower($uri[0]);

		$uri = $uri[1];

		$pattern = '~^((//)([^/?#]*))([^?#]*)(\?([^#]*))?(#(.*))?$~';
		$status  = @preg_match($pattern, $uri, $matches);
		if ($status === false) {
			require_once 'Yan/Uri/Exception.php';
			throw new Yan_Uri_Exception('Internal error: uri decomposition failed');
		}

		$this->_path     = isset($matches[4]) ? $matches[4] : '';
		$this->_query    = isset($matches[6]) ? $matches[6] : '';
		$this->_fragment = isset($matches[8]) ? $matches[8] : '';

		// Additional decomposition to get username, password, host, and port
		$combo   = isset($matches[3]) ? $matches[3] : '';
		$pattern = '~^(([^:@]*)(:([^@]*))?@)?((?(?=[[])[[][^]]+[]]|[^:]+))(:(.*))?$~';
		$status  = @preg_match($pattern, $combo, $matches);
		if ($status === false) {
			require_once 'Yan/Uri/Exception.php';
			throw new Yan_Uri_Exception('Internal error: authority decomposition failed');
		}

		// Save remaining URI components
		$this->_username = isset($matches[2]) ? $matches[2] : '';
		$this->_password = isset($matches[4]) ? $matches[4] : '';
		$this->_host     = isset($matches[5])
						 ? preg_replace('~^\[([^]]+)\]$~', '\1', $matches[5])  // Strip wrapper [] from IPv6 literal
						 : '';
		$this->_port     = isset($matches[7]) ? $matches[7] : '';
	}

	public function __get($key)
	{
		$prop = '_'.strtolower($key);
		if (! property_exists($prop, $this)) {
			require_once 'Yan/Uri/Exception.php';
			throw new Yan_Uri_Exception("Undefined key '{$key}'");
		}
		return $this->$prop;
	}

	public function __set($key, $val)
	{
		$prop = '_'.strtolower($key);
		if (! property_exists($prop, $this)) {
			require_once 'Yan/Uri/Exception.php';
			throw new Yan_Uri_Exception("Undefined key '{$key}'");
		}
		$this->$prop = $val;
	}

	public function __call($method, $args)
	{
		$matches = array();

		if (preg_match('/^get(\w+)$/', $method, $matches)) {
			return $this->__get($matches[1]);
		}

		if (preg_match('/^set(\w+)$/', $method, $matches)) {
			return $this->__set($matches[1], $args[0]);
		}

		require_once 'Yan/Uri/Exception.php';
		throw new Yan_Uri_Exception("Unrecognized method '$method()'");
	}

	/**
	 * format uri string
	 *
	 * @return string
	 */
	public function getUri()
	{
		$password = strlen($this->_password) > 0 ? ":$this->_password" : '';
		$auth     = strlen($this->_username) > 0 ? "$this->_username$password@" : '';
		$port     = strlen($this->_port) > 0 ? ":$this->_port" : '';
		$query    = strlen($this->_query) > 0 ? "?$this->_query" : '';
		$fragment = strlen($this->_fragment) > 0 ? "#$this->_fragment" : '';

		return $this->_scheme
			 . '://'
			 . $auth
			 . $this->_host
			 . $port
			 . $this->_path
			 . $query
			 . $fragment;
	}

	public function __toString()
	{
		return $this->getUri();
	}
}
