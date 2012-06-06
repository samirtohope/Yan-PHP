<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Table_Record
 *
 * @category   Yan
 * @package    Yan_Table
 */
class Yan_Table_Record implements ArrayAccess, IteratorAggregate
{
	const ATTR_COLUMN = 0;
	const ATTR_MESSAGE = 1;
	const ATTR_TYPE = 2;

	const ON_UPDATE = 1;
	const ON_INSERT = 2;
	const ON_EMPTY = 4;
	const ON_BOTH = 7;

	/**
	 * @var Yan_Table
	 */
	protected $_table = null;

	/**
	 * The data for each column in the row (column_name => value).
	 * The keys must match the physical names of columns in the
	 * table for which this row is defined.
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Clean data fetch from db
	 *
	 * @var array
	 */
	protected $_cleanData = array();

	/**
	 * Table columns defined in db
	 *
	 * @var array
	 */
	protected $_columns = null;

	/**
	 * Primary row key(s).
	 *
	 * @var array
	 */
	protected $_primary = null;

	/**
	 * default source to fill
	 *
	 * @var array
	 */
	protected $_defaultSource = array();

	/**
	 * Validator instance cache
	 *
	 * @var array
	 */
	protected $_validatorCache = array();

	/**
	 * validator rules
	 *
	 * @var array
	 */
	protected $_validators = array();

	/**
	 * validate errors after valid
	 *
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * for internal use
	 *
	 * @param Yan_Table $table
	 * @param array     $data
	 */
	final public function __construct(Yan_Table $table, array $data = array())
	{
		$this->_table = $table;
		$info = $table->info();
		$this->_columns = array_flip($info[Yan_Table::COLUMNS]);
		$this->_primary = $info[Yan_Table::PRIMARY];
		if (!empty($data)) {
			$this->_data = array_intersect_key($data, $this->_columns);
			$this->_cleanData = $this->_data;
		}
		$this->_init();
	}

	/**
	 * get the table reference
	 *
	 * @return Yan_Table
	 */
	public function getTable()
	{
		return $this->_table;
	}

	/**
	 * initilize of table
	 */
	protected function _init()
	{
	}

	/**
	 * access values contained in record
	 *
	 * @param string $column
	 *
	 * @return mixed
	 */
	public function __get($column)
	{
		$accessor = '_get' . $this->_toCamelFormat($column);
		if (method_exists($this, $accessor)) {
			return $this->$accessor();
		}
		if (array_key_exists($column, $this->_columns)) {
			return $this->_data[$column];
		}
		return null;
	}

	/**
	 * set value contained in record
	 *
	 * @param string $column
	 * @param mixed  $value
	 */
	public function __set($column, $value)
	{
		$mutator = '_set' . $this->_toCamelFormat($column);
		if (method_exists($this, $mutator)) {
			$this->$mutator($value);
		} elseif (array_key_exists($column, $this->_columns)) {
			$this->_data[$column] = $value;
		}
	}

	/**
	 * check the column if set
	 *
	 * @param string $column
	 *
	 * @return bool
	 */
	public function __isset($column)
	{
		return array_key_exists($column, $this->_data);
	}

	/**
	 * unset $column in record
	 *
	 * @param string $column
	 */
	public function __unset($column)
	{
		unset($this->_data[$column]);
	}

