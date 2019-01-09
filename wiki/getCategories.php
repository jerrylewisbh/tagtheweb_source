<?php


const MAX_QUERY_ELEMENTS = 1;
header('Content-type:application/json;charset=utf-8');
$unique = false;
switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if(!isset($_GET['entities'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$entities_param =  $_GET['entities'];
		$unique = isset($_GET['unique']) && $_GET['unique'] === 'true'? true: false;
		break;

	case 'POST':
		if(!isset($_POST['entities'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$entities_param = $_POST['entities'];
		$unique = isset($_POST['unique']) && $_POST['unique'] === 'true'? true: false;
		break;
	
	default:
		http_response_code(405);
		echo 'Method not allowed';
		die();
		break;
}


function getCategories($entities){
	if(strrpos($entities, "|") == sizeof($entities) - 1){
		$entities = substr($entities, 0, -1);
		echo  $entities;
	}

	$query = http_build_query([
	 'action' => 'query',
	 'format' => 'json',
	 'prop' => 'categories',
	 'titles' => $entities ,
	 'redirects' => 1,
	 'clshow' => '!hidden',
	 'cllimit' => 500
	]);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://en.wikipedia.org/w/api.php?');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

	curl_setopt($ch, CURLOPT_USERAGENT, 'LibraryIndexing/1.1 jerry.medeiros@uniriotec.br)');
	curl_setopt($ch, CURLOPT_POSTFIELDS  , $query);

	$result = curl_exec($ch);
	$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);


	$categories = [];
	if($response == 200){
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        $pages = json_decode($body);
        $data = $pages->query->pages;   
        foreach ($data as $key => $value){
		if(!isset($value->categories)) continue;
        	foreach ($value->categories as $key => $value) {
			$categoryName =  str_replace(' ','_',str_replace('Category:', '',$value->title));
			if(in_array($categoryName, $categories) && $unique)
				continue;
        		array_push($categories, $categoryName);
        	}  
        }
    }
    return $categories;

}


$entities = explode("|", $entities_param);
$total_entities = sizeof($entities);
$query_times = ceil($total_entities/MAX_QUERY_ELEMENTS);
$query_titles = array_fill(0,$query_times, "");
$count = 1;
$return_categories = [];

foreach ($entities as $entity){
	$array_index = max(ceil($count/MAX_QUERY_ELEMENTS) -1, 0 );
	$query_titles[(int)$array_index] = $query_titles[(int)$array_index]  . str_replace(" ", "_", $entity) . '|';
	$count = $count + 1;

}
for ($i = 0; $i< $query_times; $i++){
		echo $query_titles[$i];
		$return_categories = array_merge($return_categories, getCategories($query_titles[$i]));
	}


echo json_encode($return_categories);



?>
