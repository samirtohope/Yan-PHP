<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Table_Rowset
 *
 * @category   Yan
 * @package    Yan_Table
 */
class Yan_Table_Rowset implements SeekableIterator, Countable, ArrayAccess
{
	/**
	 * The original data for each row.
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * Yan_Table object.
	 *
	 * @var Yan_Table
	 */
	protected $_table;

	/**
	 * Yan_Table_Record class name.
	 *
	 * @var string
	 */
	protected $_rowClass;

	/**
	 * Iterator pointer.
	 *
	 * @var int
	 */
	protected $_pointer = 0;

	/**
	 * How many data rows there are.
	 *
	 * @var integer
	 */
	protected $_count;

	/**
	 * Collection of instantiated Yan_Table_Record objects.
	 *
	 * @var array
	 */
	protected $_rows = array();

	public function __construct(Yan_Table $table, array $data)
	{
		$this->_table = $table;
		$this->_rowClass = $table->info(Yan_Table::RECORD_CLASS);
		$this->_data = $data;
		$this->_count = count($this->_data);
	}

	/**
	 * Required by interface Iterator.
	 *
	 * @return Yan_Table_Rowset
	 */
	public function rewind()
	{
		$this->_pointer = 0;
		return $this;
	}

	/**
	 * Required by interface Iterator.
	 *
	 * @return Yan_Table_Record
	 */
	public function current()
	{
		if ($this->valid() === false) {
			return null;
		}
		return $this->_initRow($this->_pointer);
	}

	/**
	 * Required by interface Iterator.
	 *
	 * @return int
	 */
	public function key()
	{
		return $this->_pointer;
	}

	/**
	 * Required by interface Iterator.
	 */
	public function next()
	{
		++$this->_pointer;
	}

	/**
	 * Required by interface Iterator.
	 *
	 * @return bool
	 */
	public function valid()
	{
		return $this->_pointer < $this->_count;
	}

	/**
	 * Required by interface Countable.
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->_count;
	}

	/**
	 * Required by interface SeekableIterator.
	 *
	 * @param int $position
	 *
	 * @throws Yan_Table_Rowset_Exception
	 * @return Yan_Table_Rowset
	 */
	public function seek($position)
	{
		$position = (int)$position;
		if ($position < 0 || $position >= $this->_count) {
			require_once 'Yan/Table/Rowset/Exception.php';
			throw new Yan_Table_Rowset_Exception("Illegal index $position");
		}
		$this->_pointer = $position;
		return $this;
	}

	/**
	 * Required by the interface ArrayAccess
	 *
	 * @param $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->_data[(int)$offset]);
	}

	/**
	 * Required by the interface ArrayAccess
	 *
	 * @param int $offset
	 *
	 * @return Yan_Table_Record
	 */
	public function offsetGet($offset)
	{
		$this->seek($offset);

		return $this->current();
	}

	/**
	 * Required by the interface ArrayAccess
	 */
	public function offsetSet($offset, $value)
	{
	}

	/**
	 * Required by the interface ArrayAccess
	 */
	public function offsetUnset($offset)
	{
	}

	/**
	 * export plain array
	 *
	 * @param bool $useGet
	 *
	 * @return array
	 */
	public function toArray($useGet = false)
	{
		$data = $this->_data;
		if ($useGet) {
			foreach ($data as $i => &$d) {
				$d = $this->_initRow($i)->toArray(true);
			}
		} else {
			foreach ($this->_rows as $i => $row) {
				$data[$i] = $row->toArray();
			}
		}
		return $data;
	}

	/**
	 * retrieve Yan_Table_Record as $pos
	 *
	 * @param int $pos
	 *
	 * @return Yan_Table_Record
	 * @throws Yan_Table_Rowset_Exception
	 */
	protected function _initRow($pos)
	{
		if (!isset($this->_data[$pos])) {
			require_once 'Yan/Table/Rowset/Exception.php';
			throw new Yan_Table_Rowset_Exception("Data for provided position does not exist");
		}

		if (empty($this->_rows[$pos])) {
			$this->_rows[$pos] = new $this->_rowClass($this->_table, $this->_data[$pos]);
		}

		return $this->_rows[$pos];
	}
}