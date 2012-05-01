<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Abstract.php 17 2012-04-24 02:28:24Z kakalong $
 */

/**
 * Yan_View_Abstract
 *
 * @category  Yan
 * @package   Yan_View
 */
abstract class Yan_View_Abstract
{
	/**
	 * template variables
	 *
	 * @var array
	 */
	protected $_variables = array();

	/**
	 * @param array|Yan_Config $options
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		} elseif ($options instanceof Yan_Config) {
			$this->setOptions($options->toArray());
		}
	}

	/**
	 * setOptions
	 *
	 * @param array $options
	 * @return Yan_View_Abstract
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key=>$value) {
			$method = 'set' . ucfirst ($key);
			if (method_exists ($this, $method)) {
				// Setter exists; use it
				$this->$method($value);
			}
		}
		return $this;
	}

	/**
	 * Return the template engine object, if any
	 *
	 * If using a third-party template engine, such as Smarty, patTemplate,
	 * phplib, etc, return the template engine object. Useful for calling
	 * methods on these objects, such as for setting filters, modifiers, etc.
	 *
	 * @return engine object
	 */
	public function getEngine()
	{
		return $this;
	}

	/**
	 * Assign a variable to the view
	 *
	 * @param string $key The variable name.
	 * @param mixed $val The variable value.
	 * @return void
	 */
	public function __set($key, $val)
	{
		$this->_variables[$key] = $val;
	}

	/**
	 * Allows testing with empty() and isset() to work
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->_variables[$key]);
	}

	/**
	 * Allows unset() on object properties to work
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->_variables[$key]);
	}

	/**
	 * Assign variables to the view script via differing strategies.
	 *
	 * @param string|array $spec The assignment strategy to use (key or array of key
	 * => value pairs)
	 * @param mixed $value (Optional) If assigning a named variable, use this
	 * as the value.
	 * @return Yan_View_Abstract
	 */
	public function assign($spec, $value = null)
	{
		if (is_string($spec)) {
			$this->__set($spec,$value);
		} elseif (is_array($spec)) {
			foreach ($spec as $k => $v) {
				$this->__set($k,$v);
			}
		} else {
			require_once 'Yan/View/Exception.php';
			throw new Yan_View_Exception('assign() expects a string or array, received ' . gettype($spec));
		}
	}

	/**
	 * Clear all assigned variables
	 *
	 * @return Yan_View_Abstract
	 */
	public function clearVars()
	{
		$this->_variables = array();
		return $this;
	}

	/**
	 * set Script file
	 *
	 * $path is relative to templatebase or compliledbase
	 *
	 * @param string $path
	 * @return Yan_View_Abstract
	 */
	abstract public function setScript($path);

	/**
	 * set Compiled template file base directory
	 *
	 * @param string $dir
	 * @return Yan_View_Abstract
	 */
	abstract public function setCompiledBase($dir);

	/**
	 * set Template file base directory
	 *
	 * @param sting $dir
	 * @return Yan_View_Abstract
	 */
	abstract public function setTemplateBase($dir);

	/**
	 * Processes a view script and returns the output.
	 *
	 * @return string The script output.
	 */
	abstract public function render();
}
