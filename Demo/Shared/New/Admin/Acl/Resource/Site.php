<?php
/**
 * Blog project
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

class New_Admin_Acl_Resource_Site implements Yan_Acl_Resource_Interface
{
	protected $_ispublic = false;

	protected $_stmt = null;

	public function __construct(Yan_Db_Adapter $db, $siteid)
	{
		$this->_ispublic = false;
		$select = new Yan_Db_Select($db);
		$this->_stmt = $select->from('role_has_site', new Yan_Db_Expr('count(*)'))
			->where('siteid', $siteid)->where('roleid=?')
			->prepare(Yan_Db::FETCH_NUM);
	}

	public function isPublic()
	{
		return $this->_ispublic;
	}

	public function isAllowRole($roleid)
	{
		$this->_stmt->execute(array($roleid));
		return $this->_stmt->fetchColumn();
	}
}