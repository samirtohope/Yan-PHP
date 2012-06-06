<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Cache_Abstract
 *
 * @category   Yan
 * @package    Yan_Cache
 */
abstract class Yan_Cache_Abstract
{

	protected $_options = array(
		'lifetime' => false,
		'prefix'   => false
	);

	protected $_config = array(
		'prefix'   => 'cache',
		'lifetime' => 3600
	);

	public function __construct($config = array())
	{
		/*
		 * Verify that adapter parameters are in an array.
		 */
		if (!is_array($config)) {
			/*
			 * Convert object argument to a plain array.
			 */
			if ($config instanceof Yan_Config) {
				$config = $config->toArray();
			} else {
				require_once 'Yan/Cache/Exception.php';
				throw new Yan_Cache_Exception(
					'Adapter parameters must be in an array or a object'
				);
			}
		}

		foreach ($this->_options as $key => $optional) {
			if (array_key_exists($key, $config)) {
				$this->_config[$key] = $config[$key];
			} elseif ($optional) {
				require_once 'Yan/Cache/Exception.php';
				throw new Yan_Cache_Exception(
					"Configuration array must have a key for '$key' that names the database instance"
				);
			}
		}

		$this->_init();
	}

	protected function _init()
	{
	}

	/**
	 * Clean some cache records
	 *
	 * Available modes are :
	 * 'all' (default)  => remove all cache entries
	 * 'old'            => remove too old cache entries
	 *
	 * @param string $mode clean mode
	 *
	 * @return boolean true if no problem
	 */
	abstract public function clean($mode = Yan_Cache::CLEANING_ALL);

	/**
	 * Write value for a key into cache
	 *
	 * @param string $guid Identifier for the data
	 * @param mixed  $data Data to be cached
	 *
	 * @param int    $specificLifetime
	 *
	 * @return boolean True if the data was succesfully cached, false on failure
	 */
	abstract public function write($guid, $data, $specificLifetime = null);

	/**
	 * Read a key from the cache
	 *
	 * @param string $guid Identifier for the data
	 *
	 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
	 */
	abstract public function read($guid);

	/**
	 * Delete a key from the cache
	 *
	 * @param string $guid Identifier for the data
	 *
	 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
	 */
	abstract public function delete($guid);

	/**
	 * set a new lifetime to the given cache id
	 *
	 * @param string $guid cache id
	 * @param int    $lifetime
	 *
	 * @return boolean true if ok
	 */
	abstract public function touch($guid, $lifetime);
}