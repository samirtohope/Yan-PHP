<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Session
 *
 * @category  Yan
 * @package   Yan_Session
 */
abstract class Yan_Session
{

	/**
	 * Check whether or not the session was started
	 *
	 * @var bool
	 */
	protected static $_sessionStarted = false;

	/**
	 * Whether or not session has been destroyed via session_destroy()
	 *
	 * @var bool
	 */
	protected static $_destroyed = false;

	/**
	 * Whether or not session id cookie has been deleted
	 *
	 * @var bool
	 */
	protected static $_sessionCookieDeleted = false;

	/**
	 * Private list of php's ini values for ext/session
	 * null values will default to the php.ini value, otherwise
	 * the value below will overwrite the default ini value, unless
	 * the user has set an option explicity with setOptions()
	 *
	 * @var array
	 */
	protected static $_defaultOptions = array(
		'save_path'               => null,
		'name'                    => null, /* this should be set to a unique value for each application */
		'save_handler'            => null,
		//'auto_start'            => null, /* intentionally excluded (see manual) */
		'gc_probability'          => null,
		'gc_divisor'              => null,
		'gc_maxlifetime'          => null,
		'serialize_handler'       => null,
		'cookie_lifetime'         => null,
		'cookie_path'             => null,
		'cookie_domain'           => null,
		'cookie_secure'           => null,
		'cookie_httponly'         => null,
		'use_cookies'             => null,
		'use_only_cookies'        => 'on',
		'referer_check'           => null,
		'entropy_file'            => null,
		'entropy_length'          => null,
		'cache_limiter'           => null,
		'cache_expire'            => null,
		'use_trans_sid'           => null,
		'bug_compat_42'           => null,
		'bug_compat_warn'         => null,
		'hash_function'           => null,
		'hash_bits_per_character' => null
	);

	/**
	 * wheather the options has set
	 *
	 * @var bool
	 */
	protected static $_defaultOptionsSet = false;

	/**
	 * A reference to the set session save handler
	 *
	 * @var Yan_Session_SaveHandler
	 */
	protected static $_saveHandler = null;

	public static function setOptions(array $userOptions = array())
	{
		// set default options on first run only (before applying user settings)
		if (!self::$_defaultOptionsSet) {
			foreach (self::$_defaultOptions as $name => $value) {
				if (isset(self::$_defaultOptions[$name])) {
					ini_set("session.$name", $value);
				}
			}

			self::$_defaultOptionsSet = true;
		}

		// set the options the user has requested to set
		foreach ($userOptions as $userOptionName => $userOptionValue) {

			$userOptionName = strtolower($userOptionName);

			// set the ini based values
			if (array_key_exists($userOptionName, self::$_defaultOptions)) {
				ini_set("session.$userOptionName", $userOptionValue);
			} else {
				require_once 'Yan/Session/Exception.php';
				throw new Yan_Session_Exception("Unknown option: $userOptionName = $userOptionValue");
			}
		}
	}

	/**
	 * set a saveHandler for storage
	 *
	 * @param Yan_Session_SaveHandler $saveHandler
	 */
	public static function setSaveHandler(Yan_Session_SaveHandler $saveHandler)
	{
		session_set_save_handler(
			array($saveHandler, 'open'),
			array($saveHandler, 'close'),
			array($saveHandler, 'read'),
			array($saveHandler, 'write'),
			array($saveHandler, 'destroy'),
			array($saveHandler, 'gc')
		);
		self::$_saveHandler = $saveHandler;
	}

	/**
	 * get saveHandler
	 *
	 * @return Yan_Session_SaveHandler
	 */
	public static function getSaveHandler()
	{
		return self::$_saveHandler;
	}

	/**
	 * sessionExists() - whether or not a session exists for the current request
	 *
	 * @return bool
	 */
	public static function sessionExists()
	{
		if ((bool)ini_get('session.use_cookies') == true && isset($_COOKIE[session_name()])) {
			return true;
		} elseif ((bool)ini_get('session.use_only_cookies') == false && isset($_REQUEST[session_name()])) {
			return true;
		}

		return false;
	}

