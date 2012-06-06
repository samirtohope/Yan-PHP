<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

require_once 'Yan/Table/Validator/Interface.php';

/**
 * Yan_Table_Validator_NotEmpty
 *
 * @category   Yan
 * @package    Yan_Table
 * @subpackage Validator
 */
class Yan_Table_Validator_NotEmpty implements Yan_Table_Validator_Interface
{
	/**
	 * Check if valid
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function isValid($value)
	{
		return !is_null($value) && $value !== '';
	}
}