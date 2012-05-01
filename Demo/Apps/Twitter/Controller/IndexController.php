<?php
/**
 * 文章
 */
class IndexController extends New_Admin_Controller
{
	/**
	 * @aca 首页
	 */
	public function index()
	{}

	/**
	 * @belongs index
	 */
	protected function page()
	{
		$t = T('twitter');
		$sel = $t->select();
		$paginator = new Yan_Table_Paginator(array(
			'pageSize' => 5,
			'currentPage'=>1
		));
		$sel->page($paginator);
		return $t->fetchRowset($sel)->toArray(true);
	}

	/**
	 * @aca 文档
	 */
	public function doc()
	{
		$this->assign(array(
			'title'=>'文档',
			'basePath'=>$this->_request->getBasePath()
		));
	}

	/**
	 * @aca 写
	 */
	protected function write()
	{
		$t = T('twitter');
		$row = $t->create($_POST);
		try {
			$row->save();
		} catch (Exception $e) {
			return array('state'=>false, 'message'=>$e->getMessage());
		}
		return array('state'=>true, 'data'=>$row->toArray(true));
	}

	/**
	 * @belongs write
	 */
	protected function del()
	{
		$t = T('twitter');
		$message = '无效删除';
		if (null != ($row = $t->fetchRecord($this->_request->id)))
		{
			try {
				$row->delete();
				return array('state'=>true);
			} catch (Exception $e) {
				$message = $e->getMessage();
			}
		}
		return array('state'=>false, 'message'=>$message);
	}

	/**
	 * @belongs write
	 */
	protected function edit()
	{}
}