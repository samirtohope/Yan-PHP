<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2009-2012 Kakalong CHINA (http://yanbingbing.com)
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
	 * set Script file
	 *
	 * @param string $path
	 *
	 * @return Yan_View_Abstract|Yan_View_Simple
	 */
	public function setScript($path)
	{
		$this->_scriptPath = $path;
		return $this;
	}

	/**
	 * set Compiled template file base directory
	 *
	 * @param string $dir
	 *
	 * @return Yan_View_Abstract|Yan_View_Simple
	 */
	public function setCompiledBase($dir)
	{
		return $this;
	}

	/**
	 * set Template file base directory
	 *
	 * @param string $dir
	 *
	 * @return Yan_View_Abstract|Yan_View_Simple
	 */
	public function setTemplateBase($dir)
	{
		$this->_viewBase = $dir;
		return $this;
	}

	/**
	 * assign variables, render template
	 *
	 * @throws Yan_View_Exception
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
