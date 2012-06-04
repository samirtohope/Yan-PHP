<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Acl_Role_Abstract
 *
 * @category  Yan
 * @package   Yan_Acl
 * @subpackage Role
 */
abstract class Yan_Acl_Role_Abstract
{
	protected $_user;

	protected $_identity;

	public function __construct(Yan_Acl_User_Interface $user, $identity)
	{
		$this->_identity = $identity;
		$this->_user = $user;
	}

	abstract public function hasResource(Yan_Acl_Resource_Interface $resource);
}