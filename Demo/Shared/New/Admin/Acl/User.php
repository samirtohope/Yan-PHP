<?php
/**
 * New project
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: User.php 26 2011-11-02 17:52:11Z kakalong $
 */

class New_Admin_Acl_User extends Yan_Acl_User_DbTable
{
	protected $_tableName = 'user_has_role';
    
	protected $_roleClass = 'New_Admin_Acl_Role';
	
	protected $_userColumn = 'userid';
	
	protected $_roleColumn = 'roleid';
}