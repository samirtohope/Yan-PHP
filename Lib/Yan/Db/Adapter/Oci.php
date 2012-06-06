<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

require_once 'Yan/Db/Adapter.php';

/**
 * Yan_Db_Adapter_Oci
 *
 * @category   Yan
 * @package    Yan_Db
 * @subpackage Adapter
 */
class Yan_Db_Adapter_Oci extends Yan_Db_Adapter
{

	/**
	 * Keys are UPPERCASE SQL datatypes or the constants
	 * Yan_Db::INT_TYPE, Yan_Db::BIGINT_TYPE, or Yan_Db::FLOAT_TYPE.
	 *
	 * Values are:
	 * 0 = 32-bit integer
	 * 1 = 64-bit integer
	 * 2 = float or decimal
	 *
	 * @var array Associative array of datatypes to values 0, 1, or 2.
	 */
	protected $_numericDataTypes = array(
		Yan_Db::INT_TYPE    => Yan_Db::INT_TYPE,
		Yan_Db::BIGINT_TYPE => Yan_Db::BIGINT_TYPE,
		Yan_Db::FLOAT_TYPE  => Yan_Db::FLOAT_TYPE,
		'BINARY_DOUBLE'     => Yan_Db::FLOAT_TYPE,
		'BINARY_FLOAT'      => Yan_Db::FLOAT_TYPE,
		'NUMBER'            => Yan_Db::FLOAT_TYPE
	);

	protected $_driver = 'oci';

	protected $_savedPoints = array();

	protected $_transCount = 0;

	protected function _dsn()
	{
		// baseline of DSN parts
		$dsn = $this->_config;

		if (isset($dsn['host'])) {
			$tns = 'dbname=(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)' .
				'(HOST=' . $dsn['host'] . ')';

			if (isset($dsn['port'])) {
				$tns .= '(PORT=' . $dsn['port'] . ')';
			} else {
				$tns .= '(PORT=1521)';
			}

			$tns .= '))(CONNECT_DATA=(SID=' . $dsn['dbname'] . ')))';
		} else {
			$tns = 'dbname=' . $dsn['dbname'];
		}

		if (isset($dsn['charset'])) {
			$tns .= ';charset=' . $dsn['charset'];
		}

		return $this->_driver . ':' . $tns;
	}

	/**
	 * Quote a raw string.
	 *
	 * @param string $value     Raw string
	 *
	 * @return string           Quoted string
	 */
	protected function _quote($value)
	{
		if (is_int($value) || is_float($value)) {
			return $value;
		}
		$value = str_replace("'", "''", $value);
		return "'" . addcslashes($value, "\000\n\r\\\032") . "'";
	}

	/**
	 * Quote a table identifier and alias.
	 *
	 * @param string|array|Yan_Db_Expr $ident The identifier or expression.
	 * @param string                   $alias An alias for the table.
	 * @param boolean                  $auto  If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
	 *
	 * @return string The quoted identifier and alias.
	 */
	public function quoteTableAs($ident, $alias = null, $auto = false)
	{
		return $this->_quoteTableAs($ident, $alias, $auto, ' ');
	}

	/**
	 * Returns the column descriptions for a table.
	 *
	 * The value of each array element is an associative array
	 * with the following keys:
	 *
	 * COLUMN_NAME   => string; column name
	 * COLUMN_POS    => number; ordinal position of column in table
	 * DATA_TYPE     => string; SQL datatype name of column
	 * DEFAULT       => string; default expression of column, null if none
	 * NULLABLE      => boolean; true if column can have nulls
	 * LENGTH        => number; length of CHAR/VARCHAR
	 * UNSIGNED      => boolean; unsigned property of an integer type
	 * PRIMARY       => boolean; true if column is part of the primary key
	 * PRIMARY_POS   => integer; position of column in primary key
	 * IDENTITY      => integer; true if column isautoIncrement
	 *
	 * @param string $table
	 * @param string $schema
	 * @param bool   $hasQuoted
	 *
	 * @return array
	 */
	public function describeTable($table, $schema = null, $hasQuoted = false)
	{
		if (!$hasQuoted) {
			$table = $this->quoteTableAs($schema ? "$schema.$table" : $table, null, true);
		}
		$sql = "SELECT TC.COLUMN_NAME, TC.DATA_TYPE, TC.DATA_DEFAULT, TC.NULLABLE,
 				TC.COLUMN_ID, TC.DATA_LENGTH, C.CONSTRAINT_TYPE, CC.POSITION
			FROM ALL_TAB_COLUMNS TC
			LEFT JOIN (ALL_CONS_COLUMNS CC JOIN ALL_CONSTRAINTS C
				ON (CC.CONSTRAINT_NAME = C.CONSTRAINT_NAME AND CC.TABLE_NAME = C.TABLE_NAME AND CC.OWNER = C.OWNER AND C.CONSTRAINT_TYPE = 'P'))
			  ON TC.TABLE_NAME = CC.TABLE_NAME AND TC.COLUMN_NAME = CC.COLUMN_NAME
			WHERE UPPER(TC.TABLE_NAME) = UPPER(:TBNAME)";
		$bind[':TBNAME'] = $table;
		if ($schema) {
			$sql .= ' AND UPPER(TC.OWNER) = UPPER(:SCNAME)';
			$bind[':SCNAME'] = $schema;
		}
		$sql .= ' ORDER BY TC.COLUMN_ID';


		$stmt = $this->query($sql, $bind);

		$rowset = $stmt->fetchAll(Yan_Db::FETCH_NUM);

		$field = 0;
		$type = 1;
		$default = 2;
		$null = 3;
		$columnid = 4;
		$length = 5;
		$extra = 6;
		$position = 7;

		$desc = array();
		foreach ($rowset as $row) {
			list ($primary, $primaryPos) = array(false, null);
			if ($row[$extra] == 'P') {
				$primary = true;
				$primaryPos = $row[$position];
			}
			$columnName = $row[$field];
			$desc[$columnName] = array(
				'COLUMN_NAME' => $columnName,
				'COLUMN_POS'  => $row[$columnid],
				'DATA_TYPE'   => $row[$type],
				'DEFAULT'     => $row[$default],
				'NULLABLE'    => (bool)($row[$null] == 'Y'),
				'LENGTH'      => $row[$length],
				'UNSIGNED'    => null,
				'PRIMARY'     => $primary,
				'PRIMARY_POS' => $primaryPos,
				'IDENTITY'    => false
			);
		}
		switch ($this->_caseFolding) {
		case Yan_Db::CASE_LOWER:
			$desc = array_change_key_case($desc, CASE_LOWER);
			break;
		case Yan_Db::CASE_UPPER:
			$desc = array_change_key_case($desc, CASE_UPPER);
			break;
		}
		return $desc;
	}

