<?php

include_once('../../../wp-config.php');
include_once('../../../wp-includes/wp-db.php');

global $wpdb;

$input = $_GET["q"]; 
$data = array(); 
$query = mysql_query("SELECT * FROM ".$wpdb->prefix."users WHERE display_name LIKE '%$input%'");   
while ($row = mysql_fetch_assoc($query)) {    
	$json = array(); 
	$json['value'] = $row['ID']; 
	$json['name'] = $row['display_name']; 
	$json['image'] = ''; 
	$data[] = $json; 
} 
//header("Content-type: application/json"); 
//echo json_encode($data);
echo "XXX";

?>
