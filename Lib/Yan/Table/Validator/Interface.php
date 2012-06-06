<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Table_Validator_Interface
 *
 * @category Yan
 * @package  Yan_Table
 * @subpackage Validator
 */
Interface Yan_Table_Validator_Interface
{
	/**
	 * Check if valid
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function isValid($value);
}