<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Class for SQL SELECT fragments.
 *
 * @category   Yan
 * @package    Yan_Db
 */
class Yan_Db_Expr
{
	/**
	 * Storage for the SQL expression.
	 *
	 * @var string
	 */
	protected $_expr;

	/**
	 * Instantiate an expression, which is just a string stored as
	 * an instance member variable.
	 *
	 * @param string $expr The string containing a SQL expression.
	 */
	public function __construct($expr)
	{
		$this->_expr = (string)$expr;
	}

	/**
	 * @return string The string of the SQL expression stored in this object.
	 */
	public function __toString()
	{
		return $this->_expr;
	}
}