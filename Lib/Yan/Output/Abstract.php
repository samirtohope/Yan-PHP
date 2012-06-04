<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Output_Abstract
 *
 * @category  Yan
 * @package   Yan_Output
 */
abstract class Yan_Output_Abstract
{

	/**
	 * Response object
	 *
	 * @var Yan_Response_Abstract
	 */
	protected $_response;

	/**
	 * @param Yan_Response_Abstract $response
	 * @param array $options
	 */
	final public function __construct(Yan_Response_Abstract $response, array $options = array())
	{
		$this->_response = $response;
		$this->setOptions($options);
	}

	/**
	 * init the options
	 *
	 * @param array $options
	 * @return Yan_Output_Abstract
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
	 * output the body content
	 *
	 * @return string
	 */
	abstract public function outputBody();
}