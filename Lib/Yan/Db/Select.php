<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Db/Adapter.php';

require_once 'Yan/Db/Expr.php';

/**
 * Yan_Db_Select
 *
 * @category   Yan
 * @package    Yan_Db
 */
class Yan_Db_Select
{

	const DISTINCT = 'distinct';
	const COLUMNS = 'columns';
	const FROM = 'from';
	const UNION = 'union';
	const WHERE = 'where';
	const GROUP = 'group';
	const HAVING = 'having';
	const ORDER = 'order';
	const LIMIT_COUNT = 'limitcount';
	const LIMIT_OFFSET = 'limitoffset';
	const FOR_UPDATE = 'forupdate';

	const INNER_JOIN = 'inner join';
	const LEFT_JOIN = 'left join';
	const RIGHT_JOIN = 'right join';
	const FULL_JOIN = 'full join';
	const CROSS_JOIN = 'cross join';
	const NATURAL_JOIN = 'natural join';

	const SQL_WILDCARD = '*';
	const SQL_SELECT = 'SELECT';
	const SQL_UNION = 'UNION';
	const SQL_UNION_ALL = 'UNION ALL';
	const SQL_FROM = 'FROM';
	const SQL_WHERE = 'WHERE';
	const SQL_DISTINCT = 'DISTINCT';
	const SQL_GROUP_BY = 'GROUP BY';
	const SQL_ORDER_BY = 'ORDER BY';
	const SQL_HAVING = 'HAVING';
	const SQL_FOR_UPDATE = 'FOR UPDATE';
	const SQL_AND = 'AND';
	const SQL_AS = 'AS';
	const SQL_OR = 'OR';
	const SQL_ON = 'ON';
	const SQL_ASC = 'ASC';
	const SQL_DESC = 'DESC';

	/**
	 * The initial values for the $_parts array.
	 * NOTE: It is important for the 'FOR_UPDATE' part to be last to ensure
	 * meximum compatibility with database adapters.
	 *
	 * @var array
	 */
	protected static $_partsInit = array(
		self::DISTINCT => false,
		self::COLUMNS => array(),
		self::UNION => array(),
		self::FROM => array(),
		self::WHERE => array(),
		self::GROUP => array(),
		self::HAVING => array(),
		self::ORDER => array(),
		self::LIMIT_COUNT => null,
		self::LIMIT_OFFSET => null,
		self::FOR_UPDATE => false
	);

	/**
	 * Specify legal join types.
	 *
	 * @var array
	 */
	protected static $_joinTypes = array(
		self::INNER_JOIN => true,
		self::LEFT_JOIN => true,
		self::RIGHT_JOIN => true,
		self::FULL_JOIN => true,
		self::CROSS_JOIN => true,
		self::NATURAL_JOIN => true
	);

	/**
	 * Specify legal union types.
	 *
	 * @var array
	 */
	protected static $_unionTypes = array(
		self::SQL_UNION,
		self::SQL_UNION_ALL
	);

	/**
	 * The component parts of a SELECT statement.
	 * Initialized to the $_partsInit array in the constructor.
	 *
	 * @var array
	 */
	protected $_parts = array();

	/**
	 * Bind variables for query
	 *
	 * @var array
	 */
	protected $_bind = array();

	/**
	 * Yan_Db_Adapter object.
	 *
	 * @var Yan_Db_Adapter
	 */
	protected $_adapter;

	public function __construct(Yan_Db_Adapter $adapter)
	{
		$this->_adapter = $adapter;
		$this->_parts = self::$_partsInit;
	}

	/**
	 * Clear parts of the Select object, or an individual part.
	 *
	 * @param string $part OPTIONAL
	 * @return Yan_Db_Select
	 */
	public function reset($part = null)
	{
		if ($part == null) {
			$this->_parts = self::$_partsInit;
		} else if (array_key_exists($part, self::$_partsInit)) {
			$this->_parts[$part] = self::$_partsInit[$part];
		}
		return $this;
	}

	/**
	 * Get bind variables
	 *
	 * @return array
	 */
	public function getBind()
	{
		return $this->_bind;
	}

	/**
	 * Set bind variables
	 *
	 * @param mixed $bind
	 * @return Yan_Db_Select
	 */
	public function bind($bind)
	{
		$this->_bind = $bind;

		return $this;
	}

	/**
	 * Gets the Yan_Db_Adapter for this
	 * particular Yan_Db_Select object.
	 *
	 * @return Yan_Db_Adapter
	 */
	public function getAdapter()
	{
		return $this->_adapter;
	}

