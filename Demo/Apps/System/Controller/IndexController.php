<?php
/**
 * 后台首页
 */
class IndexController extends New_Admin_Controller
{
	/**
	 * @aca 首页
	 */
	public function index()
	{
		$this->assign('basePath', $this->_request->getBasePath());
	}

	/**
	 * @belongs index
	 */
	public function menu()
	{

	}
}