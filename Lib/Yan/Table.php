<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Table.php 19 2012-04-28 02:42:04Z kakalong $
 */

/**
 * Class for SQL Table interface.
 *
 * @category   Yan
 * @package    Yan_Table
 */
class Yan_Table
{

	const ADAPTER          = 'adapter';
	const SCHEMA           = 'schema';
	const NAME             = 'name';
	const QUOTED_NAME      = 'quotedName';
	const ALIAS_NAME       = 'aliasName';
	const PRIMARY          = 'primary';
	const COLUMNS          = 'columns';
	const METADATA         = 'metadata';
	const METADATA_CACHE   = 'metadataCache';
	const RECORD_CLASS     = 'recordClass';

	const DEFAULT_RECORD_CLASS = 'Yan_Table_Record';

	/**
	 * Object Db Adapter
	 *
	 * @var Yan_Db_Adapter
	 */
	protected $_adapter;

	/**
	 * The schema name (default null means current schema)
	 *
	 * @var array
	 */
	protected $_schema = null;

	/**
	 * The table name.
	 *
	 * @var array
	 */
	protected $_name = null;

	/**
	 * The alias name for table
	 * @var string
	 */
	protected $_aliasName = null;

	/**
	 * Quoted name for table
	 * @var string
	 */
	protected $_quotedName = null;

	/**
	 * The primary key column or columns.
	 * A compound key should be declared as an array.
	 * You may declare a single-column primary key
	 * as a string.
	 *
	 * @var array
	 */
	protected $_primary = null;

	/**
	 * If your primary key is a compound key, and one of the columns uses
	 * an auto-increment or sequence-generated value, set _identity
	 * to the ordinal index in the $_primary array for that column.
	 * Note this index is the position of the column in the primary key,
	 * not the position of the column in the table.  The primary key
	 * array is 1-based.
	 *
	 * @var integer
	 */
	protected $_identity = 1;

	/**
	 * Information provided by the adapter's metaTable() method.
	 *
	 * @var array
	 */
	protected $_metadata = array();

	/**
	 * Object for cache metadata
	 *
	 * @var Yan_Cache
	 */
	protected $_metadataCache = null;

	/**
	 * Class defined for create record
	 *
	 * @var string
	 */
	protected $_recordClass = null;

	/**
	 * The table column names derived from Yan_Db_Adapter::metaTable().
	 *
	 * @var array
	 */
	protected $_columns = array();

	/**
	 * Constructor.
	 *
	 * @param  mixed $config Array of user-specified config options, or just the Db Adapter.
	 * @return void
	 */
	public function __construct($config = array())
	{
		/**
		 * Allow a string argument to be the table name.
		 */
		if (!is_array($config)) {
			$config = array(self::NAME => $config);
		}

		$configKeys = array(
			self::ADAPTER, self::SCHEMA, self::NAME,
			self::QUOTED_NAME, self::ALIAS_NAME, self::PRIMARY,
			self::METADATA_CACHE, self::RECORD_CLASS
		);
		foreach ($configKeys as $key) {
			if (isset($config[$key])) {
				$var = '_'.$key;
				$this->$var = $config[$key];
			}
		}
		$this->_setup();
		$this->init();
	}

	/**
	 * Initilized a Yan_Table
	 *
	 * @param string $table
	 * @return Yan_Table
	 */
	public static function factory($table)
	{
		if (!is_string($table)) {
			return new Yan_Table($table);
		}
		try {
			$class = $table.'Table';
			Yan::loadClass($class);
			$instance = new $class();
			if (! $instance instanceof Yan_Table) {
				throw new Exception('catch');
			}
			return $instance;
		} catch (Exception $e) {
			return new Yan_Table($table);
		}
	}

	public function init()
	{}

	public function __toString()
	{
		return get_class($this);
	}

	public function getAdapter()
	{
		return $this->_adapter;
	}

	public function getMetadata()
	{
		return $this->_metadata;
	}

	/**
	 * Returns table information.
	 *
	 * You can elect to return only a part of this information by supplying its key name,
	 * otherwise all information is returned as an array.
	 *
	 * @param  $key The specific info part to return OPTIONAL
	 * @return mixed
	 */
	public function info($key = null) {
		$info = array(
			self::SCHEMA       => $this->_schema,
			self::NAME         => $this->_name,
			self::QUOTED_NAME  => $this->_quotedName,
			self::ALIAS_NAME   => $this->_aliasName,
			self::COLUMNS      => $this->_columns,
			self::PRIMARY      => $this->_primary,
			self::RECORD_CLASS => $this->_recordClass
		);

		if ($key === null) {
			return $info;
		}

		if (!array_key_exists($key, $info)) {
			throw new Yan_Table_Exception("There is no table information for the key '$key'");
		}

		return $info[$key];
	}

	public function isIdentity($column)
	{
		return !empty($this->_metadata[$column]['IDENTITY']);
	}

