<?php
return array(
	'php' => array(
		'date.timezone' => "UTC"
	),
	'includePaths' => array(
		'%APP_PATH%/Controller',
		'%APP_PATH%/Model',
		'%APP_PATH%/Plugin',
		'%ROOT_PATH%/Shared'
	),
	'log' => array(
		'Yan_Log_Writer_FireBug'
	),
	'controllerSuffix'=> 'Controller',
	'controllerKey' => 'c',
	'actionKey' => 'a',
	'db' => array(
		'adapter' => 'Mysql',
		'params'  => include ROOT_PATH.'/Config/Database.php',
		'cache' => array(
			'adapter' => 'Array',
			'options' => array(
				'duration' => 0,
				'cacheDir' => ROOT_PATH.'/Data/Cache/Db',
				'dirDepth' => 0
			)
		)
	),
	'session' => array(
		'options' => array(
			'save_path' => ROOT_PATH.'/Data/Session'
		)
	)
);