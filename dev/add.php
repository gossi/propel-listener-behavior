<?php
include_once 'bootstrap.php';

head();

echo '<h2>Add Record</h2>';

$t = new Table();
$t->setName('Hans');
$t->save();


foot();
?>
