<?php
return array(
	array(
		'rule' => '/:c/*',
		'type' => 'Yan_Route_Route',
		'defaults' => array(
			'a' => 'index'
		),
		'reqs' => array('c' => '\w+')
	),
	'normal' => array(
		'rule' => '/:c/:a/*',
		'type' => 'Yan_Route_Route'
	)
);