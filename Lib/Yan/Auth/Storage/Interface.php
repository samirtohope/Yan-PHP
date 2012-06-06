<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Auth_Storage_Interface
 *
 * @category   Yan
 * @package    Yan_Auth
 * @subpackage Storage
 */
Interface Yan_Auth_Storage_Interface
{
	/**
	 * Returns true if and only if storage is empty
	 *
	 * @return boolean
	 */
	public function isEmpty();

	/**
	 * Returns the contents of storage
	 *
	 * Behavior is undefined when storage is empty.
	 *
	 * @return mixed
	 */
	public function read();

	/**
	 * Writes $contents to storage
	 *
	 * @param  mixed $contents
	 *
	 * @return void
	 */
	public function write($contents);

	/**
	 * Clears contents from storage
	 *
	 * @return void
	 */
	public function clear();
}