<?php
class TwitterRecord extends Yan_Table_Record
{
	protected function _init()
	{
		$this->setDefaultValue('created', time(), self::ON_INSERT)
			->setDefaultValue('createdby', 1, self::ON_INSERT);
	}
	
	protected function _getCreatedby()
	{
		return '用户'.$this->_data['createdby'];
	}
	
	protected function _getCreated()
	{
		return date('Y-m-d H:i:s', $this->_data['created']);
	}
}