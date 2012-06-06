<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Auth_Adapter_Interface
 *
 * @category   Yan
 * @package    Yan_Auth
 * @subpackage Adapter
 */
interface Yan_Auth_Adapter_Interface
{
	/**
	 * Performs an authentication attempt
	 *
	 * @return Yan_Auth_Result
	 */
	public function authenticate();
}