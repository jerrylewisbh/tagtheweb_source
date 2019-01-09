<?php

header('Content-type:application/json;charset=utf-8');

include('Html2Text.php');

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if(!isset($_GET['url'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$url_param =  $_GET['url'];
		break;

	case 'POST':
		if(!isset($_POST['url'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$url_param = $_POST['url'];
		break;
	
	default:
		http_response_code(405);
		echo 'Method not allowed';
		die();
		break;
}




if (filter_var($url_param, FILTER_VALIDATE_URL)) { 
	
$content = file_get_contents($url_param);
  $html = new \Html2Text\Html2Text($content);
  echo $html->getText(); 
}

 
?>

