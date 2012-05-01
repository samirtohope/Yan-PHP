<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: SaveHandler.php 15 2012-04-23 11:33:00Z kakalong $
 */

/**
 * Yan_Session_SaveHandler
 *
 * @category   Yan
 * @package    Yan_Session
 * @subpackage SaveHandler
 */
interface Yan_Session_SaveHandler
{
	/**
	 * Open Session - retrieve resources
	 *
	 * @param string $save_path
	 * @param string $name
	 */
	public function open($save_path, $name);

	/**
	 * Close Session - free resources
	 *
	 */
	public function close();

	/**
	 * Read session data
	 *
	 * @param string $id
	 */
	public function read($id);

	/**
	 * Write Session - commit data to resource
	 *
	 * @param string $id
	 * @param mixed $data
	 */
	public function write($id, $data);

	/**
	 * Destroy Session - remove data from resource for
	 * given session id
	 *
	 * @param string $id
	 */
	public function destroy($id);

	/**
	 * Garbage Collection - remove old session data older
	 * than $maxlifetime (in seconds)
	 *
	 * @param int $maxlifetime
	 */
	public function gc($maxlifetime);
}