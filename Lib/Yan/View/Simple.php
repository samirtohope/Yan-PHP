<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 * @version   $Id: Simple.php 19 2012-04-28 02:42:04Z kakalong $
 */

require_once 'Yan/View/Abstract.php';

/**
 * Yan_View_Simple
 *
 * @category  Yan
 * @package   Yan_View
 */
class Yan_View_Simple extends Yan_View_Abstract
{
	/**
	 * where the original template placed
	 *
	 * @var string
	 */
	protected $_viewBase = '';

	/**
	 * the template script name
	 *
	 * @var string
	 */
	protected $_scriptPath = '';

	/**
	 * @see Yan_View_Abstract
	 * @return Yan_View_Simple
	 */
	public function setScript($path)
	{
		$this->_scriptPath = $path;
		return $this;
	}

	/**
	 * @see Yan_View_Abstract
	 * @return Yan_View_Simple
	 */
	public function setCompiledBase($dir)
	{
		return $this;
	}

	/**
	 * @see Yan_View_Abstract
	 * @return Yan_View_Simple
	 */
	public function setTemplateBase($dir)
	{
		$this->_viewBase = $dir;
		return $this;
	}

	/**
	 * assign variables, render template
	 *
	 * @return string
	 */
	public function render()
	{
		$scriptFile = $this->_viewBase . '/' . $this->_scriptPath;
		if (!is_file($scriptFile)) {
			require_once 'Yan/View/Exception.php';
			throw new Yan_View_Exception("View file '$this->_scriptPath' not found.");
		}
		// avoid find files in other paths
		$origCwd = getcwd();
		chdir($this->_viewBase);
		ob_start();
		try {
			extract($this->_variables);
			require $scriptFile;
			$content = ob_get_clean();
			chdir($origCwd);
		} catch (Exception $e) {
			ob_end_clean();
			chdir($origCwd);
			require_once 'Yan/View/Exception.php';
			throw new Yan_View_Exception($e->getMessage());
		}
		return $content;
	}
}
