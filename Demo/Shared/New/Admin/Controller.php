<?php
/**
 * New project
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Controller.php 26 2011-11-02 17:52:11Z kakalong $
 */

class New_Admin_Controller extends Yan_Controller
{

	protected function _init()
	{
		$storage = new Yan_Auth_Storage_Session('New_Admin', 'login');
		if (!($login = $storage->read())) {
			$this->_response->setRedirect('/System/Auth/login');
			$this->_response->send();
		}
		
		if (!($db = Yan_Db::getDefaultAdapter())) {
			throw new New_Admin_Exception('A db connection was not provided.');
		}
		
		$user = new New_Admin_Acl_User($db, $login['userid']);
		Yan::set('user', $user);
		
		$resource = new New_Admin_Acl_Resource_Action($db, APP_NAME, 
			$this->_request->getControllerName(),
			$this->_request->getActionName());
		
		if (!Yan_Acl::isAllowed($user, $resource)) {
			$this->showMessage('无权限');
			return;
		}
	}
	
	protected function showMessage($msg) {
		echo $msg;
	}
}