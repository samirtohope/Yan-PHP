<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

require_once 'Yan/Controller.php';

/**
 * Yan_Application
 *
 * @category  Yan
 * @package   Yan_Application
 */
class Yan_Application
{
	/**
	 * controller suffix
	 *
	 * @var string
	 */
	protected $_controllerSuffix = '';

	/**
	 * Controller key for retrieving controller from request
	 * @var string
	 */
	protected $_controllerKey = 'controller';

	/**
	 * Action key for retrieving action from request
	 * @var string
	 */
	protected $_actionKey = 'action';

	/**
	 * Default action
	 * @var string
	 */
	protected $_defaultAction = 'index';

	/**
	 * Default controller
	 * @var string
	 */
	protected $_defaultController = 'Index';

	/**
	 * Default Request
	 *
	 * @var Yan_Request_Http
	 */
	protected $_request = null;

	/**
	 * Default Response
	 *
	 * @var Yan_Response_Http
	 */
	protected $_response = null;

	/**
	 * The router
	 *
	 * @var Yan_Router
	 */
	protected $_router = null;

	/**
	 * constructor
	 *
	 * @param mixed $options array or Yan_Config
	 * @return void
	 */
	public function __construct($options = null)
	{
		if ($options) {
			require_once 'Yan/Config.php';
			if (is_string($options)) {
				$cfg = new Yan_Config($options);
				$options = $cfg->toArray();
			} elseif ($options instanceof Yan_Config) {
				$options = $options->toArray();
			}
			$this->setOptions($options);
		}
	}

	/**
	 * use ini_set to change php runtime settings
	 *
	 * @param array $settings
	 * @param string $prefix
	 * @return Yan_Application
	 */
	public function setPhp(array $settings, $prefix = '')
	{
		foreach ($settings as $key => $value) {
			$key = empty($prefix) ? $key : $prefix . $key;
			if (is_scalar($value)) {
				ini_set($key, $value);
			} elseif (is_array($value)) {
				$this->setPhp($value, $key . '.');
			}
		}

		return $this;
	}

	/**
	 * set include_path for include or require function
	 *
	 * @param string|array $paths 'path;path' or array('path','path/to/dir')
	 * @return Yan_Application
	 */
	public function setIncludePaths($paths)
	{
		if (is_string($paths)) {
			$paths = explode(PATH_SEPARATOR, $paths);
		}
		if (!is_array($paths) || empty($paths)) {
			return $this;
		}
		$paths = array_merge($paths, explode(PATH_SEPARATOR, get_include_path()));
		foreach ($paths as &$path) {
			$path = rtrim(str_replace('\\', '/', $path), '/ ');
		}
		set_include_path(implode(PATH_SEPARATOR, array_unique($paths)));
		return $this;
	}

	/**
	 * set exception_handler
	 *
	 * @param string|array $handler like 'function' or array('object','method')
	 * @return Yan_Application
	 */
	public function setExceptionHandler($handler)
	{
		set_exception_handler($handler);
		return $this;
	}

	/**
	 * set Log writers for logging
	 *
	 * @param array $writers
	 *
	 * @throws Yan_Application_Exception
	 * @return Yan_Application
	 */
	public function setLog(array $writers)
	{
		require_once 'Yan/Log.php';
		$Log = Yan_Log::getInstance();
		foreach ($writers as $writer) {
			if (is_string($writer)) {
				$writer = Yan::loadClass($writer);
				$writer = new $writer();
			} elseif (is_array($writer)) {
				if (!array_key_exists('class', $writer)) {
					require_once 'Yan/Application/Exception.php';
					throw new Yan_Application_Exception('Log writer class not provided in options');
				}
				$class = Yan::loadClass($writer['class']);
				$args = array_key_exists('args', $writer) ? $writer['args'] : null;
				if (empty($args)) {
					$writer = new $class();
				} else {
					if (is_array($args) && (array_keys($args) === range(0, count($args) - 1))) {
						$rf = new ReflectionClass($class);
						$writer = $rf->newInstanceArgs($args);
					} else {
						$writer = new $class($args);
					}
				}
			}
			$Log->addWriter($writer);
		}
		return $this;
	}

