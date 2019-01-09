<?php

include('Html2Text.php');
header("Access-Control-Allow-Origin: *");
header('Content-type:application/json;charset=utf-8');

$text_param = [];
$normalize = true;
$language = 'pt';

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if(!isset($_GET['text'])){
			http_response_code(400);
			echo 'Missing parameter text';
			die();
		}
		$text_param =  $_GET['text'];
		$normalize = isset($_GET['normalize']) ? $_GET['normalize']: true;
		break;

	case 'POST':
		if(!isset($_POST['text'])){
			http_response_code(400);
			echo 'Missing parameter text';
			die();
		}
		$text_param = $_POST['text'];
		$normalize = isset($_POST['normalize']) ? $_POST['normalize']: true;
		break;
	
	default:
		http_response_code(405);
		echo 'Method not allowed';
		die();
		break;
}


if(isset($_REQUEST['$language'])){
	$language = $_REQUEST['$language'];
}



if (filter_var(trim($text_param), FILTER_VALIDATE_URL) !== false) { 

  $content = file_get_contents(trim ($text_param));
  $html = new \Html2Text\Html2Text($content);
  $text_param =  $html->getText(); 
}




$query = http_build_query([
 'text' => mb_strtolower($text_param),
 'language' => $language 
]);



$entities =  request('http://localhost/wiki/anotateText.php?', $query);
if( count($entities) <=0){
	http_response_code(400);
	echo 'Impossible to anotate';
	die();
}




$entities = implode('|', $entities );




$query = http_build_query([
 'entities' => $entities
]);


$categories =  request('http://localhost/wiki/getCategories.php?', $query);
 



if( count($categories) <=0){
	http_response_code(400);
	echo 'Impossible to anotate';
	die();
}


$categories = implode('|', $categories );
 
$query = http_build_query([
 'categories' => $categories,
 'normalize' => $normalize
]);



$finger_print =  request('http://localhost/wiki/generatePaths.php?', $query);
echo json_encode($finger_print);

function request($url, $param){

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_USERAGENT, 'LibraryIndexing/1.1 jerry.medeiros@uniriotec.br)');
	curl_setopt($ch, CURLOPT_POSTFIELDS  , $param);

	$result = curl_exec($ch);
	$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
   	$header = substr($result, 0, $header_size);
 	$body = substr($result, $header_size);
	$result = json_decode($body);
	return $result;
}

?>
