<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Interface.php 15 2012-04-23 11:33:00Z kakalong $
 */

/**
 * Yan_Route_Interface
 *
 * @category  Yan
 * @package   Yan_Route
 */
interface Yan_Route_Interface
{
	const URI_DELI = '/';

	const REGEX_DELI = '#';

	const URL_KEY = ':';

	/**
	 * match request(Yan_Request_Abstract) to this route
	 *
	 * @param Yan_Request_Abstract $request
	 * @return bool
	 */
	public function match(Yan_Request_Abstract $request);

	/**
	 * constructor
	 *
	 * @param array $config
	 * @return void
	 */
	public function __construct(array $config);
}