	/**
	 * set Db settings
	 *
	 * @param array $options
	 * @return Yan_Application
	 */
	public function setDb(array $options)
	{
		if (!empty($options['adapter'])) {
			$db = Yan_Db::factory($options['adapter'], $options['params']);
			Yan_Db::setDefaultAdapter($db);
		}
		if (!empty($options['cache'])) {
			$cache = Yan_Cache::factory($options['cache']['adapter'], $options['cache']['options']);
			Yan_Db::setCacheAdapter($cache);
		}
		return $this;
	}

	/**
	 * set Session settings
	 *
	 * @param array $options
	 *
	 * @throws Yan_Application_Exception
	 * @return Yan_Application
	 */
	public function setSession(array $options)
	{
		if (!empty($options['saveHandler'])) {
			$saveHandler = $options['saveHandler'];
			if (is_array($saveHandler)) {
				if (!array_key_exists('class', $saveHandler)) {
					require_once 'Yan/Application/Exception.php';
					throw new Yan_Application_Exception('Session save handler class not provided in options');
				}
				$config = array_key_exists('options', $saveHandler) ? $saveHandler['options'] : null;
				$saveHandler = $saveHandler['class'];
				Yan::loadClass($saveHandler);
				$saveHandler = new $saveHandler($config);
			} elseif (is_string($saveHandler)) {
				Yan::loadClass($saveHandler);
				$saveHandler = new $saveHandler();
			}

			if (!$saveHandler instanceof Yan_Session_SaveHandler) {
				require_once 'Yan/Application/Exception.php';
				throw new Yan_Application_Exception('Invalid session save handler');
			}
		}
		if (!empty($options['options'])) {
			require_once 'Yan/Session.php';
			Yan_Session::setOptions($options['options']);
		}
		return $this;
	}

	/**
	 * set controller suffix
	 *
	 * @param string $suffix
	 *
	 * @throws Yan_Application_Exception
	 */
	public function setControllerSuffix($suffix)
	{
		if (preg_match('/[\W_]/', $suffix)) {
			require_once 'Yan/Application/Exception.php';
			throw new Yan_Application_Exception("controller suffix '$suffix' is not allowed");
		}
		$this->_controllerSuffix = ucfirst($suffix);
	}

	/**
	 * set ControllerKey
	 *
	 * @param string $key
	 * @return Yan_Application
	 */
	public function setControllerKey($key)
	{
		$this->_controllerKey = (string)$key;
		return $this;
	}

	/**
	 * set ActionKey
	 *
	 * @param string $key
	 * @return Yan_Application
	 */
	public function setActionKey($key)
	{
		$this->_actionKey = (string)$key;
		return $this;
	}

	/**
	 * set DefaultController
	 *
	 * @param string $value
	 * @return Yan_Application
	 */
	public function setDefaultController($value)
	{
		$this->_defaultController = $value;
		return $this;
	}

	/**
	 * set DefaultAction
	 *
	 * @param string $value
	 * @return Yan_Application
	 */
	public function setDefaultAction($value)
	{
		$this->_defaultAction = $value;
		return $this;
	}

	/**
	 * set Yan_Request_Abstract object
	 *
	 * @param string|Yan_Request_Abstract $request
	 * @return Yan_Application
	 * @throws Yan_Application_Exception
	 */
	public function setRequest($request)
	{
		if (is_string($request)) {
			Yan::loadClass($request);
			$request = new $request();
		}
		if (!$request instanceof Yan_Request_Abstract) {
			require_once 'Yan/Application/Exception.php';
			throw new Yan_Application_Exception('Invalid response class');
		}

		$this->_request = $request;

		return $this;
	}

	/**
	 * retrieve Yan_Request_Abstract object
	 *
	 * @return Yan_Request_Abstract
	 */
	public function getRequest()
	{
		if (null == $this->_request) {
			require_once 'Yan/Request/Http.php';
			$this->_request = new Yan_Request_Http();
		}
		$this->_request->setControllerKey($this->_controllerKey);
		$this->_request->setActionKey($this->_actionKey);
		return $this->_request;
	}

