<?php
/**
 * New project
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Role.php 26 2011-11-02 17:52:11Z kakalong $
 */

class New_Admin_Acl_Role extends Yan_Acl_Role_Abstract
{
	protected $_db;
	
	public function __construct(Yan_Acl_User_DbTable $user, $identity)
	{
		parent::__construct($user, $identity);
		$this->_db = $user->getAdapter();
	}
	
	public function hasResource(Yan_Acl_Resource_Interface $resource)
	{
		$roleid = $this->_identity;
		
		$select = new Yan_Db_Select($this->_db);
		$stmt = $select->from('role', 'parentid')->where('roleid=?')
			->prepare(Yan_Db::FETCH_NUM);
		while (1) {
			if ($resource->isAllowRole($roleid)) {
				return true;
			}
		
			$stmt->execute(array($roleid));
			$roleid = $stmt->fetchColumn();
			if (empty($roleid)) {
				return false;
			}
		}
		
		return false;
	}
}