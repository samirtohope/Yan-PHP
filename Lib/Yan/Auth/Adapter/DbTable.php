<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Auth/Adapter/Interface.php';

require_once 'Yan/Auth/Result.php';

/**
 * Yan_Auth_Adapter_DbTable
 *
 * @category  Yan
 * @package   Yan_Auth
 * @subpackage Adapter
 */
class Yan_Auth_Adapter_DbTable implements Yan_Auth_Adapter_Interface
{
	/**
	 * db adapter
	 *
	 * @var Yan_Db_Adapter
	 */
	protected $_db;
	/**
	 * $_tableName - the table name to check
	 *
	 * @var string
	 */
	protected $_tableName = null;

	/**
	 * $_identityColumn - the column to use as the identity
	 *
	 * @var string
	 */
	protected $_identityColumn = null;

	/**
	 * $_credentialColumns - columns to be used as the credentials
	 *
	 * @var string
	 */
	protected $_credentialColumn = null;

	/**
	 * $_identity - Identity value
	 *
	 * @var string
	 */
	protected $_identity = null;

	/**
	 * $_credential - Credential values
	 *
	 * @var string
	 */
	protected $_credential = null;

	protected $_fields = '*';

	public function __construct(Yan_Db_Adapter $adapter, $identity, $credential)
	{
		$this->_db = $adapter;
		$this->_identity = $identity;
		$this->_credential = $credential;
	}

	public function setTableName($tableName)
	{
		$this->_tableName = $tableName;
		return $this;
	}

	public function setIdentityColumn($identityColumn)
	{
		$this->_identityColumn = $identityColumn;
		return $this;
	}

	public function setCredentialColumn($credentialColumn)
	{
		$this->_credentialColumn = $credentialColumn;
		return $this;
	}

	public function setNeedFields($fields)
	{
		$this->_fields = $fields;
		return $this;
	}

	public function authenticate()
	{

		$exception = null;

		if (empty($this->_tableName)) {
			$exception = 'A table must be supplied.';
		} elseif (empty($this->_identityColumn)) {
			$exception = 'An identity column must be supplied.';
		} elseif (empty($this->_credentialColumn)) {
			$exception = 'A credential column must be supplied.';
		}

		if (null !== $exception) {
			require_once 'Yan/Auth/Adapter/Exception.php';
			throw new Yan_Auth_Adapter_Exception($exception);
		}

		$code = Yan_Auth_Result::FAILURE;
		$identity = $this->_identity;

		if (empty($this->_identity)) {
			$code = Yan_Auth_Result::FAILURE_IDENTITY_IS_NULL;
		} elseif (empty($this->_credential)) {
			$code = Yan_Auth_Result::FAILURE_CREDENTIAL_IS_NULL;
		} else {
			$select = new Yan_Db_Select($this->_db);
			$credentialExpression = new Yan_Db_Expr(
				'(CASE WHEN '
					. $this->_db->quoteIdentifier($this->_credentialColumn, true)
					. ' = ' . $this->_db->quote($this->_credential)
					. ' THEN 1 ELSE 0 END) AS '
					. $this->_db->quoteIdentifier('top_auth_credential_match')
			);
			$select->from($this->_tableName, $credentialExpression)
				->columns($this->_fields)
				->where(
				$this->_db->quoteIdentifier($this->_identityColumn, true)
					. ' = ' . $this->_db->quote($this->_identity)
			);
			try {
				$resultIdentities = $select->query()->fetchAll(Yan_Db::FETCH_ASSOC);
			} catch (Exception $e) {
				require_once 'Yan/Auth/Adapter/Exception.php';
				throw new Yan_Auth_Adapter_Exception(
					'Yan_Auth_Adapter_DbTable failed to produce a valid sql statement.'
				);
			}

			$result_c = count($resultIdentities);
			if ($result_c < 1) {
				$code = Yan_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
			} elseif ($result_c > 1) {
				$code = Yan_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
			} else {
				$resultIdentity = current($resultIdentities);
				if ($resultIdentity['top_auth_credential_match'] != '1') {
					$code = Yan_Auth_Result::FAILURE_CREDENTIAL_INVALID;
				} else {
					unset($resultIdentity['top_auth_credential_match']);
					$identity = $resultIdentity;
					$code = Yan_Auth_Result::SUCCESS;
				}
			}
		}

		return new Yan_Auth_Result($code, $identity);
	}
}