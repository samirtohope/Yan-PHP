<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Abstract.php 7 2012-04-06 07:50:26Z kakalong $
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