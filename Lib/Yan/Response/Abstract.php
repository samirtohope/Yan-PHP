<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Response
 *
 * @category   Yan
 * @package    Yan_Response
 */
abstract class Yan_Response_Abstract
{
	protected $_body = null;

	/**
	 * Set body content
	 *
	 * @param string|object $content
	 *
	 * @return Yan_Response_Abstract
	 */
	public function setBody($content)
	{
		if (is_object($content) && method_exists($content, 'outputBody')) {
			$this->_body = $content;
		} else {
			$this->_body = (string)$content;
		}
		return $this;
	}

	/**
	 * Return the body content
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->_body;
	}

	/**
	 * Output the body content
	 *
	 * @return void
	 */
	public function outputBody()
	{
		if (is_object($this->_body)) {
			$this->_body->outputBody();
		} elseif (!is_null($this->_body)) {
			echo $this->_body;
		}
	}

	/**
	 * Magic __toString functionality
	 *
	 * @return string
	 */
	public function __toString()
	{
		ob_start();
		$this->outputBody();
		return ob_get_clean();
	}

	/**
	 * send response and exit
	 */
	public function send()
	{
		$this->outputBody();
		exit;
	}
}