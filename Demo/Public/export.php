<?php
define('ROOT_PATH', rtrim(str_replace('\\', '/', dirname(dirname(__FILE__))), '/'));
define('LIB_PATH', rtrim(str_replace('\\', '/', dirname(ROOT_PATH)), '/') . '/Lib');


function request($url, $post = null)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 35);
	curl_setopt($ch, CURLOPT_TIMEOUT, 40);
	if ($post) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$ret = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
}

$apps = array(
	array('id' => 1, 'app' => 'System', 'name' => '系统'),
	array('id' => 2, 'app' => 'Twitter', 'name' => 'Twitter'),
	array('id' => 3, 'app' => 'Start', 'name' => '快速开始')
);
foreach ($apps as $item) {
	request('http://new.dev/parseaca.php', $item);
}
