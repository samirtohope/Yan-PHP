<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Roles.php 15 2012-04-23 11:33:00Z kakalong $
 */

/**
 * Yan_Acl_Roles
 *
 * @category  Yan
 * @package   Yan_Acl
 */
class Yan_Acl_Roles implements SeekableIterator, Countable, ArrayAccess
{
	/**
	 * The original data for each row.
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * Yan_Acl_User_Interface object.
	 *
	 * @var Yan_Acl_User_Interface
	 */
	protected $_user;

	/**
	 * Yan_Acl_Role_Abstract class name.
	 *
	 * @var string
	 */
	protected $_roleClass = 'Yan_Acl_Role_Abstract';

	/**
	 * Iterator pointer.
	 *
	 * @var integer
	 */
	protected $_pointer = 0;

	/**
	 * How many data rows there are.
	 *
	 * @var integer
	 */
	protected $_count;

	/**
	 * Collection of instantiated Yan_Acl_Role_Abstract objects.
	 *
	 * @var array
	 */
	protected $_roles = array();


	public function __construct(Yan_Acl_User_Interface $user, array $data)
	{
		$this->_user = $user;
		$this->_roleClass = $user->getRoleClass();
		$this->_data = $data;
		$this->_count = count($this->_data);
	}

	public function rewind()
	{
		$this->_pointer = 0;
		return $this;
	}

	/**
	 * Required by interface Iterator.
	 *
	 * @return Yan_Acl_Role_Abstract
	 */
	public function current()
	{
		if ($this->valid() === false) {
			return null;
		}
		return $this->_initRole($this->_pointer);
	}

	public function key()
	{
		return $this->_pointer;
	}

	public function next()
	{
		++$this->_pointer;
	}

	public function valid()
	{
		return $this->_pointer < $this->_count;
	}

	public function count()
	{
		return $this->_count;
	}

	public function seek($position)
	{
		$position = (int) $position;
		if ($position < 0 || $position >= $this->_count) {
			require_once 'Yan/Acl/Exception.php';
			throw new Yan_Acl_Exception("Illegal index $position");
		}
		$this->_pointer = $position;
		return $this;
	}

	public function offsetExists($offset)
	{
		return isset($this->_data[(int) $offset]);
	}

	/**
	 * Required by the ArrayAccess implementation
	 *
	 * @return Yan_Acl_Role
	 */
	public function offsetGet($offset)
	{
		$this->seek($offset);

		return $this->current();
	}

	public function offsetSet($offset, $value)
	{}

	public function offsetUnset($offset)
	{}

	protected function _initRole($pos)
	{
		if (!isset($this->_data[$pos])) {
			require_once 'Yan/Acl/Exception.php';
			throw new Yan_Acl_Exception("Data for provided position does not exist");
		}

		if (empty($this->_roles[$pos])) {
			Yan::loadClass($this->_roleClass);
			$this->_roles[$pos] = new $this->_roleClass($this->_user, $this->_data[$pos]);
		}

		return $this->_roles[$pos];
	}
}