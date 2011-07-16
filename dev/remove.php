<?php
include_once 'bootstrap.php';

head();

echo '<h2>Delete Record</h2>';

$t = TableQuery::create()->findOneById(TableQuery::create()->count() - 1);

if ($t) {
	$t->delete();
} else {
	echo 'Nothing to delete';
}

foot();
?>
