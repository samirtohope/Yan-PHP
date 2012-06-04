<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Table_Paginator
 *
 * @category  Yan
 * @package   Yan_Table
 */
class Yan_Table_Paginator implements Countable
{
	protected $_pageSize = 10;
	protected $_pageRange = 10;
	protected $_currentPage = 1;

	protected $_pageCount;
	protected $_recordCount;

	public function __construct(array $options = null)
	{
		if ($options != null) {
			$setups = array('pageSize', 'pageRange', 'currentPage');
			foreach ($setups as $set) {
				if (array_key_exists($set, $options)) {
					$this->{'set' . ucfirst($set)}($options[$set]);
				}
			}
		}
	}

	public function setPageSize($val)
	{
		if (($val = abs((int)$val)) > 0) {
			$this->_pageSize = $val;
		}
		return $this;
	}

	public function setPageRange($val)
	{
		if (($val = abs((int)$val)) > 0) {
			$this->_pageRange = $val;
		}
		return $this;
	}

	public function setCurrentPage($val)
	{
		if (($val = abs((int)$val)) > 0) {
			$this->_currentPage = $val;
		}
		return $this;
	}

	public function getCurrentPage()
	{
		return $this->_currentPage;
	}

	public function getPageSize()
	{
		return $this->_pageSize;
	}

	public function getRecordCount()
	{
		return $this->_recordCount;
	}

	public function init($recordCount)
	{
		$this->_recordCount = (int)$recordCount;
		return $this;
	}

	/**
	 * Returns the number of pages. Defined by Countable
	 *
	 * @return integer
	 */
	public function count()
	{
		if (!$this->_pageCount) {
			$this->_pageCount = ceil($this->getRecordCount() / $this->getPageSize());
		}

		return $this->_pageCount;
	}

	public function getPages()
	{
		$pageCount = $this->count();
		$currentPage = $this->getCurrentPage();

		$pages = new stdClass();
		$pages->recordCount = $this->getRecordCount();
		$pages->pageCount = $pageCount;
		$pages->pageSize = $this->getPageSize();
		$pages->first = 1;
		$pages->current = $currentPage;
		$pages->last = $pageCount;

		// Previous and next
		if ($currentPage > 1) {
			$pages->prev = $currentPage - 1;
		}
		if ($currentPage + 1 <= $pageCount) {
			$pages->next = $currentPage + 1;
		}
		return $pages;
	}

	public function render()
	{
	}
}