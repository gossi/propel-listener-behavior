<?php
include_once 'bootstrap.php';

head();

echo '<h2>Update Record Listener</h2>';

$t = TableQuery::create()->findOneById(OtherTableQuery::create()->findOneByChecked(false)->getOther());

if ($t) {
	$t->setName(md5(rand()));
	$t->save();
} else {
	echo 'Nothing to update';
}

foot();
?>
