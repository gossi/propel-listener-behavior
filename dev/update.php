<?php
include_once 'bootstrap.php';

head();

echo '<h2>Update Record</h2>';

$t = TableQuery::create()->findOneById(TableQuery::create()->count() - 1);

if ($t) {
	$t->setName(md5(rand()));
	$t->save();
} else {
	echo 'Nothing to update';
}

foot();
?>
