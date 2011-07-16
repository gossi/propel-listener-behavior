<?php
error_reporting(E_ALL | E_STRICT);

require_once '../src/runtime/ListenerBehavior.php';
require_once '../lib/propel-1.6.1/runtime/lib/Propel.php';

Propel::init("../build/propel-conf.php");

set_include_path('../build' . PATH_SEPARATOR . get_include_path());

function head() {
	echo '<!doctype html>
<html>
<head>
	<title>Listeners</title>
</head>
<body>
	<header>
		<a href="."><h1>Listeners</h1></a>
	</header>
	<nav>
		<a href="install.php">Install Listeners</a>
		<a href="add.php">Add Record</a>
		<a href="update.php">Update Record</a>
		<a href="remove.php">Remove Record</a>
	</nav>
	<nav>
		<a href="install_record.php">Install Record Listeners</a>
		<a href="update_record.php">Update Record Listener</a>
	</nav>
	';
}

function foot() {
	echo '</body></html>';
}

function genericCallback ($e) {
	printf('Generic Callback Called on %s, type %s<br>', get_class($e['target']), $e['event']);
}

function insertCallback ($e) {
	printf('Insert Callback Called on %s, type %s<br>', get_class($e['target']), $e['event']);
}

function updateCallback ($e) {
	printf('Update Callback Called on %s, type %s<br>', get_class($e['target']), $e['event']);
}

function deleteCallback ($e) {
	printf('Delete Callback Called on %s, type %s<br>', get_class($e['target']), $e['event']);
}

class MyListener {
	public function handleEvent($e) {
		printf('HandleEvent on MyListener, type %s<br>', $e['event']);
	}
	
}
?>