	/**
	 * Executes the current select object and returns the result
	 *
	 * @param integer $fetchMode OPTIONAL
	 * @param  mixed  $bind An array of data to bind to the placeholders.
	 * @return PDOStatement
	 */
	public function query($bind = array(), $fetchMode = null)
	{
		if (!empty($bind)) {
			$this->bind($bind);
		}

		$stmt = $this->_adapter->query($this);
		if ($fetchMode != null) {
			$stmt->setFetchMode($fetchMode);
		}
		return $stmt;
	}

	/**
	 * Prepare the current select object
	 *
	 * @param integer $fetchMode OPTIONAL
	 * @return PDOStatement
	 */
	public function prepare($fetchMode = null)
	{
		$stmt = $this->_adapter->prepare($this->assemble());
		if ($fetchMode != null) {
			$stmt->setFetchMode($fetchMode);
		}
		return $stmt;
	}

	/**
	 * Get part of the structured information for the currect query.
	 *
	 * @param string $part
	 * @return mixed
	 * @throws Yan_Db_Exception
	 */
	public function getPart($part)
	{
		if (!array_key_exists($part, $this->_parts)) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception("Invalid Select part '$part'");
		}
		return $this->_parts[$part];
	}

	public function setPart($part, $val)
	{
		if (!array_key_exists($part, $this->_parts)) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception("Invalid Select part '$part'");
		}
		$this->_parts[$part] = $val;
	}

	public function __toString()
	{
		return $this->assemble();
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function distinct()
	{
		$this->_parts[self::DISTINCT] = true;
		return $this;
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function forUpdate()
	{
		$this->_parts[self::FOR_UPDATE] = true;
		return $this;
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function from($table, $cols = '*')
	{
		return $this->join(self::INNER_JOIN, $table, null, $cols);
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function columns($cols = '*', $correlationName = null)
	{
		if ($correlationName === null && count($this->_parts[self::FROM])) {
			$aliases = array_keys($this->_parts[self::FROM]);
			$correlationName = current($aliases);
		}

		if (!array_key_exists($correlationName, $this->_parts[self::FROM])) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception("No table has been specified for the FROM clause");
		}

		$this->_tableCols($correlationName, $cols);

		return $this;
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function join($type, $table, $on, $columns = '*')
	{
		if (!isset(self::$_joinTypes[$type])) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception("Invalid join type '$type'");
		}
		list($table, $schema, $alias) = $this->_parseTable($table);
		if (!empty($alias)) {
			$this->_parts[self::FROM][$alias] = array(
				'joinType' => $type,
				'tableName' => $table,
				'schema' => $schema,
				'joinCond' => $on
			);
		}
		$this->_tableCols($alias, $columns);
		return $this;
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function limit($count = 30, $offset = 0)
	{
		$this->_parts[self::LIMIT_COUNT] = (int)$count;
		$this->_parts[self::LIMIT_OFFSET] = (int)$offset;
		return $this;
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function top($length = 30)
	{
		return $this->limit($length);
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function group($spec)
	{
		if (!is_array($spec)) {
			$spec = array($spec);
		}

		foreach ($spec as $val) {
			if (preg_match('/\(.*\)/', (string)$val)) {
				$val = new Yan_Db_Expr($val);
			}
			$this->_parts[self::GROUP][] = $val;
		}

		return $this;
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function having($cond, $val = null, $or = false)
	{
		if ($val !== null) {
			$cond = $this->_adapter->quoteInto($cond, $val);
		}
		if ($this->_parts[self::HAVING]) {
			$this->_parts[self::HAVING][] = ($or ? self::SQL_OR : self::SQL_AND) . " ($cond)";
		} else {
			$this->_parts[self::HAVING][] = "($cond)";
		}
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function order($expr)
	{
		if (!is_array($expr)) {
			$expr = explode(',', $expr);
		}
		foreach ($expr as $val) {
			if ($val instanceof Yan_Db_Expr) {
				$this->_parts[self::ORDER][] = $val;
			} else {
				if (!($val = trim($val))) {
					continue;
				}
				$direction = self::SQL_ASC;
				if (preg_match('/^([a-z][\w\.]*)\s*(' . self::SQL_ASC . '|' . self::SQL_DESC . ')?$/i', $val, $m)) {
					$val = $m[1];
					if (isset($m[2])) {
						$direction = $m[2];
					}
				}
				if (preg_match('/\(.*\)/', $val)) {
					$val = new Yan_Db_Expr($val);
				}
				$this->_parts[self::ORDER][] = array($val, $direction);
			}
		}
		return $this;
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function where($where, $value = null, $type = null)
	{
		$this->_where($where, $value, $type);
		return $this;
	}

	/**
	 * @return Yan_Db_Select
	 */
	public function orWhere($where, $value = null, $type = null)
	{
		$this->_where($where, $value, $type, true);
		return $this;
	}

	protected function _where($where, $value = null, $type = null, $or = false)
	{
		if (is_array($where)) {
			$condition = array();
			foreach ($where as $expr => $v) {
				$c = $this->_adapter->cond($expr, $v, $type);
				if (strlen($c)) $condition[] = $c;
			}
			$condition = implode(' ' . self::SQL_AND . ' ', $condition);
		} else {
			$condition = $this->_adapter->cond($where, $value, $type);
		}
		if (strlen($condition)) {
			if ($this->_parts[self::WHERE]) {
				$this->_parts[self::WHERE][] = ($or ? self::SQL_OR : self::SQL_AND) . " ($condition)";
			} else {
				$this->_parts[self::WHERE][] = "($condition)";
			}
		}
	}

	/**
	 * Converts this object to an SQL string.
	 *
	 * @return string This object as a sql string.
	 */
	public function assemble()
	{
		$sql = self::SQL_SELECT;
		foreach (array_keys(self::$_partsInit) as $part) {
			$method = '_render' . ucfirst($part);
			if (method_exists($this, $method)) {
				$sql = $this->$method($sql);
			}
		}
		return $sql;
	}

	protected function _parseTable($table)
	{
		if (is_array($table)) {
			return $table;
		}
		if ($table instanceof Yan_Table) {
			$info = $table->info();
			return array(
				$info[Yan_Table::NAME],
				$info[Yan_Table::SCHEMA],
				$info[Yan_Table::ALIAS_NAME]
			);
		}
		$alias = null;
		if (preg_match('/^(.+)\s+(?:' . self::SQL_AS . '\s+)?(.+)$/i', $table, $m)) {
			$table = $m[1];
			$alias = $m[2];
		}
		$m = explode('.', $table);
		if (isset($m[1])) {
			$schema = $m[0];
			$table = $m[1];
		} else {
			$schema = null;
		}
		$alias = $this->_uniqueAlias($alias ? $alias : $table);
		return array($table, $schema, $alias);
	}

	protected function _tableCols($correlationName, $columns)
	{
		if ($columns instanceof Yan_Db_Expr) {
			$this->_parts[self::COLUMNS][] = array(
				$correlationName,
				$columns,
				null
			);
			return;
		}
		if (!is_array($columns)) {
			$columns = explode(',', $columns);
		}

		if ($correlationName == null) {
			$correlationName = '';
		}

		foreach (array_filter($columns) as $alias => $col) {
			$currentCorrelationName = $correlationName;
			if (is_string($col)) {
				if (!($col = trim($col))) {
					continue;
				}
				if (preg_match('/^(.+)\s+' . self::SQL_AS . '\s+(.+)$/i', $col, $m)) {
					$col = $m[1];
					$alias = $m[2];
				}
				if (preg_match('/\(.*\)/', $col)) {
					$col = new Yan_Db_Expr($col);
				} elseif (preg_match('/(.+)\.(.+)/', $col, $m)) {
					$currentCorrelationName = $m[1];
					$col = $m[2];
				}
			}
			$this->_parts[self::COLUMNS][] = array(
				$currentCorrelationName,
				$col,
				is_string($alias) ? $alias : null
			);
		}
	}

	protected function _uniqueAlias($name)
	{
		$c = $name;
		for ($i = 2; array_key_exists($c, $this->_parts[self::FROM]); ++$i) {
			$c = $name . '_' . $i;
		}
		return $c;
	}

	/**
	 * Return a quoted table name
	 *
	 * @param string   $tableName        The table name
	 * @param string   $correlationName  The correlation name OPTIONAL
	 * @return string
	 */
	protected function _getQuotedTable($table, $correlationName = null)
	{
		return $this->_adapter->quoteTableAs($table, $correlationName, true);
	}

	/**
	 * Render DISTINCT clause
	 *
	 * @param string   $sql SQL query
	 * @return string
	 */
	protected function _renderDistinct($sql)
	{
		if ($this->_parts[self::DISTINCT]) {
			$sql .= ' ' . self::SQL_DISTINCT;
		}

		return $sql;
	}

	protected function _renderColumns($sql)
	{
		if (!count($this->_parts[self::COLUMNS])) {
			return null;
		}
		$columns = array();
		foreach ($this->_parts[self::COLUMNS] as $columnEntry) {
			list($correlationName, $column, $alias) = $columnEntry;
			if ($column instanceof Yan_Db_Expr) {
				$columns[] = $this->_adapter->quoteColumnAs($column, $alias, true);
			} else {
				if ($column == self::SQL_WILDCARD) {
					$column = new Yan_Db_Expr(self::SQL_WILDCARD);
					$alias = null;
				}
				if (empty($correlationName)) {
					$columns[] = $this->_adapter->quoteColumnAs($column, $alias, true);
				} else {
					$columns[] = $this->_adapter->quoteColumnAs(array($correlationName, $column), $alias, true);
				}
			}
		}

		return $sql . ' ' . implode(', ', $columns);
	}

	/**
	 * Render FROM clause
	 *
	 * @param string   $sql SQL query
	 * @return string
	 */
	protected function _renderFrom($sql)
	{
		if (empty($this->_parts[self::FROM])) {
			// $this->_parts[self::FROM] = $this->_getDummyTable();
		}

		$from = array();

		foreach ($this->_parts[self::FROM] as $correlationName => $table) {
			$tmp = '';

			// Add join clause (if applicable)
			if (!empty($from)) {
				$tmp .= ' ' . strtoupper($table['joinType']) . ' ';
			}

			$tmp .= $this->_getQuotedTable(array($table['schema'], $table['tableName']), $correlationName);

			// Add join conditions (if applicable)
			if (!empty($from) && !empty($table['joinCond'])) {
				$tmp .= ' ' . self::SQL_ON . ' ' . $table['joinCond'];
			}

			// Add the table name and condition add to the list
			$from[] = $tmp;
		}

		// Add the list of all joins
		if (!empty($from)) {
			$sql .= ' ' . self::SQL_FROM . ' ' . implode("\n", $from);
		}

		return $sql;
	}

	/**
	 * Render WHERE clause
	 *
	 * @param string   $sql SQL query
	 * @return string
	 */
	protected function _renderWhere($sql)
	{
		if ($this->_parts[self::FROM] && $this->_parts[self::WHERE]) {
			$sql .= ' ' . self::SQL_WHERE . ' ' . implode(' ', $this->_parts[self::WHERE]);
		}

		return $sql;
	}

	/**
	 * Render GROUP clause
	 *
	 * @param string   $sql SQL query
	 * @return string
	 */
	protected function _renderGroup($sql)
	{
		if ($this->_parts[self::FROM] && $this->_parts[self::GROUP]) {
			$group = array();
			foreach ($this->_parts[self::GROUP] as $term) {
				$group[] = $this->_adapter->quoteIdentifier($term, true);
			}
			$sql .= ' ' . self::SQL_GROUP_BY . ' ' . implode(",\n\t", $group);
		}

		return $sql;
	}

	/**
	 * Render HAVING clause
	 *
	 * @param string   $sql SQL query
	 * @return string
	 */
	protected function _renderHaving($sql)
	{
		if ($this->_parts[self::FROM] && $this->_parts[self::HAVING]) {
			$sql .= ' ' . self::SQL_HAVING . ' ' . implode(' ', $this->_parts[self::HAVING]);
		}

		return $sql;
	}

	/**
	 * Render ORDER clause
	 *
	 * @param string   $sql SQL query
	 * @return string
	 */
	protected function _renderOrder($sql)
	{
		if ($this->_parts[self::ORDER]) {
			$order = array();
			foreach ($this->_parts[self::ORDER] as $term) {
				if (is_array($term)) {
					$order[] = $this->_adapter->quoteIdentifier($term[0], true) . ' ' . $term[1];
				} else {
					$order[] = $this->_adapter->quoteIdentifier($term, true);
				}
			}
			$sql .= ' ' . self::SQL_ORDER_BY . ' ' . implode(', ', $order);
		}

		return $sql;
	}

	/**
	 * Render LIMIT OFFSET clause
	 *
	 * @param string   $sql SQL query
	 * @return string
	 */
	protected function _renderLimitoffset($sql)
	{
		$count = 0;
		$offset = 0;

		if (!empty($this->_parts[self::LIMIT_OFFSET])) {
			$offset = (int)$this->_parts[self::LIMIT_OFFSET];
			// This should reduce to the max integer PHP can support
			$count = intval(9223372036854775807);
		}

		if (!empty($this->_parts[self::LIMIT_COUNT])) {
			$count = (int)$this->_parts[self::LIMIT_COUNT];
		}

		/*
		 * Add limits clause
		 */
		if ($count > 0) {
			$sql = trim($this->_adapter->limit($sql, $count, $offset));
		}

		return $sql;
	}

	/**
	 * Render FOR UPDATE clause
	 *
	 * @param string   $sql SQL query
	 * @return string
	 */
	protected function _renderForupdate($sql)
	{
		if ($this->_parts[self::FOR_UPDATE]) {
			$sql .= ' ' . self::SQL_FOR_UPDATE;
		}

		return $sql;
	}
}