	/**
	 * Whether or not session has been destroyed via session_destroy()
	 *
	 * @return bool
	 */
	public static function isDestroyed()
	{
		return self::$_destroyed;
	}

	/**
	 * start session
	 *
	 * @param bool $options
	 *
	 * @throws Yan_Session_Exception
	 */
	public static function start($options = false)
	{
		if (self::$_sessionStarted && self::$_destroyed) {
			require_once 'Yan/Session/Exception.php';
			throw new Yan_Session_Exception('The session was explicitly destroyed during this request.');
		}

		if (self::$_sessionStarted) {
			return; // already started
		}

		// make sure our default options (at the least) have been set
		if (!self::$_defaultOptionsSet) {
			self::setOptions(is_array($options) ? $options : array());
		}

		$filename = $linenum = null;
		if (headers_sent($filename, $linenum)) {
			require_once 'Yan/Session/Exception.php';
			throw new Yan_Session_Exception("Session must be started before any output has been sent to the browser;"
				. " output started in {$filename}/{$linenum}");
		}

		if (defined('SID')) {
			require_once 'Yan/Session/Exception.php';
			throw new Yan_Session_Exception('session has already been started by session.auto-start or session_start()');
		}

		try {
			session_start();
		} catch (Exception $e) {
			session_write_close();
			require_once 'Yan/Session/Exception.php';
			throw new Yan_Session_Exception(__CLASS__ . '::' . __FUNCTION__ . '() - ' . $e->getMessage());
		}

		self::$_sessionStarted = true;
	}

	/**
	 * isStarted() - convenience method to determine if the session is already started.
	 *
	 * @return bool
	 */
	public static function isStarted()
	{
		return self::$_sessionStarted;
	}

	/**
	 * reset sessid
	 *
	 * @param $id
	 *
	 * @throws Yan_Session_Exception
	 */
	public static function setId($id)
	{
		if (defined('SID')) {
			require_once 'Yan/Session/Exception.php';
			throw new Yan_Session_Exception('The session has already been started.  The session id must be set first.');
		}

		$filename = $linenum = null;
		if (headers_sent($filename, $linenum)) {
			require_once 'Yan/Session/Exception.php';
			throw new Yan_Session_Exception("You must call " . __CLASS__ . '::' . __FUNCTION__ .
				"() before any output has been sent to the browser; output started in {$filename}/{$linenum}");
		}

		if (!is_string($id) || $id === '') {
			require_once 'Yan/Session/Exception.php';
			throw new Yan_Session_Exception('You must provide a non-empty string as a session identifier.');
		}

		session_id($id);
	}

	/**
	 * destroy() - This is used to destroy session data, and optionally, the session cookie itself
	 *
	 * @param bool $remove_cookie - OPTIONAL remove session id cookie, defaults to true (remove cookie)
	 *
	 * @return void
	 */
	public static function destroy($remove_cookie = true)
	{
		if (self::$_destroyed) {
			return;
		}

		session_destroy();
		self::$_destroyed = true;

		if ($remove_cookie) {
			self::expireSessionCookie();
		}
	}

	/**
	 * expireSessionCookie() - Sends an expired session id cookie, causing the client to delete the session cookie
	 *
	 * @return void
	 */
	public static function expireSessionCookie()
	{
		if (self::$_sessionCookieDeleted) {
			return;
		}

		self::$_sessionCookieDeleted = true;

		if (isset($_COOKIE[session_name()])) {
			$cookie_params = session_get_cookie_params();

			setcookie(
				session_name(),
				false,
				315554400, // strtotime('1980-01-01'),
				$cookie_params['path'],
				$cookie_params['domain'],
				$cookie_params['secure']
			);
		}
	}
}