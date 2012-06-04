<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Table/Validator/Interface.php';

/**
 * Yan_Table_Validator_NotEmpty
 *
 * @category Yan
 * @package  Yan_Table
 */
class Yan_Table_Validator_NotEmpty implements Yan_Table_Validator_Interface
{
	public function isValid($value)
	{
		return !is_null($value) && $value !== '';
	}
}