<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/View/Abstract.php';

/**
 * Yan_View_Twig
 *
 * @category  Yan
 * @package   Yan_View
 */
class Yan_View_Twig extends Yan_View_Abstract
{
	/**
	 * Twig Template Engine
	 *
	 * @var Twig_Environment
	 */
	protected $_engine = null;

	/**
	 * the template name
	 * @var string
	 */
	protected $_scriptPath = '';

	public function __construct($options = null)
	{
		try {
			Yan::loadClass('Twig_Environment');
		} catch (Exception $e) {
			throw new Yan_View_Exception('Twig Template Engine was not installed');
		}
		$this->_engine = new Twig_Environment();
		parent::__construct($options);
	}

	/**
	 * @see Yan_View_Abstract
	 * @return Twig_Environment
	 */
	public function getEngine()
	{
		return $this->_engine;
	}

	/**
	 * @see Yan_View_Abstract
	 * @return Yan_View_Twig
	 */
	public function setScript($path)
	{
		$this->_scriptPath = $path;
		return $this;
	}

	/**
	 * @see Yan_View_Abstract
	 * @return Yan_View_Twig
	 */
	public function setCompiledBase($dir)
	{
		$this->_engine->setCache($dir);
		return $this;
	}

	/**
	 * @see Yan_View_Abstract
	 */
	public function setTemplateBase($dir)
	{
		$this->_engine->setLoader(new Twig_Loader_Filesystem($dir));
		return $this;
	}

	/**
	 * assign variables, render template
	 *
	 * @return string
	 */
	public function render()
	{
		$template = $this->_engine->loadTemplate($this->_scriptPath);
		return $template->render($this->_varibles);
	}
}
