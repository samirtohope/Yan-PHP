<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Route.php 15 2012-04-23 11:33:00Z kakalong $
 */

require_once 'Yan/Route/Interface.php';

/**
 * Yan_Route_Route
 *
 * @category Yan
 * @package  Yan_Route
 */
class Yan_Route_Route implements Yan_Route_Interface
{
	protected $_parts = array();

	protected $_keys = array();

	protected $_vals = array();

	protected $_requirements;

	protected $_hashost;

	protected $_defaults;

	/**
	 * Helper var that holds a count of route pattern's static parts
	 * for validation
	 * @var int
	 */
	protected $_staticCount = 0;

	public function __construct(array $config) {
		$rule                = isset($config['rule']) ? trim($config['rule'], Yan_Route_Interface::URI_DELI) : '';
		$this->_defaults     = isset($config['defaults']) ? (array) $config['defaults'] : array();
		$this->_requirements = isset($config['reqs']) ? (array) $config['reqs'] : array();
		$this->_hashost      = !empty($config['hashost']);

		if(strlen($rule)){
			foreach (explode(Yan_Route_Interface::URI_DELI, $rule) as $pos => $part) {
				if (substr($part,0,1)==Yan_Route_Interface::URL_KEY) {
					$name = substr($part, 1);
					$this->_parts[$pos] = (isset($this->_requirements[$name]) ? $this->_requirements[$name] : null);
					$this->_keys[$pos] = $name;
				} else {
					$this->_parts[$pos] = $part;
					if ($part == '*') {
						break;
					}
					$this->_staticCount++;
				}
			}
		}
	}

	public function match(Yan_Request_Abstract $request) {
		if ($this->_hashost) {
			$path = $request->getHttpHost() . $request->getBasePath() . $request->getPathInfo();
		} else {
			$path = $request->getPathInfo();
		}
		$path = trim($path, Yan_Route_Interface::URI_DELI);
		$pathStaticCount = 0;
		$values = array();
		$extras = array();
		if (strlen($path)) {
			$path = explode(Yan_Route_Interface::URI_DELI, $path);
			foreach ($path as $pos => $pathPart) {
				if (!array_key_exists($pos, $this->_parts)) {
					return false;
				}
				$part = $this->_parts[$pos];
				if ($part == '*') {
					$count = count($path);
					for($i = $pos; $i < $count; $i+=2) {
						$var = urldecode($path[$i]);
						if (!isset($extras[$var]) && !isset($this->_defaults[$var]) && !isset($values[$var]))
						{
							$extras[$var] = (isset($path[$i+1])) ? urldecode($path[$i+1]) : null;
						}
					}
					break;
				}
				$name = isset($this->_keys[$pos]) ? $this->_keys[$pos] : null;
				$pathPart = urldecode($pathPart);
				// If it's a static part, match directly
				if ($name === null && $part != $pathPart) {
					return false;
				}
				// If it's a variable with requirement, match a regex. If not - everything matches
				if ($part !== null && !preg_match(Yan_Route_Interface::REGEX_DELI . $part . Yan_Route_Interface::REGEX_DELI . 'iu', $pathPart))
				{
					return false;
				}

				// If it's a variable store it's value for later
				if ($name !== null) {
					$values[$name] = $pathPart;
				} else {
					$pathStaticCount++;
				}
			}
		}
		// Check if all static mappings have been matched
		if ($this->_staticCount != $pathStaticCount) {
			return false;
		}

		$return = $values + $extras + $this->_defaults;

		// Check if all map variables have been initialized
		foreach ($this->_keys as $k) {
			if (!array_key_exists($k, $return)) {
				return false;
			}
		}
		$this->_values = $values;

		return $return;
	}
}
