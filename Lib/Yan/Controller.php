<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Controller.php 20 2012-04-28 05:55:14Z kakalong $
 */

/**
 * Yan_Controller
 *
 * @category  Yan
 * @package   Yan_Controller
 */
abstract class Yan_Controller
{
	const NONE = 0;

	/**
	 * suffix of template file
	 *
	 * @var string
	 */
	protected $_viewSuffix = 'phtml';

	/**
	 * Request object wrapping the request environment
	 *
	 * @var Yan_Request_Http
	 */
	protected $_request;

	/**
	 * Response object wrapping the response
	 *
	 * @var Yan_Response_Http
	 */
	protected $_response;

	/**
	 * Output object control output
	 *
	 * @var Yan_Output_Abstract
	 */
	protected $_output = null;

	/**
	 * View object to render template
	 *
	 * @var Yan_View_Abstract
	 */
	protected $_view = null;

	final public function __construct(Yan_Request_Abstract $request, Yan_Response_Abstract $response)
	{
		$this->_request = $request;
		$this->_response = $response;
		$this->_init();
	}

	/**
	 * init the controller
	 */
	protected function _init()
	{}

	/**
	 * Init a output object
	 *
	 * @param Yan_Output_Abstract|string $output
	 * @param array $options
	 * @return Yan_Output_Abstract
	 */
	protected function _setOutput($output, array $options = array())
	{
		if ($this->_output instanceof Yan_Output_Abstract)
		{
			return $this->_output->setOptions($options);
		}

		if ($output instanceof Yan_Output_Abstract)
		{
			$output->setOptions($options);
			return $this->_output = $output;
		}

		/*
		 * Verify that an adapter name has been specified.
		 */
		if (!is_string($output) || empty($output)) {
			require_once 'Yan/Controller/Exception.php';
			throw new Yan_Controller_Exception('Output name must be specified in a string');
		}

		/*
		 * Form full adapter class name
		 */
		$adapterNamespace = 'Yan_Output';
		if (isset($options['adapterNamespace'])) {
			if ($options['adapterNamespace'] != '') {
				$adapterNamespace = $options['adapterNamespace'];
			}
			unset($options['adapterNamespace']);
		}

		$adapterName = Yan::loadClass($adapterNamespace . '_' . strtolower($output));

		/*
		 * Create an instance of the adapter class.
		 * Pass the config to the adapter class constructor.
		 */
		$output = new $adapterName($this->_response, $options);

		/*
		 * Verify that the object created is a descendent of the abstract adapter type.
		 */
		if (! $output instanceof Yan_Output_Abstract) {
			require_once 'Yan/Controller/Exception.php';
			throw new Yan_Controller_Exception("Class '$adapterName' does not extend Yan_Output_Abstract");
		}

		return $this->_output = $output;
	}

	/**
	 * initilized the view object
	 *
	 * @param Yan_View_Abstract|string|array $view
	 */
	protected function _setView($view = null)
	{
		if (isset($this->_output) && !($this->_output instanceof Yan_Output_View))
		{
			require_once 'Yan/Controller/Exception.php';
			throw new Yan_Controller_Exception('Current type of Output not support Yan_View');
		}
		$options = array();
		if ($view instanceof Yan_View_Abstract) {
			$options['view'] = $view;
		} else {
			$options['view'] = array(
				'class'=>'Yan_View_Simple',
				'templateBase' => APP_PATH.'/View',
				'script' => $this->_getViewScript(
					$this->_request->getActionName(),
					$this->_request->getControllerName())
			);
			if (is_string($view)) {
				$options['view']['class'] = $view;
			} elseif (is_array($view)) {
				$options['view'] = array_merge($options['view'], $view);
			}
		}

		$this->_setOutput('view', $options);
		$this->_view = $this->_output->getView();
		$this->_view->assign('_INPUT', $this->_request);
	}

	/**
	 * get reference of view
	 *
	 * @return Yan_View_Abstract
	 */
	protected function _getView()
	{
		if (null == $this->_view) {
			$this->_setView();
		}
		return $this->_view;
	}

	/**
	 * assign variablea to view
	 *
	 * @param string|array $spec
	 * @param mixed $value
	 * @return Yan_View_Abstract
	 */
	protected function assign($spec, $value = null)
	{
		return $this->_getView()->assign($spec, $value);
	}

	/**
	 * display the view content
	 *
	 * @param string $name template name
	 * @param string $dir  directory of the template
	 */
	protected function display($name = null, $dir = null)
	{
		if (is_null($name)) {
			$name = $this->_request->getActionName();
		}
		if (is_null($dir)) {
			$dir = $this->_request->getControllerName();
		}
		$this->_getView()->setScript($this->_getViewScript($name, $dir));
	}

	/**
	 * check if has default view script
	 *
	 * @return bool
	 */
	protected function _hasDefaultView()
	{
		$script = $this->_getViewScript( $this->_request->getActionName(),
			$this->_request->getControllerName() );
		return is_file(APP_PATH . '/View/' . $script);
	}

	/**
	 * get view template file
	 *
	 * @param string $name
	 * @param string $dir
	 * @return string
	 */
	protected function _getViewScript($name, $dir)
	{
		$dir = preg_replace('/[^a-z0-9 ]/i', '',
			trim(str_replace(array('_', '/', '\\'), ' ', $dir)));
		$dir = str_replace(' ', '/', ucwords($dir));
		return $dir . '/' . strtolower($name) . '.' . $this->_viewSuffix;
	}

	/**
	 * transform action name map
	 *
	 * @param string $action
	 * @return string
	 */
	protected function _transformAction($action)
	{
		return $action;
	}

	/**
	 * For internal use, dispatch the action
	 *
	 * @param string $action
	 */
	public function _dispatch($action)
	{
		$action = $this->_transformAction($action);
		if (!method_exists($this, $action)) {
			require_once 'Yan/Controller/Exception.php';
			throw new Yan_Controller_Exception("Action '$action' not found.");
		} else {
			ob_start();
			try {
				$return = $this->$action();
			} catch (Exception $e) {
				ob_end_clean();
				throw $e;
			}

			$text = ob_get_clean();
			switch (true) {
			case $return === self::NONE:
				$this->_response->setBody('');
				break;
			case is_string($return):
				$this->_response->setBody($return);
				break;
			case is_array($return):
				$this->_response->setBody(json_encode($return));
				break;
			case $this->_output instanceof Yan_Output_Abstract:
				$this->_response->setBody($this->_output->getBody());
				break;
			case $return instanceof Yan_Output_Abstract:
				$this->_response->setBody($return->getBody());
				break;
			case $text !== '':
				$this->_response->setBody($text);
				break;
			case $this->_hasDefaultView():
				$this->_setView();
				$this->_response->setBody($this->_output->getBody());
				break;
			default:
				$this->_response->setBody('');
				break;
			}
		}
	}
}