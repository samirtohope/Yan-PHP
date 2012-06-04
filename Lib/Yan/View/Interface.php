<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_View_Interface
 *
 * @category  Yan
 * @package   Yan_View
 */
interface Yan_View_Interface
{
	/**
	 * Return the template engine object, if any
	 *
	 * If using a third-party template engine, such as Smarty, patTemplate,
	 * phplib, etc, return the template engine object. Useful for calling
	 * methods on these objects, such as for setting filters, modifiers, etc.
	 *
	 * @return engine object
	 */
	public function getEngine();

	/**
	 * set Script file
	 *
	 * $path is relative to templatebase or compliledbase
	 *
	 * @param string $path
	 * @return Yan_View
	 */
	public function setScript($path);

	/**
	 * set Compiled template file base directory
	 *
	 * @param string $dir
	 * @return Yan_View
	 */
	public function setCompiledBase($dir);

	/**
	 * set Template file base directory
	 *
	 * @param sting $dir
	 * @return Yan_View
	 */
	public function setTemplateBase($dir);

	/**
	 * Assign a variable to the view
	 *
	 * @param string $key The variable name.
	 * @param mixed $val The variable value.
	 * @return void
	 */
	public function __set($key, $val);

	/**
	 * Allows testing with empty() and isset() to work
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key);

	/**
	 * Allows unset() on object properties to work
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset($key);

	/**
	 * Assign variables to the view script via differing strategies.
	 *
	 * Suggested implementation is to allow setting a specific key to the
	 * specified value, OR passing an array of key => value pairs to set en
	 * masse.
	 *
	 * @see __set()
	 * @param string|array $spec The assignment strategy to use (key or array of key
	 * => value pairs)
	 * @param mixed $value (Optional) If assigning a named variable, use this
	 * as the value.
	 * @return void
	 */
	public function assign($spec, $value = null);

	/**
	 * Clear all assigned variables
	 *
	 * Clears all variables assigned to Zend_View either via {@link assign()} or
	 * property overloading ({@link __get()}/{@link __set()}).
	 *
	 * @return void
	 */
	public function clearVars();

	/**
	 * Processes a view script and returns the output.
	 *
	 * @return string The script output.
	 */
	public function render();
}