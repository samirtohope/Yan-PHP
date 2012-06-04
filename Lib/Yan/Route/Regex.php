<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Route/Interface.php';

/**
 * Yan_Route_Regex
 *
 * @category Yan
 * @package  Yan_Route
 */
class Yan_Route_Regex implements Yan_Route_Interface
{
	protected $_regex = null;
	protected $_defaults = array();
	protected $_reverse = null;
	protected $_map = array();
	protected $_values = array();
	protected $_hashost;

	public function __construct(array $config)
	{
		if (empty($config['rule'])) {
			require_once 'Yan/Route/Exception.php';
			throw new Yan_Route_Exception('Yan_Route_Regex need options "rule"');
		}
		$this->_regex = (string)$config['rule'];
		$this->_defaults = isset($config['defaults'])
			? $this->_getMappedValues((array)$config['defaults'])
			: array();
		$this->_map = isset($config['map']) ? (array)$config['map'] : array();
		$this->_reverse = isset($config['reverse']) ? $config['reverse'] : null;
		$this->_hashost = !empty($config['hashost']);
	}

	public function match(Yan_Request_Abstract $request)
	{
		if ($this->_hashost) {
			$path = $request->getHttpHost() . $request->getBasePath() . $request->getPathInfo();
			$path = trim(urldecode($path), Yan_Route_Interface::URI_DELI);
			$regex = '#^' . $this->_regex . '$#i';
		} else {
			$path = $request->getPathInfo();
			$regex = '#^' . $this->_regex . '#i';
		}

		$res = preg_match($regex, $path, $values);

		if (!$res) {
			return false;
		}

		unset($values[0]);

		$values = $this->_getMappedValues($values);
		$return = $values + $this->_defaults;
		return $return;
	}

	protected function _getMappedValues($values)
	{
		if (count($this->_map) == 0) {
			return $values;
		}
		$return = array();
		foreach ($values as $key => $value) {
			if (is_int($key)) {
				if (array_key_exists($key, $this->_map)) {
					$index = $this->_map[$key];
				} elseif (false === ($index = array_search($key, $this->_map))) {
					$index = $key;
				}
				$return[$index] = $values[$key];
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}
}
