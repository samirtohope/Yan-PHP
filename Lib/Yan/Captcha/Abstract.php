<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Captcha_Abstract
 *
 * @category  Yan
 * @package   Yan_Captcha
 */
abstract class Yan_Captcha_Abstract
{

	/**
	 * @param array|Yan_Config $options
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		} else if ($options instanceof Yan_Config) {
			$this->setOptions($options->toArray());
		}
	}

	/**
	 * init captcha options
	 *
	 * @param array $options
	 * @return Yan_Captcha_Abstract
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (method_exists($this, $method)) {
				// Setter exists; use it
				$this->$method($value);
			}
		}
		return $this;
	}

	/**
	 * get contentType of captcha
	 *
	 * @return string
	 */
	abstract public function getContentType();

	/**
	 * output captcha
	 *
	 * @param boolean $return
	 * @return string output if set $return true
	 */
	abstract public function render($return = false);
}