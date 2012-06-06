<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
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
	 *
	 * @return array|bool
	 */
	public function match(Yan_Request_Abstract $request);

	/**
	 * constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config);
}