<?php

$action = $_POST['action'];

if ($action == "symposium_test_ajax") {

	$value = $_POST['postID'];	
	echo $value*100;
	exit;
}

echo "Incorrect call to test AJAX functions (".$action.")";

?>
