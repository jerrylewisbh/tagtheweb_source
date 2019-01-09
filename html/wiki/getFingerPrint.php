<?php
include('Html2Text.php');
header("Access-Control-Allow-Origin: *");
header('Content-type:application/json;charset=utf-8');
$text_param = [];
$language = 'en';
$normalize = true;
$unique = false;
$ignoreContainers = false;
$depth = 0;
switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
	case 'POST':
		if(!isset($_REQUEST['text'])){
			http_response_code(400);
			echo 'Missing parameter text';
			die();
		}
		if(!isset($_REQUEST['language'])){
			http_response_code(400);
			echo 'Missing parameter language';
			die();
		}
		$text_param = $_REQUEST['text'];
		$language =  $_REQUEST['language'];
		$normalize = isset($_REQUEST['normalize']) && $_REQUEST['normalize'] ==='false' ? false: true;
		$unique = isset($_REQUEST['unique']) && $_REQUEST['unique'] === 'true'? true: false;
		$ignoreContainers = isset($_REQUEST['ignoreContainers']) && $_REQUEST['ignoreContainers'] === 'true'? true: false;
		$depth = isset($_REQUEST['depth']) ? $_REQUEST['depth'] : 0;
		break;
	default:
		http_response_code(405);
		echo 'Method not allowed';
		die();
		break;
}


if (filter_var(trim($text_param), FILTER_VALIDATE_URL) !== false) {
$content = file_get_contents(trim ($text_param));
$html = new \Html2Text\Html2Text($content);
$text_param =  $html->getText();
}
$query = http_build_query([
'text' => mb_strtolower ($text_param),
'unique'=> $unique,
'language' => $language
]);


$entities =  request('http://localhost/wiki/anotateText.php?', $query);
if( count($entities) <=0){
	http_response_code(400);
	echo 'Impossible to anotate - ';
	die();
}

$entities = implode('|', $entities );
$query = http_build_query([
'entities' => $entities,
'unique'=> $unique
]);
$categories =  request('http://localhost/wiki/getCategories.php?', $query);
if( count($categories) <=0){
	http_response_code(400);
	echo 'Impossible to anotate';
	die();
}
$categories = implode('|', $categories );
//echo $categories;
//die();
$query = http_build_query([
'depth' => $depth,
'categories' => $categories,
'normalize' => $normalize,
'ignoreContainers'=>$ignoreContainers
]);
$finger_print =  request('http://localhost/wiki/generateFingerPrint.php?', $query);
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
