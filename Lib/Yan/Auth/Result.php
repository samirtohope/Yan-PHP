<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Auth_Result
 *
 * @category  Yan
 * @package   Yan_Auth
 */
class Yan_Auth_Result
{
	/**
	 * General Failure
	 */
	const FAILURE = 0;

	/**
	 * Failure due to identity not being found.
	 */
	const FAILURE_IDENTITY_NOT_FOUND = -1;

	/**
	 * Failure due to identity being ambiguous.
	 */
	const FAILURE_IDENTITY_AMBIGUOUS = -2;

	/**
	 * Failure due to invalid credential being supplied.
	 */
	const FAILURE_CREDENTIAL_INVALID = -3;

	const FAILURE_IDENTITY_IS_NULL = -4;

	const FAILURE_CREDENTIAL_IS_NULL = -5;

	/**
	 * Failure due to uncategorized reasons.
	 */
	const FAILURE_UNCATEGORIZED = -6;

	/**
	 * Authentication success.
	 */
	const SUCCESS = 1;

	/**
	 * Authentication result code
	 *
	 * @var int
	 */
	protected $_code;

	/**
	 * The identity used in the authentication attempt
	 *
	 * @var mixed
	 */
	protected $_identity;

	/**
	 * Sets the result code, identity, and failure messages
	 *
	 * @param  int     $code
	 * @param  mixed   $identity
	 * @param  array   $messages
	 * @return void
	 */
	public function __construct($code, $identity)
	{
		$code = (int)$code;

		if ($code < self::FAILURE_UNCATEGORIZED) {
			$code = self::FAILURE;
		} elseif ($code > self::SUCCESS) {
			$code = 1;
		}

		$this->_code = $code;
		$this->_identity = $identity;
	}

	/**
	 * Returns whether the result represents a successful authentication attempt
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return ($this->_code > 0) ? true : false;
	}

	/**
	 * getCode() - Get the result code for this authentication attempt
	 *
	 * @return int
	 */
	public function getCode()
	{
		return $this->_code;
	}

	/**
	 * Returns the identity used in the authentication attempt
	 *
	 * @return mixed
	 */
	public function getIdentity()
	{
		return $this->_identity;
	}
}