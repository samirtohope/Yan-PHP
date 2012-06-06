<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Db
 *
 * @category  Yan
 * @package   Yan_Db
 */
abstract class Yan_Db
{

	/**
	 * Use the CASE_FOLDING constant in the config of a Yan_Db_Adapter.
	 */
	const CASE_FOLDING = 'caseFolding';

	/**
	 * Use the FETCH_MODE constant in the config of a Yan_Db_Adapter.
	 */
	const FETCH_MODE = 'fetchMode';

	/**
	 * Use the INT_TYPE, BIGINT_TYPE, and FLOAT_TYPE with the quote() method.
	 */
	const INT_TYPE = 0;
	const BIGINT_TYPE = 1;
	const FLOAT_TYPE = 2;

	const ATTR_AUTOCOMMIT = 0;
	const ATTR_CASE = 8;
	const ATTR_CLIENT_VERSION = 5;
	const ATTR_CONNECTION_STATUS = 7;
	const ATTR_CURSOR = 10;
	const ATTR_CURSOR_NAME = 9;
	const ATTR_DRIVER_NAME = 16;
	const ATTR_ERRMODE = 3;
	const ATTR_FETCH_CATALOG_NAMES = 15;
	const ATTR_FETCH_TABLE_NAMES = 14;
	const ATTR_MAX_COLUMN_LEN = 18;
	const ATTR_ORACLE_NULLS = 11;
	const ATTR_PERSISTENT = 12;
	const ATTR_PREFETCH = 1;
	const ATTR_SERVER_INFO = 6;
	const ATTR_SERVER_VERSION = 4;
	const ATTR_STATEMENT_CLASS = 13;
	const ATTR_STRINGIFY_FETCHES = 17;
	const ATTR_TIMEOUT = 2;
	const CASE_NATURAL = 0;
	const CASE_LOWER = 2;
	const CASE_UPPER = 1;
	const CURSOR_FWDONLY = 0;
	const CURSOR_SCROLL = 1;
	const ERR_ALREADY_EXISTS = NULL;
	const ERR_CANT_MAP = NULL;
	const ERR_CONSTRAINT = NULL;
	const ERR_DISCONNECTED = NULL;
	const ERR_MISMATCH = NULL;
	const ERR_NO_PERM = NULL;
	const ERR_NONE = '00000';
	const ERR_NOT_FOUND = NULL;
	const ERR_NOT_IMPLEMENTED = NULL;
	const ERR_SYNTAX = NULL;
	const ERR_TRUNCATED = NULL;
	const ERRMODE_EXCEPTION = 2;
	const ERRMODE_SILENT = 0;
	const ERRMODE_WARNING = 1;
	const FETCH_ASSOC = 2;
	const FETCH_BOTH = 4;
	const FETCH_BOUND = 6;
	const FETCH_CLASS = 8;
	const FETCH_CLASSTYPE = 262144;
	const FETCH_COLUMN = 7;
	const FETCH_FUNC = 10;
	const FETCH_GROUP = 65536;
	const FETCH_INTO = 9;
	const FETCH_LAZY = 1;
	const FETCH_NAMED = 11;
	const FETCH_NUM = 3;
	const FETCH_OBJ = 5;
	const FETCH_ORI_ABS = 4;
	const FETCH_ORI_FIRST = 2;
	const FETCH_ORI_LAST = 3;
	const FETCH_ORI_NEXT = 0;
	const FETCH_ORI_PRIOR = 1;
	const FETCH_ORI_REL = 5;
	const FETCH_SERIALIZE = 524288;
	const FETCH_UNIQUE = 196608;
	const NULL_EMPTY_STRING = 1;
	const NULL_NATURAL = 0;
	const NULL_TO_STRING = 2;
	const PARAM_BOOL = 5;
	const PARAM_INPUT_OUTPUT = -2147483648;
	const PARAM_INT = 1;
	const PARAM_LOB = 3;
	const PARAM_NULL = 0;
	const PARAM_STMT = 4;
	const PARAM_STR = 2;

