<?php
/**
 * Yan Framework
 *
 * @copyright Copyright (c) 2011-2012 kakalong (http://yanbingbing.com)
 */

require_once 'Yan/Config.php';

require_once 'Helper/Yaml/sfYamlParser.class.php';

require_once 'Helper/Yaml/sfYamlInline.class.php';

/**
 * Yan_Config_Yaml
 *
 * @category  Yan
 * @package   Yan_Config
 */
class Yan_Config_Yaml extends Yan_Config
{
	/**
	 * yaml file parse engine
	 *
	 * @var sfYamlParser
	 */
	protected static $_parser = null;

	public function __construct($file = null)
	{
		$this->merge(self::parse($file));
	}

	public static function parse($filename)
	{
		try {
			$yamlString = file_get_contents($filename);
		} catch (Exception $e) {
			require_once 'Yan/Config/Exception.php';
			throw new Yan_Config_Exception("Cannot load Yaml file '$filename'");
		}

		if (self::$_parser == null) {
			self::$_parser = new sfYamlParser();
		}

		try {
			$yamlArray = self::$_parser->parse($yamlString);
		} catch (Exception $e) {
			require_once 'Yan/Config/Exception.php';
			throw new Yan_Config_Exception($e->getMessage());
		}

		return $yamlArray;
	}
}