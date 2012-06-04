<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Request/Abstract.php';

/**
 * Yan_Request_Http
 *
 * @category  Yan
 * @package   Yan_Request
 */
class Yan_Request_Http extends Yan_Request_Abstract
{
	/**
	 * REQUEST_URI
	 * @var string;
	 */
	protected $_requestUri;

	/**
	 * Base URL of request
	 * @var string
	 */
	protected $_baseUrl = null;

	/**
	 * Base path of request
	 * @var string
	 */
	protected $_basePath = null;

	/**
	 * PATH_INFO
	 * @var string
	 */
	protected $_pathInfo = '';

	/**
	 * Raw request body
	 * @var string|false
	 */
	protected $_rawBody;

	public function __construct($uri = null)
	{
		if (null !== $uri) {
			if (!$uri instanceof Yan_Uri) {
				require_once 'Yan/Uri.php';
				$uri = new Yan_Uri($uri);
			}
			$path = $uri->getPath();
			$query = $uri->getQuery();
			if (!empty($query)) {
				$path .= '?' . $query;
			}
			$uri = $path;
		}
		$this->setRequestUri($uri);
	}

	/**
	 * Check to see if a property is set
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		switch (true) {
		case isset($this->_params[$key]):
			return true;
		case isset($_GET[$key]):
			return true;
		case isset($_POST[$key]):
			return true;
		case isset($_COOKIE[$key]):
			return true;
		case isset($_SERVER[$key]):
			return true;
		case isset($_ENV[$key]):
			return true;
		default:
			return false;
		}
	}

	/**
	 * Access values contained in the superglobals as public members
	 * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
	 *
	 * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		switch (true) {
		case isset($this->_params[$key]):
			return $this->_params[$key];
		case isset($_GET[$key]):
			return $_GET[$key];
		case isset($_POST[$key]):
			return $_POST[$key];
		case isset($_COOKIE[$key]):
			return $_COOKIE[$key];
		case ($key == 'REQUEST_URI'):
			return $this->getRequestUri();
		case ($key == 'PATH_INFO'):
			return $this->getPathInfo();
		case isset($_SERVER[$key]):
			return $_SERVER[$key];
		case isset($_ENV[$key]):
			return $_ENV[$key];
		default:
			return null;
		}
	}

	/**
	 * Retrieve a member of the $_GET superglobal
	 *
	 * If no $key is passed, returns the entire $_GET array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getQuery($key = null, $default = null)
	{
		if (null === $key) {
			return $_GET;
		}
		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}

	/**
	 * Set GET values
	 *
	 * @param  string|array $spec
	 * @param  null|mixed $value
	 * @return Yan_Request_Http
	 */
	public function setQuery($spec, $value = null)
	{
		if ((null === $value) && !is_array($spec)) {
			require_once 'Yan/Request/Exception.php';
			throw new Yan_Request_Exception('Invalid value passed to setQuery()');
		}
		if ((null === $value) && is_array($spec)) {
			foreach ($spec as $key => $value) {
				$this->setQuery($key, $value);
			}
			return $this;
		}
		$_GET[(string)$spec] = $value;
		return $this;
	}

	/**
	 * Retrieve a member of the $_POST superglobal
	 *
	 * If no $key is passed, returns the entire $_POST array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getPost($key = null, $default = null)
	{
		if (null === $key) {
			return $_POST;
		}
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	/**
	 * Set POST values
	 *
	 * @param  string|array $spec
	 * @param  null|mixed $value
	 * @return Yan_Request_Http
	 */
	public function setPost($spec, $value = null)
	{
		if ((null === $value) && !is_array($spec)) {
			require_once 'Yan/Request/Exception.php';
			throw new Yan_Request_Exception('Invalid value passed to setPost()');
		}
		if ((null === $value) && is_array($spec)) {
			foreach ($spec as $key => $value) {
				$this->setPost($key, $value);
			}
			return $this;
		}
		$_POST[(string)$spec] = $value;
		return $this;
	}

