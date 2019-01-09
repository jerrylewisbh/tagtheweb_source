<?php

header('Content-type:application/json;charset=utf-8');

$categories_param = [];
$unique = false;
$confidence = 0.35;
switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if(!isset($_GET['text'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$text_param =  $_GET['text'];
		$unique = isset($_GET['unique']) && $_GET['unique'] === 'true'? true: false;
		$confidence = isset($_GET['confidence']) ? $_GET['confidence']: 0.35;
		break;

	case 'POST':
		if(!isset($_POST['text'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$text_param = $_POST['text'];
		$unique = isset($_POST['unique']) && $_POST['unique'] === 'true'? true: false;
		$confidence = isset($_POST['confidence']) ? $_POST['confidence']: 0.35;
		break;
	
	default:
		http_response_code(405);
		echo 'Method not allowed';
		die();
		break;
}



$query = http_build_query([
 'text' => $text_param,
  'confidence'=> $confidence
]);


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://model.dbpedia-spotlight.org/en/annotate?');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json'));

curl_setopt($ch, CURLOPT_USERAGENT, 'LibraryIndexing/1.1 jerry.medeiros@uniriotec.br)');
curl_setopt($ch, CURLOPT_POSTFIELDS  , $query);

$result = curl_exec($ch);
$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);


$entities = [];

if($response == 200){
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($result, 0, $header_size);
    $body = substr($result, $header_size);
	$result = json_decode($body);
    $result = $result->Resources;
    foreach ($result as $key => $value){
    	$entityName = str_replace('http://dbpedia.org/resource/', '', $value->{'@URI'});
	if(in_array($entityName, $entities) && $unique)
		continue;
	array_push($entities, $entityName);
    }

    echo json_encode($entities);

}


?>