	/**
	 * Required by interface ArrayAccess
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	/**
	 * Required by interface ArrayAccess
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * Required by interface ArrayAccess
	 *
	 * @param string $offset
	 * @param mixed  $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}

	/**
	 * Required by interface ArrayAccess
	 *
	 * @param string $offset
	 */
	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}

	/**
	 * Set from external data
	 *
	 * @param array $data
	 *
	 * @return Yan_Table_Record
	 */
	public function fromArray(array $data)
	{
		foreach ($data as $column => $value) {
			$this->__set($column, $value);
		}
		return $this;
	}

	/**
	 * Save data to database
	 *
	 * @return array|mixed of primary key values
	 */
	public function save()
	{
		if (empty($this->_cleanData)) {
			return $this->_insert();
		} else {
			return $this->_update();
		}
	}

	/**
	 * insert a row to database
	 *
	 * @return array|mixed
	 * @throws Yan_Table_Record_Exception
	 */
	protected function _insert()
	{
		$this->_fire('pre', 'insert');

		$this->_autoFill(self::ON_INSERT);

		if (!$this->_validate(self::ON_INSERT)) {
			require_once 'Yan/Table/Record/Exception.php';
			throw new Yan_Table_Record_Exception('Validation failure');
		}

		$data = $this->getModified();

		$pkData = $this->_table->insert($data);

		$newPkData = $pkData;
		if (!is_array($newPkData)) {
			$primary = (array)$this->_primary;
			$newPkData = array(current($primary) => $newPkData);
		}

		$this->_data = array_merge($this->_data, $newPkData);

		$this->_fire('post', 'insert');

		$this->refresh();

		return $pkData;
	}

	/**
	 * update a row to database
	 *
	 * @return array|mixed of primarykey value
	 * @throws Yan_Table_Record_Exception
	 */
	protected function _update()
	{
		$this->_fire('pre', 'update');

		$where = $this->_table->_pkWhere($this->_getPkData(false));

		$this->_autoFill(self::ON_UPDATE);

		if (!$this->_validate(self::ON_UPDATE)) {
			require_once 'Yan/Table/Record/Exception.php';
			throw new Yan_Table_Record_Exception('Validation failure');
		}

		$data = $this->getModified();

		if (!empty($data)) {
			$this->_table->update($data, $where);
		}

		$this->_fire('post', 'update');

		$this->refresh();

		$pkData = $this->_getPkData(true);
		if (count($pkData) == 1) {
			return current($pkData);
		}
		return $pkData;
	}

	/**
	 *  primarykey value use to genarate where sequence
	 *
	 * @param bool $useDirty
	 *
	 * @return array
	 * @throws Yan_Table_Record_Exception
	 */
	protected function _getPkData($useDirty = true)
	{
		$primary = array_flip($this->_primary);
		$pkData = array_intersect_key($useDirty ? $this->_data : $this->_cleanData, $primary);
		if (count($primary) != count($pkData)) {
			require_once 'Yan/Table/Record/Exception.php';
			throw new Yan_Table_Record_Exception("primary data is not set enough");
		}
		return $pkData;
	}

	/**
	 * Delete data from db
	 *
	 * @return int affect rows
	 */
	public function delete()
	{
		$this->_fire('pre', 'delete');

		$where = $this->_table->_pkWhere($this->_getPkData());

		$ret = $this->_table->delete($where);

		$this->_fire('post', 'delete');

		/**
		 * Reset all fields to null to indicate that the row is not there
		 */
		$this->_data = array_combine(
			array_keys($this->_data),
			array_fill(0, count($this->_data), null)
		);

		return $ret;
	}

	/**
	 * Fetch the modified data
	 *
	 * @return array
	 */
	public function getModified()
	{
		if (empty($this->_cleanData)) {
			return $this->_data;
		}
		$data = $this->_data;
		foreach ($this->_cleanData as $key => $val) {
			if ($val == $data[$key]) {
				unset($data[$key]);
			}
		}
		return $data;
	}

	/**
	 * Check the column if modified
	 *
	 * @param string $column
	 *
	 * @return bool
	 */
	public function isModified($column)
	{
		if (empty($this->_cleanData) || !array_key_exists($column, $this->_cleanData)) {
			return array_key_exists($column, $this->_data);
		}
		return $this->_cleanData[$column] != $this->_data[$column];
	}

	/**
	 * set a default source set
	 *
	 * @param string       $column
	 * @param array|string $data
	 * @param int          $on
	 *
	 * @throws Yan_Table_Record_Exception
	 * @return Yan_Table_Record
	 */
	public function setDefaultValue($column, $data, $on = null)
	{
		if (!array_key_exists($column, $this->_columns)) {
			require_once 'Yan/Table/Record/Exception.php';
			throw new Yan_Table_Record_Exception("Column name '$column' is not found");
		}
		if ($this->_isNull($data)) {
			require_once 'Yan/Table/Record/Exception.php';
			throw new Yan_Table_Record_Exception("Null data for column '$column' was given");
		}
		if (!($on & self::ON_BOTH)) {
			$on = self::ON_BOTH;
		}
		$this->_defaultSource[$column][$on] = $data;
		return $this;
	}

	/**
	 * format to camel style
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function _toCamelFormat($name)
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
	}

	/**
	 * check the value is empty and is not numeric
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	protected function _isNull($value)
	{
		return empty($value) && !is_numeric($value);
	}

	/**
	 * autofill empty values or unmodified values
	 *
	 * @param int $on
	 */
	protected function _autoFill($on)
	{
		// fill auto update column
		$when = $on == self::ON_UPDATE ? 'OnUpdate' : 'OnInsert';
		foreach ($this->_columns as $column => $v) {
			if ($this->isModified($column)) {
				continue;
			}
			// apply _fillColumn OR _fillColumnOnUpdate _fillColumnOnInsert
			$func = '_fill' . $this->_toCamelFormat($column);
			if (method_exists($this, $func)
				|| method_exists($this, ($func = $func . $when))
			) {
				$this->__set($column, $this->$func($on));
			} // use defaultSource to fill
			elseif (isset($this->_defaultSource[$column])) {
				$set = $this->_defaultSource[$column];
				if (isset($set[$on])) {
					$this->__set($column, $set[$on]);
				} elseif (isset($set[self::ON_BOTH])) {
					$this->__set($column, $set[self::ON_BOTH]);
				}
			}
		}

		// fill empty column
		$columns = $on == self::ON_UPDATE ? $this->_data : $this->_columns;
		foreach ($columns as $column => $v) {
			if (isset($this->_data[$column]) && !$this->_isNull($this->_data[$column])) {
				continue;
			}
			// apply _fillColumn OR _fillColumnOnEmpty
			$func = '_fill' . $this->_toCamelFormat($column);
			if (method_exists($this, $func)
				|| method_exists($this, ($func = $func . 'OnEmpty'))
			) {
				$this->__set($column, $this->$func($on));
			} // use defaultSource to fill
			elseif (isset($this->_defaultSource[$column])) {
				$set = $this->_defaultSource[$column];
				if (isset($set[self::ON_EMPTY])) {
					$this->__set($column, $set[self::ON_EMPTY]);
				} elseif (isset($set[self::ON_BOTH])) {
					$this->__set($column, $set[self::ON_BOTH]);
				}
			}
		}
	}

	/**
	 * add a error
	 *
	 * @param string $column
	 * @param string $msg
	 * @param string $type
	 */
	protected function _addError($column, $msg, $type = null)
	{
		$this->_errors[] = array(
			self::ATTR_COLUMN  => $column,
			self::ATTR_MESSAGE => $msg,
			self::ATTR_TYPE    => $type
		);
	}

	/**
	 * set column validator
	 *
	 * @param string $column
	 * @param string $name
	 * @param string $message
	 * @param int    $on
	 *
	 * @throws Yan_Table_Record_Exception
	 * @return Yan_Table_Record
	 */
	public function addValidator($column, $name, $message = null, $on = null)
	{
		if (!array_key_exists($column, $this->_columns)) {
			require_once 'Yan/Table/Record/Exception.php';
			throw new Yan_Table_Record_Exception("Column name '$column' is not found");
		}
		if (!($on & self::ON_BOTH)) {
			$on = self::ON_BOTH;
		}

		$this->_validators[$column][$on][] = array(
			'name'    => $name,
			'message' => $message
		);
		return $this;
	}

	/**
	 * retrieve validator instance
	 *
	 * @param string $name
	 *
	 * @throws Yan_Table_Record_Exception
	 * @return Yan_Table_Validator_Interface
	 */
	protected function _retrieveValidator($name)
	{
		if ($name instanceof Yan_Table_Validator_Interface) {
			return $name;
		}

		if (!is_string($name)) {
			throw new Yan_Table_Record_Exception('Invalid validator');
		}

		if (isset($this->_validatorCache[$name])) {
			return $this->_validatorCache[$name];
		}
		if (preg_match('/^\w+$/', $name)) {
			$name = $this->_toCamelFormat($name);
			try {
				$class = $name . 'Validator';
				Yan::loadClass($class);
				$rf = new ReflectionClass($class);
				if (!$rf->isSubclassOf('Yan_Table_Validator_Interface')) {
					throw new Exception('catch');
				}
			} catch (Exception $e) {
				$class = 'Yan_Table_Validator_' . $name;
				Yan::loadClass($class);
			}
			$inst = new $class();
		} else {
			$inst = new Yan_Table_Validator_Regex($name);
		}
		return $this->_validatorCache[$name] = $inst;
	}

	/**
	 * get last validation errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * validate fields
	 *
	 * @param int  $on
	 *
	 * @param bool $breakOnFailure
	 *
	 * @return bool
	 */
	protected function _validate($on, $breakOnFailure = false)
	{
		$this->_errors = array();
		$data = $this->getModified();

		$columns = $on == self::ON_UPDATE ? $data : $this->_columns;
		$when = $on == self::ON_UPDATE ? 'OnUpdate' : 'OnInsert';
		foreach ($columns as $column => $v) {
			$value = isset($data[$column]) ? $data[$column] : null;
			// do not validate Expr
			if ($value instanceof Yan_Db_Expr) {
				continue;
			} else {
				if (is_object($value)) {
					$value = (string)$value;
				}
			}
			// apply validators
			foreach (array($on, self::ON_BOTH) as $o) {
				if (empty($this->_validators[$column][$o])) {
					continue;
				}
				foreach ($this->_validators[$column][$o] as $rule) {
					$validator = $this->_retrieveValidator($rule['name']);
					if ($validator->isValid($value)) {
						continue;
					}

					$this->_addError($column, $rule['message'], get_class($validator));

					if ($breakOnFailure) {
						return false;
					}
				}
			}
			// apply _validateColumn
			$func = '_validate' . $this->_toCamelFormat($column);
			if (method_exists($this, $func)) {
				if (null != ($msg = $this->$func($value, $on))) {
					$this->_addError($column, $msg, get_class($this) . '::' . $func);

					if ($breakOnFailure) {
						return false;
					}
				}
			}
			// apply _validateColumnOnWhen
			if (method_exists($this, ($func = $func . $when))) {
				if (null != ($msg = $this->$func($value))) {
					$this->_addError($column, $msg, get_class($this) . '::' . $func);

					if ($breakOnFailure) {
						return false;
					}
				}
			}
		}

		return empty($this->_errors);
	}

	/**
	 * if data valid
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return $this->_validate(is_null($this->_cleanData) ? self::ON_INSERT : self::ON_UPDATE, true);
	}

	/**
	 * Refresh data from db
	 *
	 * @throws Yan_Table_Record_Exception
	 * @return Yan_Table_Record
	 */
	public function refresh()
	{
		$where = $this->_table->_pkWhere($this->_getPkData());
		$this->_data = $this->_table->get($where);
		if (empty($this->_data)) {
			require_once 'Yan/Table/Record/Exception.php';
			throw new Yan_Table_Record_Exception('Cannot refresh record as it is missing');
		}
		$this->_cleanData = $this->_data;
		return $this;
	}

	/**
	 * Extract data to array
	 *
	 * @param boolean $useGet
	 *
	 * @return array
	 */
	public function toArray($useGet = false)
	{
		$data = $this->_data;
		if ($useGet) {
			foreach ($data as $c => &$d) {
				$d = $this->__get($c);
			}
		}
		return $data;
	}

	/**
	 * Required by interface IteratorAggregate, use for foreach
	 *
	 * @return ArrayIterator|Traversable
	 */
	public function getIterator()
	{
		return new ArrayIterator((array)$this->_data);
	}

	/**
	 * fire a event
	 *
	 * @param string $event
	 * @param string $on
	 */
	protected function _fire($event, $on)
	{
		$call = '_' . $event . ucfirst($on);
		if (method_exists($this, $call)) {
			$this->$call();
		}
	}
}