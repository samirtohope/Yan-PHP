<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

/**
 * Yan_Router
 *
 * @category  Yan
 * @package   Yan_Router
 */
class Yan_Router
{

	/**
	 * array rules of routes
	 *
	 * @var array
	 */
	protected $_routes = array();

	/**
	 * add a single route of type Yan_Router
	 *
	 * @param string $name
	 * @param Yan_Route_Interface|array $route
	 * @return Yan_Router fluent interface
	 */
	public function addRoute($name, $route = null)
	{
		if (is_array($name)) {
			foreach ($name as $key => $route) {
				$this->addRoute($key, $route);
			}
		} elseif ($route instanceof Yan_Route_Interface) {
			$this->_routes[] = $route;
		} else {
			$class = isset($route['type']) ? $route['type'] : 'Yan_Route_Route';
			Yan::loadClass($class);
			$this->_routes[] = new $class($route);
		}
		return $this;
	}

	/**
	 * routing... match request(Yan_Request_Abstract) to all routes, parsing and get param data
	 *
	 * @param Yan_Request_Abstract $request
	 * @return void
	 */
	public function route(Yan_Request_Abstract $request)
	{
		foreach (array_reverse($this->_routes) as $route) {
			if (($params = $route->match($request)) != false) {
				$request->setParams($params);
				break;
			}
		}
	}
}
