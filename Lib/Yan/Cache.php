<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

require_once 'Yan/Cache/Abstract.php';

/**
 * Yan_Cache
 *
 * @category  Yan
 * @package   Yan_Cache
 */
abstract class Yan_Cache
{
	/**
	 * Consts for clean() method
	 */
	const CLEANING_ALL = 'all';
	const CLEANING_OLD = 'old';

	/**
	 * create a new cache object
	 *
	 * @param string $adapter
	 * @param array  $config
	 *
	 * @return Yan_Cache_Abstract
	 * @throws Yan_Cache_Exception
	 */
	public static function factory($adapter, $config)
	{
		/*
		 * Verify that an adapter name has been specified.
		 */
		if (!is_string($adapter) || empty($adapter)) {
			require_once 'Yan/Cache/Exception.php';
			throw new Yan_Cache_Exception('Adapter name must be specified in a string');
		}

		/*
		 * Convert object argument to array
		 */
		if (is_object($config)) {
			if (method_exists($config, 'toArray')) {
				$config = $config->toArray();
			} else {
				$config = (array)$config;
			}
		}
		/*
		 * Verify that adapter parameters are in an array.
		 */
		if (!is_array($config)) {
			require_once 'Yan/Cache/Exception.php';
			throw new Yan_Cache_Exception('Adapter parameters must be in an array or a array object');
		}

		/*
		 * Form full adapter class name
		 */
		$adapterClass = Yan::loadClass('Yan_Cache_' . strtolower($adapter));

		/*
		 * Create an instance of the adapter class.
		 * Pass the config to the adapter class constructor.
		 */
		$adapter = new $adapterClass($config);

		/*
		 * Verify that the object created is a descendent of the abstract adapter type.
		 */
		if (!$adapter instanceof Yan_Cache_Abstract) {
			require_once 'Yan/Cache/Exception.php';
			throw new Yan_Cache_Exception("Adapter class '$adapterClass' does not extend Yan_Cache_Abstract");
		}

		return $adapter;
	}
}