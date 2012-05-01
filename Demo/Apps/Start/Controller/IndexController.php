<?php
class IndexController extends Yan_Controller
{
	/**
	 * 一般输出
	 */
	public function index()
	{
		echo "Thanks!";
	}

	/**
	 * return 输出
	 */
	public function echo2()
	{
		return "Echo from reutrn";
	}

	/**
	 * 输出json
	 */
	public function json()
	{
		return array('Zend Framework', 'Doctrine', 'Twig');
	}

	/**
	 * 自动探测模版(ControllerName/actionName.suffix)输出
	 */
	public function tpltest()
	{}

	/**
	 * 自动模版输出，赋值变量
	 */
	public function tpltest2()
	{
		$this->assign('title', 'title set from controller');
	}

	/**
	 * 预设模版引擎, 使用Twig
	 */
	public function tpltest3()
	{
		$this->_setView(array(
			'class'=>'Yan_View_Twig',
			'compiledBase'=>ROOT_PATH.'/Data/Cache/Template',
			'templateBase'=>APP_PATH.'/View'
		));
		// 赋值方法一
		$this->assign('title', 'Twig Template Engine Test');
		// 赋值方法二
		$this->_view->hello = 'Twig Template Engine Test';
	}

	/**
	 * 预设输出类型 Yan_Output_View 模版输出
	 */
	public function output1()
	{
		$this->_setOutput('view', array(
			'view' => array(
				'class'=>'Yan_View_Simple',
				'templateBase' => APP_PATH.'/View'
			),
			'contentType'=>'text/html',
			'charset'=>'utf-8',
			'serverCache'=>array(
				'class'=>'Yan_Cache_Memcached'
			)
		));
		$this->display($this->_request->getActionName(), $this->_request->getControllerName());
	}

	/**
	 * 输出类型 Yan_Output_Captcha 验证码
	 */
	public function output2()
	{
		// 创建 Captcha 引擎
		$captcha = new Yan_Captcha_Image(array(
			'name' => 'valicode',
			'format' => 'png',
			'fonts'=>array(
				array('spacing' => -2, 'minSize' => 26, 'maxSize' => 30,
					'file' => APP_PATH.'/Assets/Fonts/Ding-DongDaddyO.ttf')
			),
			'height'=>50
		));
		// 创建 Output 适配器
		$output = new Yan_Output_Captcha($this->_response);
		$output->setCaptcha($captcha);

		$this->_output = $output;
		// 或者
		return $output;
	}

	/**
	 * 输出类型 Yan_Output_Attachment 附件下载
	 */
	public function output3()
	{
		$output = new Yan_Output_Attachment($this->_response, array(
			'fileName'=>'文件.txt',
			'fileNameCharset'=>'utf-8',
			'mimeType'=>'text/plain'
		));
		$output->setFile('/home/my/demo.txt');
		return $output;
	}

	/**
	 * 最后为了提高效率快速返回以退出，不建议使用exit/die
	 */
	public function exitTest()
	{
		if (2 != 1) {
			return self::NONE;
		}
	}


	/**
	 * 数据库 record
	 */
	public function record()
	{
		/**
		 * table 入口
		 *
		 * @var Yan_Table
		 */
		$table = Yan_Table::getInstance('tablename');

		/**
		 * 创建记录
		 *
		 * @var Yan_Table_Record
		 */
		$record = $table->create($this->_request->getPost());

		/**
		 * 保存记录
		 *
		 * @var array(primarykey, primarykey) | primarykey
		 */
		$primaryKey = $record->save();

		// 查询记录 (快速查询)
		$record = $table->fetchRecord($primaryKey);

		// 修改字段值
		$record->username = '名字';
		$record['email'] = 'me@xx.com';

		// 保存更改
		$record->save();

		/**
		 * 删除记录
		 *
		 * @var int affected rows
		 */
		$count = $record->delete();


		/****** 复杂查询 *****/

		/**
		 * 创建查询
		 *
		 * @var Yan_Table_Select
		 */
		$select = $table->select();
		// or
		$select = new Yan_Table_Select($table);
		$select->columns('*')->limit(30, 0)->where('useid', 1)
			->where(array('email'=>'me@xx.com', "username LIKE '%?%'"=>'名字'));

		/**
		 * @var Yan_Table_Rowset
		 */
		$rowset = $table->fetchRowset($select);

		// 遍历记录
		foreach ($rowset as $record/* Yan_Table_Record */) {
			// 获得 raw array
			$record->toArray();
		}
		// 获得raw array
		$rawarray = $rowset->toArray();

		/** 丰富record **/
		// 添加验证
		$record->addValidator('username', 'NotEmpty', '名字不能为空', Yan_Table_Record::ON_BOTH);
		$record->addValidator('email', '/^\w+@\w+.com$/i', '邮件格式不对', Yan_Table_Record::ON_BOTH);

		// 添加默认数据, 保存时自动填充
		$record->setDefaultValue('created', time(), Yan_Table_Record::ON_INSERT);
		$record->setDefaultValue('updated', time(), Yan_Table_Record::ON_UPDATE);
	}
}
