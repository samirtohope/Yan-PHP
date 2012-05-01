<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Acl.php 19 2012-04-28 02:42:04Z kakalong $
 */

/**
 * Yan_Acl
 * 
 * @category Yan
 * @package Yan_Acl
 */
abstract class Yan_Acl
{
	/**
	 * check that is allowed
	 *
	 * @param Yan_Acl_User_Interface $user
	 * @param Yan_Acl_Resource_Interface $resource
	 * @return boolean
	 */
	public static function isAllowed(Yan_Acl_User_Interface $user, Yan_Acl_Resource_Interface $resource)
	{
		foreach ($user->getRoles() as $role) {
			if ($role->hasResource($resource)) {
				return true;
			}
		}
		return false;
	}
}