	/**
	 * Return the most recent value from the specified sequence in the database.
	 * This is supported only on RDBMS brands that support sequences
	 * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
	 *
	 * @param string $sequenceName
	 *
	 * @return int
	 */
	public function lastSequenceId($sequenceName)
	{
		$this->_connect();
		return $this->fetchOne('SELECT ' . $this->quoteIdentifier($sequenceName, true) . '.CURRVAL FROM dual');
	}

	/**
	 * Adds an adapter-specific LIMIT clause to the SELECT statement.
	 *
	 * @param mixed   $sql
	 * @param integer $count
	 * @param integer $offset
	 *
	 * @throws Yan_Db_Adapter_Exception
	 * @return string
	 */
	public function limit($sql, $count, $offset = 0)
	{
		$count = intval($count);
		if ($count <= 0) {
			require_once 'Yan/Db/Adapter/Exception.php';
			throw new Yan_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
		}

		$offset = intval($offset);
		if ($offset < 0) {
			require_once 'Yan/Db/Adapter/Exception.php';
			throw new Yan_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
		}

		/**
		 * Oracle does not implement the LIMIT clause as some RDBMS do.
		 * We have to simulate it with subqueries and ROWNUM.
		 * Unfortunately because we use the column wildcard "*",
		 * this puts an extra column into the query result set.
		 */
		$limit_sql = "SELECT t2.*
            FROM (
                SELECT t1.*, ROWNUM AS \"t_rownum\"
                FROM (
                    " . $sql . "
                ) t1
            ) t2
            WHERE t2.\"t_rownum\" BETWEEN " . ($offset + 1) . " AND " . ($offset + $count);
		return $limit_sql;
	}

	/**
	 * Leave autocommit mode and begin a transaction.
	 *
	 * @return bool
	 */
	public function beginTransaction()
	{
		$conn = $this->getConnection();
		if ($this->_transCount < 1) {
			$conn->exec('START TRANSACTION');
		} else {
			$point = 'POINT_' . $this->_transCount;
			$conn->exec('SAVEPOINT ' . $this->_quoteIdentifier($point));
			array_push($this->_savedPoints, $point);
		}
		++$this->_transCount;
		return true;
	}

	/**
	 * Roll back a transaction and return to autocommit mode.
	 *
	 * @return bool
	 */
	public function rollBack()
	{
		$conn = $this->getConnection();
		if ($this->_transCount < 1) {
			$this->_transCount = 0;
			return true;
		}
		if (--$this->_transCount == 0) {
			$conn->exec('ROLLBACK');
		} else {
			$point = array_pop($this->_savedPoints);
			$conn->exec('ROLLBACK TO SAVEPOINT ' . $this->_quoteIdentifier($point));
		}
		return true;
	}

	/**
	 * Commit a transaction and return to autocommit mode.
	 *
	 * @return bool
	 */
	public function commit()
	{
		$conn = $this->getConnection();
		if ($this->_transCount < 1) {
			$this->_transCount = 0;
			return true;
		}
		if (--$this->_transCount == 0) {
			$conn->exec('COMMIT');
		} else {
			array_pop($this->_savedPoints);
		}
		return true;
	}
}
