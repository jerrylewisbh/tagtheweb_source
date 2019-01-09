<?php

header('Content-type:application/json;charset=utf-8');
$username='neo4j';
$password='1908878715';



$categories_param = [];
$normalize = true;
switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if(!isset($_GET['categories'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$categories_param = explode("|", $_GET['categories']);
		$normalize = isset($_GET['normalize']) ? $_GET['normalize'] : true;
		break;

	case 'POST':
		if(!isset($_POST['categories'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$categories_param =  explode("|", $_POST['categories']);
		$normalize = isset($_POST['normalize']) ?  $_POST['normalize']: true;
		break;
	
	default:
		http_response_code(405);
		echo 'Method not allowed';
		die();
		break;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://tagtheweb.com.br:7474/db/data/cypher");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, true);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

$query = [
    'query' => 'match p = allShortestPaths((source:Category)-[r:SUBCAT_OF*]->(destination:Category))
        where source.categoryName IN {categories} AND destination.categoryName IN ["Main_topic_classifications"]
        return extract(n in nodes(p)| n.categoryName) as Paths',
                'params' => ["categories" => $categories_param]
];
//echo $_POST;
$query =  json_encode($query);
curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json; ','Content-Type: application/json','Content-Length: ' . strlen($query),'X-Stream: true'));
curl_setopt($ch, CURLOPT_POSTFIELDS  , $query);


$result = curl_exec($ch);
$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if($response == 200){
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        $categories = json_decode($body );

        $data = $categories->data;
        echo json_encode($data);
}



curl_close($ch);



?>