	public function insert(array $data)
	{

		$primary = $this->_primary;
		$pkI = $primary[(int)$this->_identity];

		$data = array_intersect_key($data, array_flip($this->_columns));

		// filter null primary value
		if (array_key_exists($pkI, $data) &&
			($data[$pkI] === null || $data[$pkI] === '' || is_bool($data[$pkI])
			 || (is_array($data[$pkI]) && empty($data[$pkI]))))
		{
			unset($data[$pkI]);
		}

		$this->_adapter->insert($this->_quotedName, $data, true);

		if (!isset($data[$pkI])) {
			$data[$pkI] = $this->_adapter->lastInsertId($this->_quotedName, $pkI);
		}

		$pkData = array_intersect_key($data, array_flip($primary));
		if (count($primary) == 1) {
			reset($pkData);
			return current($pkData);
		}
		return $pkData;
	}

	public function update(array $data, $where)
	{
		$data = array_intersect_key($data, array_flip($this->_columns));
		return $this->_adapter->update($this->_quotedName, $data, $where, true);
	}

	public function delete($where)
	{
		return $this->_adapter->delete($this->_quotedName, $where, true);
	}

	/**
	 * create primary key where sequence, internal use only
	 *
	 * @param array $pkData
	 * @return string
	 */
	public function _pkWhere(array $pkData)
	{
		$where = array();
		foreach ($pkData as $column => $value) {
			$type = $this->_metadata[$column]['DATA_TYPE'];
			$columnName = $this->_adapter->quoteIdentifier($column, true);
			$where[] = "{$columnName} = ".$this->_adapter->quote($value,$type);
		}
		return implode(' AND ', $where);
	}

	/**
	 * @return Yan_Table_Select
	 */
	public function select($withFrom = true)
	{
		$select = new Yan_Table_Select($this);
		if ($withFrom) {
			$select->from($this);
		}
		return $select;
	}

	/**
	 * Fetches rows by primary key.  The argument specifies one or more primary
	 * key value(s).  To find multiple rows by primary key, the argument must
	 * be an array.
	 *
	 * This method accepts a variable number of arguments.  If the table has a
	 * multi-column primary key, the number of arguments must be the same as
	 * the number of columns in the primary key.  To find multiple rows in a
	 * table with a multi-column primary key, each argument must be an array
	 * with the same number of elements.
	 *
	 * The find() method always returns a Rowset object, even if only one row
	 * was found.
	 *
	 * @param  mixed $key The value(s) of the primary keys.
	 * @return Yan_Table_Rowset
	 */
	public function find()
	{
		$args = func_get_args();
		$primary = array_values($this->_primary);
		$argc = count($args);
		$keyc = count($primary);
		if ($argc < $keyc) {
			throw new Yan_Table_Exception("Too few columns for the primary key");
		}

		$termc = 0;
		$pkValues = array();
		for ($pos=0; $pos<$keyc; $pos++) {
			$arg = $args[$pos];
			if (!is_array($arg)) {
				$arg = array($arg);
			}
			if (!$termc) {
				$termc = count($arg);
			} else if (count($arg) != $termc) {
				throw new Yan_Table_Exception('Missing value(s) for the primary key');
			}
			for ($i=0; $i<$termc; $i++) {
				$pkValues[$i][$primary[$pos]] = $arg[$i];
			}
		}
		$where = array();
		foreach ($pkValues as $pkData) {
			$where[] = '(' . $this->_pkWhere($pkData) . ')';
		}
		$where = '(' . implode(' OR ', $where) . ')';

		return $this->fetchRowset($where);
	}

	/**
	 * Fetch a Yan_Table_Rowset object
	 *
	 * @param Yan_Table_Select|string $where
	 * @param string $order
	 * @param int $count
	 * @param int $offset
	 * @return Yan_Table_Rowset
	 */
	public function fetchRowset($where = null, $order = null, $count = null, $offset = null)
	{
		if ($where instanceof Yan_Table_Select) {
			$data = $where->fetchAll(null, $order, $count, $offset);
		} else {
			$data = $this->select()->fetchAll($this->_where($where), $order = null, $count = null, $offset = null);
		}
		return new Yan_Table_Rowset($this, $data);
	}

	/**
	 * Fetch a Yan_Table_Record object
	 *
	 * @param Yan_Table_Select|string $where
	 * @param string $order
	 * @param int $offset
	 * @return Yan_Table_Record
	 */
	public function fetchRecord($where = null, $order = null, $offset = null)
	{
		$data = $this->get($where, $order, $offset);
		if (empty($data)) {
			return null;
		}
		return new $this->_recordClass($this, $data);
	}

	/**
	 * Get a row in raw array
	 *
	 * @param Yan_Table_Select|string $where
	 * @param string $order
	 * @param int $offset
	 * @return array
	 */
	public function get($where = null, $order = null, $offset = null)
	{
		if ($where instanceof Yan_Table_Select) {
			return $where->fetchOne(null, $order, $offset);
		} else {
			return $this->select()->fetchOne($this->_where($where), $order, $offset);
		}
	}

