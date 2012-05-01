<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Captcha.php 20 2012-04-28 05:55:14Z kakalong $
 */

require_once 'Yan/Output/Abstract.php';

/**
 * Yan_Output_Captcha
 *
 * @category   Yan
 * @package    Yan_Output
 */
class Yan_Output_Captcha extends Yan_Output_Abstract
{
	/**
	 * the captcha render engine
	 *
	 * @var Yan_Captcha_Abstract
	 */
	protected $_captcha = null;

	/**
	 * set captcha of render engine
	 *
	 * @param string|array|Yan_Captcha_Abstract $captcha
	 * @return Yan_Output_Captcha
	 * @throws Yan_Output_Exception
	 */
	public function setCaptcha($captcha)
	{
		if (is_string($captcha)) {
			Yan::loadClass($captcha);
			$captcha = new $captcha();
		} elseif (is_array($captcha)) {
			if (! array_key_exists('class', $captcha)) {
				require_once 'Yan/Output/Exception.php';
				throw new Yan_Output_Exception('View class not provided in options');
			}
			$class = $captcha['class'];
			unset($captcha['class']);
			Yan::loadClass($class);
			$captcha = new $class($captcha);
		}
		if (! $captcha instanceof Yan_Captcha) {
			require_once 'Yan/Output/Exception.php';
			throw new Yan_Output_Exception('Not valid type of captcha instance.');
		}
		$this->_captcha = $captcha;
		return $this;
	}

	/**
	 * retrieve captcha render engine
	 *
	 * @return Yan_Captcha_Abstract
	 */
	public function getCaptcha()
	{
		return $this->_captcha;
	}

	/**
	 * get body of captcha ouput
	 *
	 * @return string
	 */
	public function getBody()
	{
		if (null != $this->_captcha) {
			$this->_response->setHeader('Content-type', $this->_captcha->getContentType());
			return $this->_captcha->render();
		} else {
			return '';
		}
	}
}