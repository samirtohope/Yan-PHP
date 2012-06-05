<?php
return array(
	'default' => array(
		'rule' => '/:c/:a/*',
		'type' => 'Yan_Route_Route'
	),
	array(
		'rule' => '/:c/*',
		'type' => 'Yan_Route_Route',
		'defaults' => array(
			'a' => 'index'
		),
		'reqs' => array('c' => '\w+')
	)
);