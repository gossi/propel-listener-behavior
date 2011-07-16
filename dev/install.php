<?php
include 'bootstrap.php';

$t = new Table();
$t->addListener('genericCallback');

$t->addListener(array(
	'callback' => 'insertCallback',
	'event' => 'postInsert'
));

// $t->removeListener(array(
// 	'callback' => 'genericCallback',
// 	'event' => 'postDelete'
// ));

$t->addListener(array(
	'callback' => 'updateCallback',
	'event' => 'postUpdate'
));

$t->addListener(array(
	'callback' => 'deleteCallback',
	'event' => 'postDelete'
));

$t->addListener(array(
	'on' => 'MyListener'
));

// anonymous functions don't work
// $t->addListener(function ($e) {
// 	printf('Callback on Anonymous function');
// });

header('Location: http://'.$_SERVER['HTTP_HOST'].''.dirname($_SERVER['PHP_SELF']).'/index.php');
?>