	/**
	 * Retrieve a member of the $_COOKIE superglobal
	 *
	 * If no $key is passed, returns the entire $_COOKIE array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getCookie($key = null, $default = null)
	{
		if (null === $key) {
			return $_COOKIE;
		}
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
	}

	/**
	 * Retrieve a member of the $_SESSION superglobal
	 *
	 * If no $key is passed, returns the entire $_SESSION array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getSession($key = null, $default = null)
	{
		if (null === $key) {
			return $_SESSION;
		}
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	/**
	 * Retrieve a member of the $_SERVER superglobal
	 *
	 * If no $key is passed, returns the entire $_SERVER array.
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function getServer($key = null, $default = null)
	{
		if (null === $key) {
			return $_SERVER;
		}
		return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
	}

	/**
	 * Retrieve a member of the $_ENV superglobal
	 */
	public function getEnv($key = null, $default = null)
	{
		if (null === $key) {
			return $_ENV;
		}
		return isset($_ENV[$key]) ? $_ENV[$key] : $default;
	}

	/**
	 * Set the REQUEST_URI on which the instance operates
	 *
	 * @param string $requestUri
	 * @return Yan_Request_Http
	 */
	public function setRequestUri($requestUri = null)
	{
		if ($requestUri === null) {
			if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
				$requestUri = $_SERVER['HTTP_X_ORIGINAL_URL'];
			} elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
				$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
			}
			// IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
			elseif (isset($_SERVER['IIS_WasUrlRewritten']) && $_SERVER['IIS_WasUrlRewritten'] == '1'
				&& isset($_SERVER['UNENCODED_URL']) && $_SERVER['UNENCODED_URL'] != ''
			) {
				$requestUri = $_SERVER['UNENCODED_URL'];
			} elseif (isset($_SERVER['REQUEST_URI'])) {
				$requestUri = $_SERVER['REQUEST_URI'];
				// Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
				$schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();
				if (strpos($requestUri, $schemeAndHttpHost) === 0) {
					$requestUri = substr($requestUri, strlen($schemeAndHttpHost));
				}
			} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
				$requestUri = $_SERVER['ORIG_PATH_INFO'];
				if (!empty($_SERVER['QUERY_STRING'])) {
					$requestUri .= '?' . $_SERVER['QUERY_STRING'];
				}
			} else {
				return $this;
			}
		} elseif (!is_string($requestUri)) {
			return $this;
		} else {
			// Set GET items, if available
			if (false !== ($pos = strpos($requestUri, '?'))) {
				// Get key => value pairs and set $_GET
				$query = substr($requestUri, $pos + 1);
				parse_str($query, $vars);
				$this->setQuery($vars);
			}
		}

		$this->_requestUri = $requestUri;
		return $this;
	}

	/**
	 * Returns the REQUEST_URI taking into account
	 * platform differences between Apache and IIS
	 *
	 * @return string
	 */
	public function getRequestUri()
	{
		if (empty($this->_requestUri)) {
			$this->setRequestUri();
		}

		return $this->_requestUri;
	}

	/**
	 * Set the base path for the URL
	 *
	 * @param string|null $basePath
	 * @return Yan_Request_Http
	 */
	public function setBasePath($basePath = null)
	{
		if ($basePath === null) {
			$filename = (isset($_SERVER['SCRIPT_FILENAME']))
				? basename($_SERVER['SCRIPT_FILENAME'])
				: '';
			$baseUrl = $this->getBaseUrl();
			if (empty($baseUrl)) {
				$this->_basePath = '';
				return $this;
			}
			if (basename($baseUrl) === $filename) {
				$basePath = dirname($baseUrl);
			} else {
				$basePath = $baseUrl;
			}
		}

		$this->_basePath = rtrim(str_replace('\\', '/', $basePath), '/');

		return $this;
	}

	/**
	 * Everything in REQUEST_URI before PATH_INFO not including the filename
	 * <img src="<?=$basePath?>/images/logo.png"/>
	 *
	 * @return string
	 */
	public function getBasePath()
	{
		if (null === $this->_basePath) {
			$this->setBasePath();
		}

		return $this->_basePath;
	}

	/**
	 * Set the base URL of the request
	 *
	 * @param mixed $baseUrl
	 * @return Yan_Request_Http
	 */
	public function setBaseUrl($baseUrl = null)
	{
		if ((null !== $baseUrl) && !is_string($baseUrl)) {
			return $this;
		}

		if ($baseUrl === null) {
			$filename = isset($_SERVER['SCRIPT_FILENAME']) ? basename($_SERVER['SCRIPT_FILENAME']) : '';

			if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $filename) {
				$baseUrl = $_SERVER['SCRIPT_NAME'];
			} elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $filename) {
				$baseUrl = $_SERVER['PHP_SELF'];
			} elseif (isset($_SERVER['ORIG_SCRIPT_NAME'])
				&& basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename
			) {
				// 1and1 shared hosting compatibility
				$baseUrl = $_SERVER['ORIG_SCRIPT_NAME'];
			} else {
				// Backtrack up the script_filename to find the portion matching
				$path = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
				$file = isset($_SERVER['SCRIPT_FILENAME']) ? trim($_SERVER['SCRIPT_FILENAME'], '/') : '';
				$segs = array_reverse(explode('/', $file));
				$index = 0;
				$last = count($segs);
				$baseUrl = '';
				do {
					$baseUrl = '/' . $segs[$index] . $baseUrl;
					++$index;
				} while ($last > $index && strpos($path, $baseUrl));
			}

			if (empty($baseUrl)) {
				$this->_baseUrl = '';
				return $this;
			}

			$requestUri = $this->getRequestUri();

			if (0 === strpos($requestUri, $baseUrl)) {
				$this->_baseUrl = $baseUrl;
				return $this;
			}

			$basePath = rtrim(str_replace('\\', '/', dirname($baseUrl)), '/');
			if (strlen($basePath) && 0 === strpos($requestUri, $basePath)) {
				// directory portion of $baseurl matches
				$this->_baseUrl = $basePath;
				return $this;
			}

			if (($pos = strpos($requestUri, '?')) !== false) {
				$requestUri = substr($requestUri, 0, $pos);
			}

			$basename = basename($baseUrl);
			if (empty($basename) || !strpos($requestUri, $basename)) {
				// no match whatsoever; set it blank
				$this->_baseUrl = '';
				return $this;
			}

			// If using mod_rewrite or ISAPI_Rewrite strip the script filename
			// out of baseUrl. $pos !== 0 makes sure it is not matching a value
			// from PATH_INFO or QUERY_STRING
			if (strlen($requestUri) >= strlen($baseUrl) && ($pos = strpos($requestUri, $baseUrl))) {
				$baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
			}
		}

		$this->_baseUrl = rtrim($baseUrl, '/');
		return $this;
	}

	/**
	 * Everything in REQUEST_URI before PATH_INFO
	 * <form action="<?=$baseUrl?>/news/submit" method="POST"/>
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		if (null === $this->_baseUrl) {
			$this->setBaseUrl();
		}

		return $this->_baseUrl;
	}

	/**
	 * Set the PATH_INFO string
	 *
	 * @param string|null $pathInfo
	 * @return Yan_Request_Http
	 */
	public function setPathInfo($pathInfo = null)
	{
		if ($pathInfo === null) {
			$baseUrl = $this->getBaseUrl();
			$pathInfo = $this->getRequestUri();

			if (($pos = strpos($pathInfo, '?')) !== false) {
				$pathInfo = substr($pathInfo, 0, $pos);
			}
			if (strlen($baseUrl)) {
				if (strpos($pathInfo, $baseUrl) === 0) {
					$pathInfo = substr($pathInfo, strlen($baseUrl));
				}
			}
		}

		$this->_pathInfo = (string)$pathInfo;
		return $this;
	}

	/**
	 * Returns everything between the BaseUrl and QueryString.
	 * This value is calculated instead of reading PATH_INFO
	 * directly from $_SERVER due to cross-platform differences.
	 *
	 * @return string
	 */
	public function getPathInfo()
	{
		if (empty($this->_pathInfo)) {
			$this->setPathInfo();
		}

		return $this->_pathInfo;
	}

	/**
	 * Return the value of the given HTTP header. Pass the header name as the
	 * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
	 * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
	 *
	 * @param string $header HTTP header name
	 * @return string|null HTTP header value, or null if not found
	 */
	public function getHeader($header)
	{
		// Try to get it from the $_SERVER array first
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (!empty($_SERVER[$temp])) {
			return $_SERVER[$temp];
		}

		// This seems to be the only way to get the Authorization header on
		// Apache
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (!empty($headers[$header])) {
				return $headers[$header];
			}
		}

		return null;
	}

	/**
	 * Return the raw body of the request, if present
	 *
	 * @return string|false Raw body, or false if not present
	 */
	public function getRawBody()
	{
		if (null === $this->_rawBody) {
			$body = file_get_contents('php://input');

			if (strlen(trim($body)) > 0) {
				$this->_rawBody = $body;
			} else {
				$this->_rawBody = false;
			}
		}
		return $this->_rawBody;
	}

	/**
	 * Get the request URI scheme
	 *
	 * @return string
	 */
	public function getScheme()
	{
		return ($this->getServer('HTTPS') == 'on') ? 'https' : 'http';
	}

	/**
	 * Get the HTTP host.
	 *
	 * "Host" ":" host [ ":" port ] ; Section 3.2.2
	 * Note the HTTP Host header is not the same as the URI host.
	 * It includes the port while the URI host doesn't.
	 *
	 * @return string
	 */
	public function getHttpHost()
	{
		$host = $_SERVER['HTTP_HOST'];
		if (!empty($host)) {
			return $host;
		}

		$scheme = $this->getScheme();
		$name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
		$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';

		if (!strlen($name) || !strlen($port)) {
			return $name;
		}

		if (!(($scheme == 'http' && $port == 80) || ($scheme == 'https' && $port == 443))) {
			$name .= ':' . $port;
		}

		return $name;
	}

	/**
	 * Get the client's IP addres
	 *
	 * @param  boolean $checkProxy
	 * @return string
	 */
	public function getClientIp()
	{
		if ($this->getServer('HTTP_CLIENT_IP') != null) {
			$ip = $this->getServer('HTTP_CLIENT_IP');
		} elseif ($this->getServer('HTTP_X_FORWARDED_FOR') != null) {
			$ip = $this->getServer('HTTP_X_FORWARDED_FOR');
		} else {
			$ip = $this->getServer('REMOTE_ADDR');
		}
		return $ip;
	}

	/**
	 * Return the method by which the request was made
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->getServer('REQUEST_METHOD');
	}

	/**
	 * Was the request made by GET?
	 *
	 * @return boolean
	 */
	public function isGet()
	{
		return $this->getMethod() == 'GET';
	}

	/**
	 * Was the request made by POST?
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return $this->getMethod() == 'POST';
	}

	/**
	 * Was the request made by PUT?
	 *
	 * @return boolean
	 */
	public function isPut()
	{
		return $this->getMethod() == 'PUT';
	}

	/**
	 * Was the request made by HEAD?
	 *
	 * @return boolean
	 */
	public function isHead()
	{
		return $this->getMethod() == 'HEAD';
	}

	/**
	 * Is this a Flash request?
	 *
	 * @return bool
	 */
	public function isFlash()
	{
		$header = strtolower($this->getHeader('USER_AGENT'));
		return (strstr($header, ' flash')) ? true : false;
	}

	/**
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * Should work with Prototype/jQuery, possibly others.
	 *
	 * @return boolean
	 */
	public function isAjax()
	{
		if (($env = $this->getHeader('X_REQUESTED_WITH')) != null) {
			return $env == 'XMLHttpRequest';
		} else {
			return false;
		}
	}
}