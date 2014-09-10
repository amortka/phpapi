<?php
set_time_limit (0) ;
ini_set('memory_limit', '-1');

header('Access-Control-Allow-Origin: *');
require_once('myapi.class.php');

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	$response = $API->processAPI();

    echo $response;
	
} catch (Exception $e) {
   echo json_encode(Array('error' => $e->getMessage()));
}

?>
