<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Interface.php 15 2012-04-23 11:33:00Z kakalong $
 */

/**
 * Yan_Auth_Adapter_Interface
 *
 * @category  Yan
 * @package   Yan_Auth
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