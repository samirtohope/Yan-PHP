<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Abstract.php 19 2012-04-28 02:42:04Z kakalong $
 */

/**
 * Response
 *
 * @category   Yan
 * @package    Yan_Response
 */
abstract class Yan_Response_Abstract
{
	protected $_output = null;

	protected $_body = '';

	/**
	 * Set body content
	 *
	 * If $name is not passed, or is not a string, resets the entire body and
	 * sets the 'default' key to $content.
	 *
	 * If $name is a string, sets the named segment in the body array to
	 * $content.
	 *
	 * @param string $content
	 * @return Yan_Response
	 */
	public function setBody($content)
	{
		$this->_body = (string) $content;
		return $this;
	}

	/**
	 * Return the body content
	 *
	 * If $spec is false, returns the concatenated values of the body content
	 * array. If $spec is boolean true, returns the body content array. If
	 * $spec is a string and matches a named segment, returns the contents of
	 * that segment; otherwise, returns null.
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->_body;
	}

	public function send()
	{
		exit ($this->getBody());
	}
}