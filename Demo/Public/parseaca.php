<?php
define('ROOT_PATH', rtrim(str_replace('\\', '/', dirname(dirname(__FILE__))), '/'));
define('LIB_PATH', rtrim(str_replace('\\', '/', dirname(ROOT_PATH)), '/') . '/Lib');

require_once LIB_PATH.'/Yan.php';
require_once ROOT_PATH.'/Shared/New/Admin/Controller.php';

function val($arr, $key, $default = '') {
	return empty($arr[$key]) ? $default : $arr[$key];
}
function parseDoc($doc) {
	$doc = str_replace('*', ' ', trim($doc, '/'));
	$arr = preg_split('/@(\w+)/', $doc, -1, PREG_SPLIT_DELIM_CAPTURE);
	$params = array();
	$params['description'] = trim(array_shift($arr));
	for ($i=0, $l=count($arr); $i<$l; $i++)
	{
		$params[$arr[$i++]] = isset($arr[$i]) ? trim($arr[$i]) : '';
	}
	return $params;
}
function goodArray($array) {
	return array_unique(array_map('strtolower',array_filter(array_map('trim', $array))));
}


class ParseAca {

	protected $_actions = array();
	protected $_accesses = array();
	protected $_belongs = array();
	protected $_appName;
	protected $_controllerDir;

	public function __construct($appid, $appName, $nickname) {
		$this->_appName = strtolower($appName);
		$appid = str_pad($appid, 3, '0', STR_PAD_LEFT);
		$id = $appid.'000000';
		$this->_actions[$id] = array($id, $nickname, '0', 'NULL');
		$this->_accesses[$id] = $this->_appName;
		$this->_controllerDir = ROOT_PATH.'/Apps/'.ucfirst($this->_appName).'/Controller';
		chdir($this->_controllerDir);
		$did = 1;
		foreach (glob('*Controller.php') as $f) {
			preg_match('/^((.*)Controller)\.php$/', $f, $m);
			if ($this->_parseClass($f, $m[1], $m[2], $appid . str_pad($did, 3, '0', STR_PAD_LEFT), $id))
			{
				$did++;
			}
		}
		foreach ($this->_actions as $val) {
			$sql[] = vsprintf("INSERT INTO `kakalong_action`(`actionid`,`name`,`public`,`parentid`) VALUES('%s', '%s', '%s', %s)", $val);
		}
		foreach ($this->_accesses as $key=>$val) {
			$sql[] = vsprintf("INSERT INTO `kakalong_action_has_access`(`actionid`,`access`) VALUES('%s','%s')", array($key, $val));
		}
		foreach ($this->_belongs as $key=>$val) {
			if (false !== ($id = array_search($key, $this->_accesses)))
			{
				foreach ($val as $v) {
					$sql[] = vsprintf("INSERT INTO `kakalong_action_has_access`(`actionid`,`access`) VALUES('%s','%s')", array($id, $v));
				}
			}
		}
		file_put_contents(ROOT_PATH.'/aca.sql', implode(";\n", $sql).";\n", FILE_APPEND);
	}

	protected function _parseClass($classFile, $controllerClass, $controllerName, $cid, $pid)
	{
		require_once $this->_controllerDir .'/'. $classFile;
		$rf = new ReflectionClass($controllerClass);
		if ($rf->isInstantiable() && $rf->isSubclassOf('New_Admin_Controller'))
		{
			$id = $cid . '000';
			$nickname = val($params, 'aca', val($params, 'description', $controllerName));
			$controllerName = strtolower($controllerName);
			$params = parseDoc($rf->getDocComment());
			$this->_actions[$id] = array($id, $nickname, '0', "'$pid'");
			$this->_accesses[$id] = "$this->_appName.$controllerName";
			$did = 1;
			foreach ($rf->getMethods() as $rm) {
				if ($this->_parseMethod($rm, $controllerName, $cid.str_pad($did, 3, '0', STR_PAD_LEFT), $id))
				{
					$did++;
				}
			}
			return true;
		}
	}
	protected function _parseMethod($method, $controllerName, $aid, $pid)
	{
		$name = $method->getName();
		$doc = $method->getDocComment();
		if (empty($doc) || $method->isStatic() || $method->isPrivate() || !preg_match('/^[a-z]/i', $name))
		{
			return;
		}
		$params = parseDoc($doc);
		$access = "$this->_appName.$controllerName.".strtolower($name);
		if (isset($params['aca'])) {
			$nickname = val($params, 'aca', val($params, 'description', $name));
			$public = $method->isPublic() ? '1' : '0';
			$this->_actions[$aid] = array($aid, $nickname, $public, "'$pid'");
			$this->_accesses[$aid] = $access;
			return true;
		}
		if (isset($params['belongs'])) {
			$belongs = goodArray(preg_split('/\s+/', $params['belongs']));
			foreach ($belongs as $item) {
				$path = explode('.', $item);
				$action = array_pop($path);
				$controller = array_pop($path);
				if (empty($controller)) {
					$controller = $controllerName;
				}
				$this->_belongs["$this->_appName.$controller.$action"][] = $access;
			}
		}
	}
}


new ParseAca($_POST['id'], $_POST['app'], $_POST['name']);