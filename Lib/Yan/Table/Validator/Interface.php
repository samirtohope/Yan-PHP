<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Table_Validator_Interface
 *
 * @category Yan
 * @package  Yan_Table
 */
Interface Yan_Table_Validator_Interface
{
	/**
	 * Check if valid
	 *
	 * @return bool
	 */
	public function isValid($value);
}