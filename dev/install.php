<?php
include 'bootstrap.php';

Table::addGlobalListener('genericCallback');

Table::addGlobalListener(array(
	'callback' => 'insertCallback',
	'event' => 'postInsert'
));

// $t->removeListener(array(
// 	'callback' => 'genericCallback',
// 	'event' => 'postDelete'
// ));

Table::addGlobalListener(array(
	'callback' => 'updateCallback',
	'event' => 'postUpdate'
));

Table::addGlobalListener(array(
	'callback' => 'deleteCallback',
	'event' => 'postDelete'
));

Table::addGlobalListener(array(
	'on' => 'MyListener'
));

// anonymous functions don't work
// $t->addListener(function ($e) {
// 	printf('Callback on Anonymous function');
// });

header('Location: http://'.$_SERVER['HTTP_HOST'].''.dirname($_SERVER['PHP_SELF']).'/index.php');
?>