	/**
	 * default db adapter for global use
	 *
	 * @var Yan_Db_Adapter
	 */
	protected static $_defaultDb = null;

	/**
	 * the last db adapter from factory
	 *
	 * @var Yan_Db_Adapter
	 */
	protected static $_lastDb = null;

	/**
	 * db meta data cache adapter
	 *
	 * @var Yan_Cache_Abstract
	 */
	protected static $_cacheAdapter = null;

	/**
	 * Factory for Yan_Db_Adapter classes.
	 *
	 * @param string $adapter
	 * @param array  $config
	 *
	 * @throws Yan_Db_Exception
	 * @return Yan_Db_Adapter
	 */
	public static function factory($adapter, $config = array())
	{
		/*
		 * Verify that an adapter name has been specified.
		 */
		if (!is_string($adapter) || empty($adapter)) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception('Adapter name must be specified in a string');
		}

		/*
		 * Convert object argument to array
		 */
		if (is_object($config)) {
			if (method_exists($config, 'toArray')) {
				$config = $config->toArray();
			} else {
				$config = (array)$config;
			}
		}
		/*
		 * Verify that adapter parameters are in an array.
		 */
		if (!is_array($config)) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception('Adapter parameters must be in an array or a array object');
		}
		/*
		 * Form full adapter class name
		 */
		$adapterNamespace = 'Yan_Db_Adapter';
		if (isset($config['adapterNamespace'])) {
			if ($config['adapterNamespace'] != '') {
				$adapterNamespace = $config['adapterNamespace'];
			}
			unset($config['adapterNamespace']);
		}

		$adapterName = Yan::loadClass($adapterNamespace . '_' . strtolower($adapter));

		/*
		 * Create an instance of the adapter class.
		 * Pass the config to the adapter class constructor.
		 */
		$dbAdapter = new $adapterName($config);

		/*
		 * Verify that the object created is a descendent of the abstract adapter type.
		 */
		if (!$dbAdapter instanceof Yan_Db_Adapter) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception("Adapter class '$adapterName' does not extend Yan_Db_Adapter");
		}

		self::$_lastDb = $dbAdapter;

		return $dbAdapter;
	}

	/**
	 * set default db adapter
	 *
	 * @param Yan_Db_Adapter|string $db
	 *
	 * @return Yan_Db_Adapter
	 * @throws Yan_Db_Exception
	 */
	public static function setDefaultAdapter($db = null)
	{
		if ($db === null) {
			return null;
		}
		if (is_string($db)) {
			$db = Yan::get($db);
		}
		if (!$db instanceof Yan_Db_Adapter) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception(
				'Argument must be of type Yan_Db_Adapter, or a Registry key where a Yan_Db_Adapter object is stored'
			);
		}
		return self::$_defaultDb = $db;
	}

	/**
	 * get the db from default set or the last factory
	 *
	 * @return Yan_Db_Adapter
	 */
	public static function getDefaultAdapter()
	{
		return self::$_defaultDb
			? self::$_defaultDb
			: self::$_lastDb;
	}

	/**
	 * set default cache adapter
	 *
	 * @param Yan_Cache_Abstract|string $cache
	 *
	 * @return Yan_Cache_Abstract
	 * @throws Yan_Db_Exception
	 */
	public static function setCacheAdapter($cache = null)
	{
		if ($cache === null) {
			return null;
		}
		if (is_string($cache)) {
			$cache = Yan::get($cache);
		}
		if (!$cache instanceof Yan_Cache_Abstract) {
			require_once 'Yan/Db/Exception.php';
			throw new Yan_Db_Exception(
				'Argument must be of type Yan_Cache_Abstract, or a Registry key where a Yan_Cache_Abstract object is stored'
			);
		}
		return self::$_cacheAdapter = $cache;
	}

	/**
	 * set db meta data cache adapter
	 *
	 * @return Yan_Cache_Abstract
	 */
	public static function getCacheAdapter()
	{
		return self::$_cacheAdapter;
	}
}