<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Acl_User_Interface
 *
 * @category  Yan
 * @package   Yan_Acl
 * @subpackage User
 */
interface Yan_Acl_User_Interface
{
	public function setRoleClass($class);

	public function getRoleClass();

	public function getRoles();
}