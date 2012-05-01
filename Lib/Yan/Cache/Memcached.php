<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Memcached.php 15 2012-04-23 11:33:00Z kakalong $
 */

require_once 'Yan/Cache/Abstract.php';

/**
 * Memcached-cache Adapter
 *
 * @category   Yan
 * @package    Yan_Cache
 */
class Yan_Cache_Memcached extends Yan_Cache_Abstract
{

	protected $_memcache;

	protected $_options = array(
		'lifetime' => false,
		'servers' => false,
		'compression' => false,
	);

	protected $_config = array(
		'lifetime' => 3600,
		'servers' => array(
			'127.0.0.1:11211'
		),
		/**
		 * 是否压缩缓存数据
		 */
		'compression' => false
	);

	const DEFAULT_HOST = '127.0.0.1';
	const DEFAULT_PORT =  11211;
	const DEFAULT_PERSISTENT = true;
	const DEFAULT_WEIGHT  = 1;
	const DEFAULT_TIMEOUT = 1;

	protected function _init(){
		if (! extension_loaded('memcache')) {
			require_once 'Yan/Cache/Exception.php';
			throw new Yan_Cache_Exception('The memcache extension must be loaded before use!');
		}

		if (! is_array($this->_config['servers'])) {
			$this->_config['servers'] = array($this->_config['servers']);
		}
		$this->_memcache = new Memcache();
		foreach ($this->_config['servers'] as $server){
			if (is_string($server)) {
				$server = explode(':', $server);
				$host = $server[0];
				$port = 11211;
				if (! empty($server[1])) {
					$port = (int) $server[1];
				}
				$persistent = self::DEFAULT_PERSISTENT;
				$weight = self::DEFAULT_WEIGHT;
				$timeout = self::DEFAULT_TIMEOUT;
			} elseif (is_array($server)) {
				$host = array_key_exists('host', $server) ? $server['host'] : self::DEFAULT_HOST;
				$port = array_key_exists('port', $server) ? $server['port'] : self::DEFAULT_PORT;
				$persistent = array_key_exists('persistent', $server) ? $server['persistent'] : self::DEFAULT_PERSISTENT;
				$weight = array_key_exists('weight', $server) ? $server['weight'] : self::DEFAULT_WEIGHT;
				$timeout = array_key_exists('timeout', $server) ? $server['timeout'] : self::DEFAULT_TIMEOUT;
			} else {
				continue;
			}
			$this->_memcache->addServer($host, $port, $persistent, $weight, $timeout);
		}
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function clean($mode = Yan_Cache::CLEANING_ALL)
	{
		if ($mode == Yan_Cache::CLEANING_ALL) {
			return $this->_memcache->flush();
		}
		return false;
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function write($guid, $data, $specificLifetime = null) {
		if ($this->_config['compression']) {
			$flag = MEMCACHE_COMPRESSED;
		} else {
			$flag = 0;
		}
		$lifetime = $specificLifetime ? $specificLifetime : $this->_config['lifetime'];
		if (false === $this->_memcache->add($guid, $data, $flag, $lifetime)) {
		   return $this->_memcache->replace($guid, $data, $flag, $lifetime);
		}
		return true;
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function read($guid) {
		return $this->_memcache->get($guid);
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function delete($guid) {
		return $this->_memcache->delete($guid);
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function touch($guid, $lifetime) {
		if ($this->_config['compression']) {
			$flag = MEMCACHE_COMPRESSED;
		} else {
			$flag = 0;
		}
		$data = $this->_memcache->get($guid);
		if ($data) {
			return $this->_memcache->replace($guid, $data, $flag, $lifetime);
		}
		return false;
	}
}