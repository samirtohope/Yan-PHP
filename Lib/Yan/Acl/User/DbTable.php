<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Acl/User/Interface.php';

/**
 * Yan_Acl_User_DbTable
 *
 * @category  Yan
 * @package   Yan_Acl
 * @subpackage User
 */
class Yan_Acl_User_DbTable implements Yan_Acl_User_Interface
{
	/**
	 * db adapter
	 *
	 * @var Yan_Db_Adapter
	 */
	protected $_db;

	/**
	 * $_tableName - the table name to check
	 *
	 * @var string
	 */
	protected $_tableName = null;

	protected $_roleClass = 'Yan_Acl_Role_Abstract';

	protected $_userColumn = null;

	protected $_roleColumn = null;

	protected $_user = null;

	public function __construct(Yan_Db_Adapter $adapter, $user)
	{
		$this->_db = $adapter;
		$this->_user = $user;
	}

	public function getAdapter()
	{
		return $this->_db;
	}

	public function setTableName($tableName)
	{
		$this->_tableName = $tableName;
		return $this;
	}

	public function setRoleClass($class)
	{
		$this->_roleClass = $class;
		return $this;
	}

	public function getRoleClass()
	{
		return $this->_roleClass;
	}

	public function setRoleColumn($column)
	{
		$this->_roleColumn = $column;
		return $this;
	}

	public function setUserColumn($column)
	{
		$this->_userColumn = $column;
		return $this;
	}

	public function getRoles()
	{
		$exception = null;

		if ($this->_tableName == '') {
			$exception = 'A table must be supplied.';
		} elseif ($this->_userColumn == '') {
			$exception = 'An user column must be supplied.';
		} elseif ($this->_roleColumn == '') {
			$exception = 'A role column must be supplied.';
		} elseif ($this->_user == '') {
			$exception = 'A value for the user was not provided.';
		}

		if (null !== $exception) {
			require_once 'Yan/Acl/User/Exception.php';
			throw new Yan_Acl_User_Exception($exception);
		}

		$select = new Yan_Db_Select($this->_db);
		$select->from($this->_tableName, $this->_roleColumn)->where(
			$this->_db->quoteIdentifier($this->_userColumn, true)
				. ' = ' . $this->_db->quote($this->_user));
		try {
			$data = $select->query()->fetchAll(Yan_Db::FETCH_NUM);
		} catch (Exception $e) {
			require_once 'Yan/Acl/User/Exception.php';
			throw new Yan_Acl_User_Exception(
				'Yan_Acl_User_DbTable failed to produce a valid sql statement.'
			);
		}

		$temp = array();
		foreach ($data as $r) {
			$temp[] = $r[0];
		}

		return new Yan_Acl_Roles($this, $temp);
	}
}