	/**
	 * set Yan_Response_Abstract object
	 *
	 * @param string|Yan_Response_Abstract $response
	 * @return Yan_Application
	 * @throws Yan_Application_Exception
	 */
	public function setResponse($response)
	{
		if (is_string($response)) {
			Yan::loadClass($response);
			$response = new $response();
		}
		if (!$response instanceof Yan_Response_Abstract) {
			require_once 'Yan/Application/Exception.php';
			throw new Yan_Application_Exception('Invalid response class');
		}

		$this->_response = $response;

		return $this;
	}

	/**
	 * retrieve Yan_Response_Abstract object
	 *
	 * @return Yan_Response_Abstract
	 */
	public function getResponse()
	{
		if (null == $this->_response) {
			require_once 'Yan/Response/Http.php';
			$this->_response = new Yan_Response_Http();
		}
		return $this->_response;
	}

	/**
	 * set Yan_Router object
	 *
	 * @param Yan_Router $router
	 * @return Yan_Application
	 */
	public function setRouter(Yan_Router $router)
	{
		$this->_router = $router;
		return $this;
	}

	/**
	 * retrieve Yan_Router object
	 *
	 * @return null|Yan_Router
	 */
	public function getRouter()
	{
		return $this->_router;
	}

	/**
	 * retrieve controller name
	 *
	 * @return string
	 */
	public function getControllerName()
	{
		$controller = $this->_request->getControllerName();
		if (!$controller && $this->_defaultController) {
			$controller = $this->_defaultController;
		}

		$controller = preg_replace('/[^a-z0-9 ]/i', '', str_replace('_', ' ', $controller));
		$controller = str_replace(' ', '_', ucwords($controller));
		$this->_request->setControllerName($controller);
		return $controller;
	}

	/**
	 * retrieve action name
	 *
	 * @return string
	 */
	public function getActionName()
	{
		$action = $this->_request->getActionName();
		if (!$action && $this->_defaultAction) {
			$action = $this->_defaultAction;
		}
		$action = strtolower($action);
		$this->_request->setActionName($action);
		return $action;
	}

	/**
	 * set all Options
	 *
	 * @param array $options
	 * @return Yan_Application
	 */
	public function setOptions(array $options)
	{
		$optional = array(
			'php', 'includePaths', 'exceptionHandler', 'log',
			'db', 'session', 'controllerSuffix', 'controllerKey',
			'actionKey', 'defaultController', 'defaultAction'
		);
		foreach ($optional as $key) {
			if (isset($options[$key])) {
				$this->{'set' . ucfirst($key)}($options[$key]);
			}
		}
		return $this;
	}

	/**
	 * run application
	 *
	 * @param null|Yan_Request_Abstract  $request
	 * @param null|Yan_Response_Abstract $response
	 *
	 * @throws Yan_Application_Exception
	 * @return void
	 */
	public function run(Yan_Request_Abstract $request = null, Yan_Response_Abstract $response = null)
	{
		/**
		 * Instantiate default request object (HTTP version) if none provided
		 */
		if (null !== $request) {
			$this->setRequest($request);
		}
		$request = $this->getRequest();

		/**
		 * Instantiate default response object (HTTP version) if none provided
		 */
		if (null !== $response) {
			$this->setResponse($response);
		}
		$response = $this->getResponse();

		/**
		 * Initialize router
		 */
		if ($router = $this->getRouter()) {
			$router->route($request);
		}


		$controllerName = $this->getControllerName();
		$actionName = $this->getActionName();

		$className = $controllerName . $this->_controllerSuffix;
		Yan::loadClass($className);
		// instance controller
		$controller = new $className($request, $response);
		if (!$controller instanceof Yan_Controller) {
			require_once 'Yan/Application/Exception.php';
			throw new Yan_Application_Exception(
				"Controller '$className' is not an instance of Yan_Controller"
			);
		}

		// dispatching
		$controller->_dispatch($actionName);

		// send response to client
		$response->send();
	}
}