	/**
	 * Fetch a page
	 *
	 * @param string $where
	 * @param string $order
	 * @param int $page
	 * @param int $size
	 * @return array with pagination:Yan_Table_Paginator & rowset:Yan_Table_Rowset
	 */
	public function page($where, $order = null, $page = 1, $size = 20)
	{
		$pagination = new Yan_Table_Paginator(array(
			'pageSize'=>$size,
			'currentPage'=>$page
		));
		$data = $this->select()->page($pagination)->fetchAll($this->_where($where), $order);
		return array(
			'pagination' => $pagination,
			'rowset' => new Yan_Table_Rowset($this, $data)
		);
	}

	public function _where($where)
	{
		if (is_numeric($where) && count($this->_primary) == 1) {
			reset($this->_primary);
			$pk = current($this->_primary);
			return $this->_pkWhere(array(
				$pk => $where
			));
		}
		return $where;
	}

	/**
	 * Create a blank record
	 *
	 * @param array $data
	 * @return Yan_Table_Record
	 */
	public function create(array $data = array())
	{
		$record = new $this->_recordClass($this);
		$record->fromArray($data);
		return $record;
	}

	protected function _setup()
	{
		$this->_setupAdapter();
		$this->_setupName();
		$this->_setupRecordClass();
		$this->_setupMetadata();
		$this->_setupColumns();
		$this->_setupPrimaryKey();
	}

	protected function _setupAdapter()
	{
		if (!$this->_adapter) {
			$this->_adapter = Yan_Db::getDefaultAdapter();
		}
		if (!$this->_adapter instanceof Yan_Db_Adapter) {
			throw new Yan_Table_Exception('Need a of type Yan_Db_Adapter object');
		}
	}

	protected function _setupName()
	{
		$this->_name = (string) $this->_name;
		if (preg_match('/^(.+)\s+as\s+(.+)$/i', $this->_name, $m)) {
			$this->_name = $m[1];
			$this->_aliasName = $m[2];
		}

		if (strpos($this->_name, '.')) {
			list($this->_schema, $this->_name) = explode('.', $this->_name);
		}

		if (! strlen($this->_name)) {
			throw new Yan_Table_Exception('Needs Table name');
		}
		if (! $this->_quotedName) {
			$this->_quotedName = $this->_adapter->quoteTableAs(array($this->_schema, $this->_name), null, true);
		}
		if (! $this->_aliasName) {
			$this->_aliasName = $this->_name;
		}
	}

	protected function _setupRecordClass()
	{
		$detect = 0;
		if (! $this->_recordClass) {
			$this->_recordClass = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->_name))).'Record';
			$detect = 1;
		}
		try {
			Yan::loadClass($this->_recordClass);
		} catch (Exception $e) {
			if ($detect) {
				$this->_recordClass = self::DEFAULT_RECORD_CLASS;
				return;
			}
			require_once 'Yan/Table/Exception.php';
			throw new Yan_Table_Exception("Unavailable record class '$this->_recordClass'");
		}
	}

	protected function _setupMetadata()
	{
		if (null === $this->_metadataCache) {
			$this->_metadataCache = Yan_Db::getCacheAdapter();
		}
		$cacheId = md5($this->_quotedName);
		// If $this has a metadata cache
		if ($this->_metadataCache) {
			// Define the cache identifier where the metadata are saved
			$metadata = $this->_metadataCache->read($cacheId);
			if ($metadata && is_array($metadata)) {
				$this->_metadata = $metadata;
				return true;
			}
		}
		$metadata = $this->_adapter->describeTable($this->_quotedName, null, true);
		// Assign the metadata to $this
		$this->_metadata = $metadata;
		if ($this->_metadataCache && !$this->_metadataCache->write($cacheId, $metadata))
		{
			throw new Yan_Table_Exception('Failed saving metadata to metadataCache');
		}
		return true;
	}

	protected function _setupColumns()
	{
		$this->_columns = array_keys($this->_metadata);
	}

	protected function _setupPrimaryKey()
	{
		if (!$this->_primary) {
			$this->_primary = array();
			foreach ($this->_metadata as $col) {
				if ($col['PRIMARY']) {
					$this->_primary[$col['PRIMARY_POS']] = $col['COLUMN_NAME'];
					if ($col['IDENTITY']) {
						$this->_identity = $col['PRIMARY_POS'];
					}
				}
			}
			// if no primary key was specified and none was found in the metadata
			// then throw an exception.
			if (empty($this->_primary)) {
				throw new Yan_Table_Exception(
					'A table must have a primary key, but none was found');
			}
		} else if (!is_array($this->_primary)) {
			$this->_primary = array(1 => $this->_primary);
		} else if (isset($this->_primary[0])) {
			array_unshift($this->_primary, null);
			unset($this->_primary[0]);
		}
		if (! array_intersect((array) $this->_primary, $this->_columns) == (array) $this->_primary) {
			throw new Yan_Table_Exception("Primary key column(s) ("
				. implode(',', (array) $this->_primary)
				. ") are not columns in this table ("
				. implode(',', $this->_columns)
				. ")");
		}
	}
}