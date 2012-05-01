<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Interface.php 18 2012-04-26 10:49:57Z kakalong $
 */

/**
 * Yan_Acl_Resource_Interface
 *
 * @category  Yan
 * @package   Yan_Acl
 * @subpackage Resource
 */
interface Yan_Acl_Resource_Interface
{
	public function isAllowRole($roleid);
}