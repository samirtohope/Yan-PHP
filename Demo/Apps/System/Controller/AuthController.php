<?php
class AuthController extends Yan_Controller
{
	protected $_messages = array(
		Yan_Auth_Result::FAILURE_IDENTITY_NOT_FOUND => '用户密码不匹配。',
		Yan_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS => '用户冲突。',
		Yan_Auth_Result::FAILURE_CREDENTIAL_INVALID => '用户密码不匹配。',
		Yan_Auth_Result::FAILURE_IDENTITY_IS_NULL => '帐号或密码为空。',
		Yan_Auth_Result::FAILURE_CREDENTIAL_IS_NULL => '帐号或密码为空。',
		Yan_Auth_Result::FAILURE_UNCATEGORIZED => '未识别的登录错误。',
		Yan_Auth_Result::FAILURE => '登录失败。',
		Yan_Auth_Result::SUCCESS => '登录成功。'
	);

	public function login()
	{
		if ($this->_request->isPost()) {
			$storage = new Yan_Auth_Storage_Session('New_Admin', 'login');

			if ($storage->read()) {
				// 已经登录
				// die('已经登录');
				// 跳转
			}

			if (!($db = Yan_Db::getDefaultAdapter())) {
				throw new New_Admin_Exception('A db connection was not provided.');
			}


			$auth = Yan_Auth::getInstance();

			$auth->setStorage($storage);

			$result = $auth->authenticate(new New_Admin_Auth_DbTable($db, $this->_request));

			$message = $this->_messages[$result->getCode()];

			echo $message;
		} else {
			$this->assign('basePath', $this->_request->getBasePath());
			$this->assign('baseUrl', $this->_request->getBaseUrl());
			$this->assign('photo', $this->imageDataURI(ROOT_PATH . '/Public/Assets/photo.png'));
		}
	}

	private function imageDataURI($photo)
	{
		$info = getimagesize($photo);
		$data = base64_encode(file_get_contents($photo));
		$mime = image_type_to_mime_type($info[2]);
		return 'data:' . $mime . ';base64,' . $data;
	}

	public function logout()
	{

	}

	public function forgot()
	{

	}
}