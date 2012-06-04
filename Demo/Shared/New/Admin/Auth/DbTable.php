<?php
/**
 * New project
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

class New_Admin_Auth_DbTable extends Yan_Auth_Adapter_DbTable
{
	protected $_tableName = 'user';

	/**
	 * $_identityColumn - the column to use as the identity
	 *
	 * @var string
	 */
	protected $_identityColumn = 'email';

	/**
	 * $_credentialColumns - columns to be used as the credentials
	 *
	 * @var string
	 */
	protected $_credentialColumn = 'password';

	protected $_fields = 'userid, groupid, email, username, disabled';

	public function __construct(Yan_Db_Adapter $adapter, Yan_Request_Abstract $request)
	{
		parent::__construct($adapter,
			$request->get($this->_identityColumn),
			md5($request->get($this->_credentialColumn)));
	}
}