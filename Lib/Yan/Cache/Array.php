<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Array.php 16 2012-04-23 14:32:49Z kakalong $
 */

require_once 'Yan/Cache/Abstract.php';

/**
 * Array-cache Adapter
 *
 * @category   Yan
 * @package    Yan_Cache
 */
class Yan_Cache_Array extends Yan_Cache_Abstract
{
	protected $_options = array(
		'lifetime' => false,
		'prefix'   => false,
		'cacheDir' => false,
		'dirDepth' => false,
		'fileUmask'=> false,
		'dirUmask' => false
	);

	protected $_config = array(
		'prefix'   => 'cache',
		'dirDepth' => 0,
		'lifetime' => 3600,
		'fileUmask'=> 0600,
		'dirUmask' => 0700
	);

	protected function _init(){
		if (($cacheDir = $this->_config['cacheDir']) != null) {
			if (!$this->_isValidDir($cacheDir)) {
				require_once 'Yan/Cache/Exception.php';
				throw new Yan_Cache_Exception("Unavailable cache dir:$cacheDir");
			}
		} else {
			$this->_config['cacheDir'] = $this->_getTmpDir();
		}
		if (! preg_match('#^[\w]+$#', $this->_config['prefix'])) {
			require_once 'Yan/Cache/Exception.php';
			throw new Yan_Cache_Exception('Invalid filename prefix : must use only [a-zA-A0-9_]');
		}
		if (is_string($this->_config['dirUmask'])) {
			$this->_config['dirUmask'] = octdec($this->_config['dirUmask']);
		}
		if (is_string($this->_config['fileUmask'])) {
			$this->_config['fileUmask'] = octdec($this->_config['fileUmask']);
		}
	}

	protected function _getTmpDir()
	{
		$tmpdir = array();
		foreach (array($_ENV, $_SERVER) as $tab) {
			foreach (array('TMPDIR', 'TEMP', 'TMP', 'windir', 'SystemRoot') as $key) {
				if (isset($tab[$key])) {
					if (($key == 'windir') or ($key == 'SystemRoot')) {
						$dir = realpath($tab[$key] . '\\temp');
					} else {
						$dir = realpath($tab[$key]);
					}
					if ($this->_isValidDir($dir)) {
						return $dir;
					}
				}
			}
		}
		$upload = ini_get('upload_tmp_dir');
		if ($upload) {
			$dir = realpath($upload);
			if ($this->_isValidDir($dir)) {
				return $dir;
			}
		}
		if (function_exists('sys_get_temp_dir')) {
			$dir = sys_get_temp_dir();
			if ($this->_isValidDir($dir)) {
				return $dir;
			}
		}
		// Attemp to detect by creating a temporary file
		$tempFile = tempnam(md5(uniqid(mt_rand(5, 15), true)), '');
		if ($tempFile) {
			$dir = realpath(dirname($tempFile));
			unlink($tempFile);
			if ($this->_isValidDir($dir)) {
				return $dir;
			}
		}
		require_once 'Yan/Cache/Exception.php';
		throw new Yan_Cache_Exception(
			'Could not determine temp directory, please specify a cache_dir manually'
		);
	}

	protected function _isValidDir($dir)
	{
		if (is_dir($dir) || $dir=='') {
			return is_readable($dir) && is_writable($dir);
		}
		if (is_file($dir)) {
			return false;
		}
		if (!$this->_isValidDir(dirname($dir))) {
			return false;
		}
		return @mkdir($dir, 0755);
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function clean($mode = Yan_Cache::CLEANING_ALL)
	{
		clearstatcache();
		return $this->_clean($this->_config['cacheDir'],$mode);
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function write($guid, $data, $specificLifetime = null) {
		if(!is_array($data)){
			if (is_object($data) && method_exists($data,'toArray'))
			{
				$data = $data->toArray();
			} else {
				return false;
			}
		}
		$data  = '<?php'."\n"
			. 'return '.var_export($data,true)
			. ';';
		$path = $this->_path($guid, true);
		if (!is_writable(dirname($path))) {
			return false;
		}
		$lifetime = $specificLifetime ? $specificLifetime : $this->_config['lifetime'];
		try {
			file_put_contents($path, $data, LOCK_EX);
			chmod($path, $this->_config['fileUmask']);
			return touch($path, time() + $lifetime);
		} catch(Exception $e) {
			return false;
		}
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function read($guid) {
		$path = $this->_path($guid);
		if (!is_file($path) || $this->_expired($path)) {
			return null;
		}
		$data = include($path);
		return is_array($data) ? $data : null;
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function delete($guid) {
		$path = $this->_path($guid);
		if (is_file($path)) {
			return @unlink($path);
		}
		return true;
	}

	/**
	 * @see Yan_Cache_Abstract
	 */
	public function touch($guid, $lifetime) {
		$path = $this->_path($guid);
		return is_file($path) && touch($path, time() + $lifetime);
	}

	protected function _clean($dir,$mode) {
		$prefix = $this->_config['prefix'];
		$glob = @glob($dir . '/' . $prefix . '--*');
		if ($glob === false) {
			return true;
		}
		$result = true;
		foreach ($glob as $file) {
			if (is_file($file)) {
				switch ($mode) {
					case Yan_Cache::CLEANING_ALL:
						$result = $result && ( @unlink($file) );
						break;
					case Yan_Cache::CLEANING_OLD:
						if ($this->_expired($file)) {
							$result = $result && ( @unlink($file) );
						}
						break;
				}
			} elseif (is_dir($file) && $this->_config['dirDepth']>0) {
				$result = $result && $this->_clean($file, $mode);
				if ($mode == Yan_Cache::CLEANING_ALL) {
					@rmdir($file);
				}
			}
		}
		return $result;
	}

	protected function _expired($file) {
		if (empty($this->_config['lifetime'])) {
			return false;
		}
		return filemtime($file) < time();
	}

	protected function _path($guid, $cr = false) {
		$root = $this->_config['cacheDir'];
		$prefix = $this->_config['prefix'];
		$depth = $this->_config['dirDepth'];
		$umask = $this->_config['dirUmask'];
		if ($depth > 0) {
			for ($i=0 ; $i < $depth ; $i++) {
				$root = $root . '/' . $prefix . '--' . substr($guid, 0, $i + 1);
				if (!$cr || is_dir($root)) { continue; }
				@mkdir($root, $umask);
			}
		}
		return $root . '/' . $prefix . '---' . $guid . '.php';
	}
}