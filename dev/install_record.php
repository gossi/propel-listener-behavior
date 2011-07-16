<?php
include 'bootstrap.php';

$t = new Table();
$t->setName('Unchanged Record');
$t->save();

$o = new OtherTable();
$o->setOther($t->getId());
$o->save();

$t->addListener(new RecordListener(array(
	'event' => 'postUpdate',
	'target' => 'OtherTable',
	'find' => 'findOneByOther',
	'param' => $t->getId()
)));

// print_r($_SERVER);

header('Location: http://'.$_SERVER['HTTP_HOST'].''.dirname($_SERVER['PHP_SELF']).'/index.php');
?>