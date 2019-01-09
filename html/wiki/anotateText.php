<?php

header('Content-type:application/json;charset=utf-8');

$categories_param = [];
$unique = false;
switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
	case 'POST':
		if(!isset($_REQUEST['text'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		if(!isset($_REQUEST['language'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}

		$text_param = $_REQUEST['text'];
		$language = $_REQUEST['language'];
		$unique = isset($_REQUEST['unique']) && $_REQUEST['unique'] === 'true'? true: false;
		break;
	
	default:
		http_response_code(405);
		echo 'Method not allowed';
		die();
		break;
}



$query = http_build_query([
 'text' => $text_param,
  'confidence'=> 0.2
]);


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://model.dbpedia-spotlight.org/'.$language.'/annotate?');
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
    	$split = explode( '/', $value->{'@URI'});
    	$entityName = $split[count($split) -1 ];
	if(in_array($entityName, $entities) && $unique)
		continue;
	array_push($entities, $entityName);
    }

    echo json_encode($entities);

}


?>
