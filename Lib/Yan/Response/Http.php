<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Http.php 19 2012-04-28 02:42:04Z kakalong $
 */

require_once 'Yan/Response/Abstract.php';

/**
 * Yan_Response_Http
 *
 * @category   Yan
 * @package    Yan_Response
 */
class Yan_Response_Http extends Yan_Response_Abstract
{
	/**
	 * Array of headers. Each header is an array with keys 'name' and 'value'
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * Array of raw headers. Each header is a single string, the entire header to emit
	 * @var array
	 */
	protected $_headersRaw = array();

	/**
	 * HTTP response code to use in headers
	 * @var int
	 */
	protected $_httpResponseCode = 200;

	/**
	 * Flag; is this response a redirect?
	 * @var boolean
	 */
	protected $_isRedirect = false;

	/**
	 * Normalize a header name
	 *
	 * Normalizes a header name to X-Capitalized-Names
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function _normalizeHeader($name)
	{
		$filtered = str_replace(array('-', '_'), ' ', (string) $name);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);
		return $filtered;
	}

	/**
	 * Set a header
	 *
	 * If $replace is true, replaces any headers already defined with that
	 * $name.
	 *
	 * @param string $name
	 * @param string $value
	 * @param boolean $replace
	 * @return Yan_Response_Http
	 */
	public function setHeader($name, $value, $replace = false)
	{
		$this->canSendHeaders(true);
		$name  = $this->_normalizeHeader($name);
		$value = (string) $value;

		if ($replace) {
			foreach ($this->_headers as $key => $header) {
				if ($name == $header['name']) {
					unset($this->_headers[$key]);
				}
			}
		}

		$this->_headers[] = array(
			'name'    => $name,
			'value'   => $value,
			'replace' => $replace
		);

		return $this;
	}

	/**
	 * Set a cookie
	 *
	 * @return Yan_Response_Http
	 */
	public function setCookie($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = false,$httponly = false)
	{
		$this->canSendHeaders(true);
		$value = $name.'='.((string) $value);
		if(is_int($expire) && $expire) {
			$value .= '; expires='.gmdate('D,d M Y H:i:s', $expire).' GMT';
		}
		if(!is_null($path)){
			$value .= '; path='.((string) $path);
		}
		if(!is_null($domain)){
			$value .= '; domain='.((string) $domain);
		}
		if($secure){
			$value .= '; secure';
		}
		if($httponly){
			$value .= '; httponly';
		}
		$this->setHeader('Set-Cookie',$value);
		return $this;
	}

	/**
	 * Set redirect URL
	 *
	 * Sets Location header and response code. Forces replacement of any prior
	 * redirects.
	 *
	 * @param string $url
	 * @param int $code
	 * @return Yan_Response_Http
	 */
	public function setRedirect($url, $code = 302)
	{
		$this->canSendHeaders(true);
		$this->setHeader('Location', $url, true)
			 ->setHttpResponseCode($code);

		return $this;
	}

	/**
	 * Is this a redirect?
	 *
	 * @return boolean
	 */
	public function isRedirect()
	{
		return $this->_isRedirect;
	}

	/**
	 * Clear headers
	 *
	 * @return Yan_Response_Http
	 */
	public function clearHeaders()
	{
		$this->_headers = array();

		return $this;
	}

	/**
	 * Set raw HTTP header
	 *
	 * Allows setting non key => value headers, such as status codes
	 *
	 * @param string $value
	 * @return Yan_Response_Http
	 */
	public function setRawHeader($value){
		$this->canSendHeaders(true);
		if ('Location' == substr($value, 0, 8)) {
			$this->_isRedirect = true;
		}
		$this->_headersRaw[] = (string) $value;
		return $this;
	}

	/**
	 * Clear all {@link setRawHeader() raw HTTP headers}
	 *
	 * @return Yan_Response_Http
	 */
	public function clearRawHeaders()
	{
		$this->_headersRaw = array();
		return $this;
	}

	/**
	 * Clear all headers, normal and raw
	 *
	 * @return Yan_Response_Http
	 */
	public function clearAllHeaders()
	{
		return $this->clearHeaders()->clearRawHeaders();
	}

	/**
	 * Set HTTP response code to use with headers
	 *
	 * @param int $code
	 * @return Yan_Response_Http
	 */
	public function setHttpResponseCode($code)
	{
		if (!is_int($code) || (100 > $code) || (599 < $code)) {
			throw new Yan_Response_Exception('Invalid HTTP response code');
		}

		if ((300 <= $code) && (307 >= $code)) {
			$this->_isRedirect = true;
		} else {
			$this->_isRedirect = false;
		}

		$this->_httpResponseCode = $code;
		return $this;
	}

	/**
	 * Can we send headers
	 *
	 * @param boolean $throw Whether or not to throw an exception if headers have been sent; defaults to false
	 * @return boolean
	 * @throws Yan_Response_Exception
	 */
	public function canSendHeaders($throw = false)
	{
		$ok = headers_sent($file, $line);
		if ($ok && $throw) {
			throw new Yan_Response_Exception('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
		}
		return !$ok;
	}

	/**
	 * Send all headers
	 *
	 * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
	 * has been specified, it is sent with the first header.
	 *
	 * @return Yan_Response_Http
	 */
	public function header()
	{
		// Do not send 200 code
		$httpCodeNeedSent = 200 == $this->_httpResponseCode;

		// Only check if we can send headers if we have headers to send
		if (count($this->_headersRaw) || count($this->_headers) || $httpCodeNeedSent) {
			$this->canSendHeaders(true);
		} elseif (!$httpCodeNeedSent) {
			// Haven't changed the response code, and we have no headers
			return $this;
		}

		if ($httpCodeNeedSent && ($header = array_shift($this->_headersRaw))) {
			header($header, true, $this->_httpResponseCode);
			$httpCodeNeedSent = false;
		}

		foreach ($this->_headersRaw as $header) {
			header($header);
		}

		if ($httpCodeNeedSent && ($header = array_shift($this->_headers))) {
			header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
			$httpCodeNeedSent = false;
		}

		foreach ($this->_headers as $header) {
			header($header['name'] . ': ' . $header['value'], $header['replace']);
		}

		if ($httpCodeNeedSent) {
			header('HTTP/1.1 ' . $this->_httpResponseCode);
		}

		return $this;
	}

	/**
	 * send the body contents, including all headers
	 *
	 * @return void
	 */
	public function send()
	{
		$this->header();
		exit ($this->getBody());
	}
}