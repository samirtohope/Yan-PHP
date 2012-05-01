<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Config.php 19 2012-04-28 02:42:04Z kakalong $
 */

/**
 * Yan_Config
 *
 * @category  Yan
 * @package   Yan_Config
 */
class Yan_Config implements ArrayAccess
{
	protected $_config = array();
	protected $_count = 0;
	protected $_index = 0;

	/**
	 * @param string $file of config data
	 */
	public function __construct($file = null)
	{
		if ($file) {
			$this->load($file);
		}
	}

	public function __get($index)
	{
		return isset($this->_config[$index]) ? $this->_config[$index] : null;
	}

	public function __isset($index)
	{
		return isset($this->_config[$index]);
	}

	public function __set($index,$val)
	{
		$this->_config[$index] = $val;
		$this->_count = count($this->_config);
	}

	public function __unset($index)
	{
		unset($this->_config[$index]);
		$this->_count = count($this->_config);
	}

	/**
	 * interface defined by ArrayAccess
	 * @param $index
	 */
	public function offsetExists($index)
	{
		return $this->__isset($index);
	}

	/**
	 * interface defined by ArrayAccess
	 * @param $index
	 * @param $value
	 */
	public function offsetSet($index, $value)
	{
		$this->__set($index, $value);
	}

	/**
	 * interface defined by ArrayAccess
	 * @param $index
	 * @return $mixed
	 */
	public function offsetGet($index)
	{
		return $this->__get($index);
	}

	/**
	 * interface defined by ArrayAccess
	 * @param $index
	 */
	public function offsetUnset($index)
	{
		$this->__unset($index);
	}

	/**
	 * export plain array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->_config;
	}

	/**
	 * load config from file
	 *
	 * @param $filename
	 * @return Yan_Config
	 * @throws Yan_Config_Exception
	 */
	public function load($filename)
	{
		if (!file_exists($filename)) {
			return $this;
		}
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		if (!$ext || $ext == 'php') {
			return $this->merge(include $filename);
		}
		try {
			$parser = Yan::loadClass('Yan_Config_'.$ext);
		} catch (Exception $e) {
			require_once 'Yan/Config/Exception.php';
			throw new Yan_Config_Exception(
				"Unsupport typeof '$ext' config file parser engine"
			);
		}
		return $this->merge(call_user_func(array($parser, 'parse'), $filename));
	}

	/**
	 * parse data to array
	 *
	 * @param $filename
	 * @return array
	 */
	public static function parse($filename)
	{
		return array();
	}

	/**
	 * merge config to array
	 *
	 * @param $array
	 * @return Yan_Config
	 * @throws Yan_Config_Exception
	 */
	public function merge($array)
	{
		if ($array instanceof Yan_Config) {
			$array = $array->toArray();
		}
		if (!is_array($array)) {
			require_once 'Yan/Config/Exception.php';
			throw new Yan_Config_Exception('Parameter $array must be an array or a Yan_Config object');
		}
		$this->_arrayDeepMerge($this->_config, $array);
		$this->_count = count($this->_config);
		return $this;
	}

	protected function _arrayDeepMerge(& $target, $source)
	{
		foreach ($source as $key => $item) {
			if (is_array($item)) {
				if (array_key_exists($key, $target) && is_array($target[$key]))
				{
					$this->_arrayDeepMerge($target[$key], $item);
				} else {
					array_walk_recursive($item, array($this,'_replace'));
					$target[$key] = $item;
				}
			} else {
				$target[$key] = $this->_replace($item);
			}
		}
	}

	private function _replace(&$item)
	{
		if (is_string($item)) {
			$item = preg_replace('/%([\w\:]+)%/e','$this->_macroReplace("\1")', $item);
		}
		return $item;
	}

	private function _macroReplace($macro)
	{
		return defined($macro) ? constant($macro) : "%$macro%";
	}
}
