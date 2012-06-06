<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

require_once 'Yan/Config.php';

/**
 * Yan_Config_Ini
 *
 * @category  Yan
 * @package   Yan_Config
 */
class Yan_Config_Ini extends Yan_Config
{
	public function __construct($file = null)
	{
		$this->merge(self::parse($file));
	}

	/**
	 * parse data to array
	 *
	 * @param $filename
	 *
	 * @throws Yan_Config_Exception
	 * @return array
	 */
	public static function parse($filename)
	{
		try {
			$loaded = parse_ini_file($filename, true);
		} catch (Exception $e) {
			require_once 'Yan/Config/Exception.php';
			throw new Yan_Config_Exception($e->getMessage());
		}

		$iniArray = array();
		foreach ($loaded as $section => $data) {
			if (!is_array($data)) {
				$iniArray = array_merge_recursive($iniArray, self::_processKey(array(), $section, $data));
			} else {
				$config = array();
				foreach ($data as $key => $value) {
					$config = self::_processKey($config, $key, $value);
				}
				$iniArray[$section] = $config;
			}
		}

		return $iniArray;
	}

	protected static function _processKey($config, $key, $value)
	{
		if (strpos($key, '.') !== false) {
			$pieces = explode('.', $key, 2);
			if (strlen($pieces[0]) && strlen($pieces[1])) {
				if (!isset($config[$pieces[0]])) {
					if ($pieces[0] === '0' && !empty($config)) {
						// convert the current values in $config into an array
						$config = array($pieces[0] => $config);
					} else {
						$config[$pieces[0]] = array();
					}
				} elseif (!is_array($config[$pieces[0]])) {
					require_once 'Yan/Config/Exception.php';
					throw new Yan_Config_Exception("Cannot create sub-key for '{$pieces[0]}' as key already exists");
				}
				$config[$pieces[0]] = self::_processKey($config[$pieces[0]], $pieces[1], $value);
			} else {
				require_once 'Yan/Config/Exception.php';
				throw new Yan_Config_Exception("Invalid key '$key'");
			}
		} else {
			$config[$key] = $value;
		}
		return $config;
	}
}
