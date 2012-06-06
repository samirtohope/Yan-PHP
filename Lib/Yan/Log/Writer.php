<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
 */

/**
 * Yan_Log_Writer
 *
 * @category   Yan
 * @package    Yan_Log
 * @subpackage Writer
 */
abstract class Yan_Log_Writer
{
	/**
	 * runtime cached log array
	 *
	 * @var array
	 */
	protected $_log = array();

	/**
	 *  format a message and add to the log.
	 *
	 * @param  array  $event  log data event
	 *
	 * @return void
	 */
	public function append($event)
	{
	}

	/**
	 * write cached array to log file and shutdown
	 *
	 * @return void
	 */
	public function write()
	{
	}
}