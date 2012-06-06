<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

require_once 'Yan/Cache/Array.php';

/**
 * File-cache Adapter
 *
 * @category   Yan
 * @package    Yan_Cache
 */
class Yan_Cache_File extends Yan_Cache_Array
{

	protected $_options = array(
		'lifetime'  => false,
		'prefix'    => false,
		'cacheDir'  => false,
		'dirDepth'  => false,
		'fileUmask' => false,
		'dirUmask'  => false,
		'readTest'  => false,
		'testType'  => false
	);

	protected $_config = array(
		'prefix'    => 'cache',
		'cacheDir'  => null,
		'dirDepth'  => 0,
		'lifetime'  => 3600,
		'fileUmask' => 0600,
		'dirUmask'  => 0700,
		'readTest'  => true,
		'testType'  => 'crc32'
	);

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function write($guid, $data, $specificLifetime = null)
	{
		if (is_string($data)) {
			$serialization = 0;
		} else {
			$data = serialize($data);
			$serialization = 1;
		}
		// 构造缓存文件头部
		$head = pack('vv', $serialization, $this->_config['readTest']);
		if ($this->_config['readTest']) {
			$head .= sprintf('% 6s', $this->_config['testType']);
			$head .= $this->_hash($data, $this->_config['testType']);
		}
		$data = $head . $data;

		$path = $this->_path($guid, true);
		if (!is_writable(dirname($path))) {
			return false;
		}
		$lifetime = $specificLifetime ? $specificLifetime : $this->_config['lifetime'];
		try {
			file_put_contents($path, $data, LOCK_EX);
			chmod($path, $this->_config['fileUmask']);
			return touch($path, time() + $lifetime);
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function read($guid)
	{
		$path = $this->_path($guid);
		if (!is_file($path) || $this->_expired($path)) {
			return null;
		}
		try {
			$f = fopen($path, 'rb');
			@flock($f, LOCK_SH);
			$policy = fread($f, 4);
			if (strlen($policy) < 4) {
				return null;
			}
			$policy = unpack('vserialization/vreadTest', $policy);
			if ($policy['readTest']) {
				$policy['testType'] = trim(fread($f, 6));
				$policy['crc'] = fread($f, 32);
			}
			$data = stream_get_contents($f);
			@flock($f, LOCK_UN);
			fclose($f);
			if ($policy['readTest']
				&& $this->_hash($data, $policy['testType']) != $policy['crc']
			) {
				// set expired
				touch($path, time() - 3600);
				return null;
			}
			if ($policy['serialization']) {
				$data = unserialize($data);
			}
			return $data;
		} catch (Exception $e) {
			return null;
		}
	}

	/**
	 * 获得数据的校验码
	 *
	 * @param string $data
	 * @param string $type
	 *
	 * @return string
	 */
	protected function _hash($data, $type)
	{
		switch ($type) {
		case 'md5':
			return md5($data);
		case 'crc32':
			return sprintf('% 32d', crc32($data));
		case 'strlen':
		default:
			return sprintf('% 32d', strlen($data));
		}
	}
}