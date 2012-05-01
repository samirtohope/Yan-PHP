<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Regex.php 15 2012-04-23 11:33:00Z kakalong $
 */

require_once 'Yan/Table/Validator/Interface.php';

/**
 * Yan_Table_Validator_Regex
 * 
 * @category Yan
 * @package  Yan_Table
 */
class Yan_Table_Validator_Regex implements Yan_Table_Validator_Interface
{
	public function __construct($pattern)
	{
		$this->_pattern = (string) $pattern;
		try {
			// "@" cannot fully disable errors reporting when an exception was made in error
			$status = @preg_match($this->_pattern, "Test");
		} catch (Exception $e){}

		if (false === $status) {
			require_once 'Yan/Table/Validator/Exception.php';
			throw new Yan_Table_Validator_Exception("Invalid pattern '$this->_pattern'");
		}
	}

	public function isValid($value)
	{
		return is_null($value) || preg_match($this->_pattern, $value);
	}
}