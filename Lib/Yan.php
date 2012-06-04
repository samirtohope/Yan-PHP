<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/*  remember framework start time  */
define('START_MICROTIME', microtime(true));

/*  current unix timestamp  */
define('START_TIME', isset($_SERVER['REQUEST_TIME']) ? (int)$_SERVER['REQUEST_TIME'] : time());

/* compatible 5.3 below */
defined('E_DEPRECATED') or define('E_DEPRECATED', 8192);

/**
 * dump a var
 *
 * @param mixed $var
 * @param null $label
 * @param bool $return use return instead output
 * @return string
 */
function dump($var, $label = null, $return = false)
{
	// format the label
	$label = ($label === null) ? '' : rtrim($label) . ' ';

	$output = print_r($var, true);

	if (PHP_SAPI == 'cli') {
		$output = PHP_EOL . $label
			. PHP_EOL . $output
			. PHP_EOL;
	} else {
		$output = htmlspecialchars($output, ENT_QUOTES);
		$output = '<pre>'
			. $label
			. $output
			. '</pre>';
	}

	if ($return) {
		return $output;
	}
	echo $output;
}

/**
 * use Firephp dump message to firebug
 *
 * @param mixed $message
 */
function console($message)
{
	static $fb = null;
	if ($fb == null) {
		require_once 'Helper/FirePHP/FirePHP.class.php';
		$fb = FirePHP::getInstance(true);
	}
	$fb->info($message);
}

function excerpt($file, $line)
{
	if (!(file_exists($file) && is_file($file))) {
		return array();
	}
	$data = file($file);
	$start = $line - 10;
	if ($start < 0) {
		$start = 0;
	}
	$rv = array_slice($data, $start, 21);
	$rk = range($start + 1, $start + count($rv));
	return array_combine($rk, $rv);
}

/**
 * A quick function to get a Yan_Table Object
 *
 * @param string|array $table
 * @return Yan_Table
 */
function T($table)
{
	return Yan_Table::factory($table);
}

/**
 * A quick function to make a Yan_Db_Expr
 *
 * @param string $expr
 * @return Yan_Db_Expr
 */
function E($expr)
{
	return new Yan_Db_Expr($expr);
}

/**
 * Yan
 *
 * @category  Yan
 */
abstract class Yan
{
	/**
	 * 储存注册对象
	 */
	protected static $_registry = array();

	/**
	 * 载入class
	 *
	 * @param  $name String
	 * @return string
	 */
	public static function loadClass($name, $dirs = null)
	{
		$name = ucwords(str_replace('_', ' ', $name));
		$name = str_replace(' ', '_', $name);
		$file = str_replace('_', '/', $name) . '.php';
		if (class_exists($name, false) || interface_exists($name, false)) {
			return $name;
		}
		if (!empty($dirs) && (is_array($dirs) || is_string($dirs))) {
			if (is_array($dirs)) {
				$dirs = implode(PATH_SEPARATOR, $dirs);
			}
			$orig_inc_path = get_include_path();
			set_include_path($dirs . PATH_SEPARATOR . $orig_inc_path);
			include_once $file;
			set_include_path($orig_inc_path);
		} else {
			include_once $file;
		}
		if (!class_exists($name, false) && !interface_exists($name, false)) {
			require_once 'Yan/Exception.php';
			throw new Yan_Exception(
				"File '$file' does not exist or class '$name' was not found in the file"
			);
		}
		return $name;
	}

	/**
	 * set a global value
	 *
	 * @param $index
	 * @param $value
	 */
	public static function set($index, $value)
	{
		self::$_registry[$index] = $value;
	}

	/**
	 * get a global value
	 *
	 * @param $index
	 * @return mixed
	 * @throws Yan_Exception
	 */
	public static function get($index)
	{
		if (!array_key_exists($index, self::$_registry)) {
			require_once 'Yan/Exception.php';
			throw new Yan_Exception("No entry is registered for key '$index'");
		}
		return self::$_registry[$index];
	}

	/**
	 * check a global value exists
	 *
	 * @param $index
	 * @return bool
	 */
	public static function exists($index)
	{
		return array_key_exists($index, self::$_registry);
	}

	public static function init()
	{
		/* register autoloader */
		spl_autoload_register(array('Yan', '__autoload'));

		/* set error handler */
		set_error_handler(array('Yan', '__error_handler'));
		set_exception_handler(array('Yan', '__exception_handler'));

		/* add to include_path */
		set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path());
	}

	protected static function __autoload($name)
	{
		try {
			$name = ucwords(str_replace('_', ' ', $name));
			$name = str_replace(' ', '_', $name);
			include_once str_replace('_', '/', $name) . '.php';
		} catch (Exception $e) {
		}
	}

	public static function __exception_handler($e)
	{
		$error = get_class($e);
		$errstr = $e->getMessage();
		$errno = $e->getCode();
		$errfile = $e->getFile();
		$errline = $e->getLine();
		$trace = $e->getTrace();
		if ($e instanceof ErrorException) {
			array_slice($trace, 0, 2);
		}
		$errinfo = "Exception '{$error}'";
		if ($errstr != '') {
			$errinfo .= " with message '{$errstr}'";
		}
		$errinfo .= " in {$errfile}:{$errline}";
		$ix = count($trace);
		foreach ($trace as &$point) {
			$point['function'] = isset($point['class']) ? "{$point['class']}::{$point['function']}" : $point['function'];
			$argd = array();
			if (!isset($point['args'])) {
				$point['args'] = array();
			}
			if (is_array($point['args']) && count($point['args']) > 0) {
				foreach ($point['args'] as $arg) {
					switch (gettype($arg)) {
					case 'array':
						$argd[] = 'array(' . count($arg) . ')';
						break;
					case 'resource':
						$argd[] = gettype($arg);
						break;
					case 'object':
						$argd[] = get_class($arg);
						break;
					case 'string':
						if (strlen($arg) > 30) {
							$arg = substr($arg, 0, 27) . ' ...';
						}
						$argd[] = "'{$arg}'";
						break;
					default:
						$argd[] = $arg;
					}
				}
			}
			$point['argd'] = $argd;
			$point['index'] = $ix--;
		}
		try {
			require_once 'Yan/Log.php';
			Yan_Log::log("{$errstr} in {$errfile}:{$errline}", Yan_Log::ERROR);
		} catch (Exception $e) {
		}
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		require_once 'Yan/Assets/exception.phtml';
		exit;
	}

	public static function __error_handler($errno, $errstr, $errfile, $errline)
	{
		switch ($errno) {
		case E_NOTICE:
		case E_STRICT:
		case E_DEPRECATED:
			try {
				require_once 'Yan/Log.php';
				Yan_Log::log("{$errstr} in {$errfile}:{$errline}", Yan_Log::NOTICE);
			} catch (Exception $e) {
			}
			return;
		}
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}

/* init the framework */
Yan::init();