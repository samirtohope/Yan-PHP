<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Output/Abstract.php';

/**
 * Yan_Output_View
 *
 * @category  Yan
 * @package   Yan_Output
 */
class Yan_Output_View extends Yan_Output_Abstract
{
	const COMPRESS_ANY = '*';
	const COMPRESS_GZIP = 'gzip';
	const COMPRESS_DEFLATE = 'deflate';

	/**
	 * method of compress, 0 is no compress
	 *
	 * @var string
	 */
	protected $_compress = 0;

	/**
	 * server cache adapter
	 *
	 * @var Yan_Cache_Abstract
	 */
	protected $_cacheAdapter = null;

	/**
	 * guid of current request
	 *
	 * @var string
	 */
	protected $_guid = null;

	/**
	 * View object of render
	 *
	 * @var Yan_View_Abstract
	 */
	protected $_view = null;

	/**
	 * charset of output
	 *
	 * @var string
	 */
	protected $_charset = 'utf-8';

	/**
	 * content type of output
	 *
	 * @var string
	 */
	protected $_contentType = 'text/html';

	/**
	 * body of output
	 *
	 * @var string
	 */
	protected $_body = '';

	/**
	 * set object of view engine
	 *
	 * @param string|array|Yan_View_Abstract $view
	 * @return Yan_Output_View
	 * @throws Yan_Output_Exception
	 */
	public function setView($view)
	{
		if (is_string($view)) {
			Yan::loadClass($view);
			$view = new $view();
		} elseif (is_array($view)) {
			if (!array_key_exists('class', $view)) {
				require_once 'Yan/Output/Exception.php';
				throw new Yan_Output_Exception('View class not provided in options');
			}
			$class = $view['class'];
			unset($view['class']);
			Yan::loadClass($class);
			$view = new $class($view);
		}
		if (!$view instanceof Yan_View_Abstract) {
			require_once 'Yan/Output/Exception.php';
			throw new Yan_Output_Exception('Not valid type of view instance.');
		}
		$this->_view = $view;
		return $this;
	}

	/**
	 * retrieve view object
	 *
	 * @return Yan_View_Abstract
	 */
	public function getView()
	{
		return $this->_view;
	}

	/**
	 * set content type of output
	 *
	 * @param string $contentType
	 * @return Yan_Output_View
	 */
	public function setContentType($contentType)
	{
		$this->_contentType = $contentType;
		return $this;
	}

	/**
	 * set charset of output
	 *
	 * @param string $charset
	 * @return Yan_Output_View
	 */
	public function setCharset($charset)
	{
		$this->_charset = $charset;
		return $this;
	}

	/**
	 * set compress method
	 *
	 * @param string $type
	 * @return Yan_Output_View
	 */
	public function setCompress($type = self::COMPRESS_ANY)
	{
		$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], self::COMPRESS_GZIP);
		$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], self::COMPRESS_DEFLATE);
		switch ($type) {
		case self::COMPRESS_GZIP:
			$compress = $gzip ? self::COMPRESS_GZIP : false;
			break;
		case self::COMPRESS_DEFLATE:
			$compress = $deflate ? self::COMPRESS_DEFLATE : false;
			break;
		case self::COMPRESS_ANY:
		default:
			$compress = $gzip ? self::COMPRESS_GZIP : ($deflate ? self::COMPRESS_DEFLATE : false);
		}
		if ($compress && function_exists('gzencode')) {
			$this->_response->setHeader('Content-Encoding', $compress, true);
			$this->_compress = $compress;
		}
		return $this;
	}

	/**
	 * set sever cache adapter
	 *
	 * @param string|array|Yan_Cache_Abstract $cacher
	 * @return Yan_Output_View
	 * @throws Yan_Output_Exception
	 */
	public function setServerCache($cacher)
	{
		if (is_string($cacher)) {
			Yan::loadClass($cacher);
			$cacher = new $cacher();
		} elseif (is_array($cacher)) {
			if (!array_key_exists('class', $cacher)) {
				require_once 'Yan/Output/Exception.php';
				throw new Yan_Output_Exception('Cache class not provided in options');
			}
			$class = $cacher['class'];
			unset($cacher['class']);
			Yan::loadClass($class);
			$cacher = new $class($cacher);
		}
		if (!$cacher instanceof Yan_Cache_Abstract) {
			require_once 'Yan/Output/Exception.php';
			throw new Yan_Output_Exception('Not valid type of CacheAdapter instance.');
		}
		if (null != ($data = $cacher->read($this->getId()))) {
			$this->_body = $data;
			$this->_response->setBody($this)->send();
		}

		$this->_cacheAdapter = $cacher;
		return $this;
	}

	/**
	 * turn on client cache
	 *
	 * @param int $cacheLife
	 * @return Yan_Output_View
	 */
	public function setClientCache($cacheLife = null)
	{
		$cacheLife = (int)$cacheLife;
		if ($cacheLife < 5) {
			return $this;
		}
		$now = time();
		$guid = $this->getId();
		if (isset($_COOKIE[$guid]) && ($_COOKIE[$guid] > $now - $cacheLife)) {
			return $this->_response->setBody(null)->setHttpResponseCode(304)->send();
		}
		$this->_response->setCookie($guid, $now, $now + 86400)
			->setHeader('Last-Modified', gmdate('D,d M Y H:i:s', $now) . ' GMT')
			->setHeader('Expires', gmdate('D, d M Y H:i:s', $now + $cacheLife) . ' GMT')
			->setHeader('Cache-Control', 'public,max-age=' . $cacheLife);
		return $this;
	}

	/**
	 * get output body content
	 *
	 * @return string
	 */
	public function outputBody()
	{
		$this->_response->setHeader('Content-type',
			"{$this->_contentType}; Charset={$this->_charset};");
		if ($this->_body === '' && null != $this->_view) {
			$this->_body = $this->_view->render();
		}
		$body = $this->_body;
		if (null != $this->_cacheAdapter) {
			$this->_cacheAdapter->write($this->getId(), $body);
		}
		if ($this->_compress) {
			$body = gzencode($body, 9,
				$this->_compress == self::COMPRESS_GZIP ? FORCE_GZIP : FORCE_DEFLATE);
		}
		echo $body;
	}

	/**
	 * genarate a request guid
	 *
	 * @return string
	 */
	public function getId()
	{
		if (null == $this->_guid) {
			$this->_guid = md5($_SERVER['PHP_SELF']);
		}
		return $this->_guid;
	}
}