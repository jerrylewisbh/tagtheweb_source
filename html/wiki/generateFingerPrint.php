<?php

header('Content-type:application/json;charset=utf-8');
$username='neo4j';
$password='1908878715';



$categories_param = [];
$normalize = true;
$ignoreContainers = false;
$depth = 0;


switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		if(!isset($_GET['categories'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$categories_param = explode("|", $_GET['categories']);
		$normalize = isset($_GET['normalize']) & $_GET['normalize'] == '1' ? true : false;
		$ignoreContainers = isset($_GET['ignoreContainers']) && $_GET['ignoreContainers'] === 'true'? true: false;
		$depth = isset($_GET['depth']) ? intval($_GET['depth']) : 0;
		break;

	case 'POST':
		if(!isset($_POST['categories'])){
			http_response_code(400);
			echo 'Missing parameter';
			die();
		}
		$categories_param =  explode("|", $_POST['categories']);
		$normalize = isset($_POST['normalize']) & $_POST['normalize'] == '1' ? true : false;
		$ignoreContainers = isset($_POST['ignoreContainers']) && $_POST['ignoreContainers'] === 'true'? true: false;
		$depth = isset($_POST['depth']) ? intval($_POST['depth']) : 0;
		break;
	
	default:
		http_response_code(405);
		echo 'Method not allowed';
		die();
		break;
}




$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:7474/db/data/cypher");
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
//echo $query;
curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json; ','Content-Type: application/json','Content-Length: ' . strlen($query),'X-Stream: true'));
curl_setopt($ch, CURLOPT_POSTFIELDS  , $query);


$result = curl_exec($ch);
$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);


if($depth > 0){
$category_map = [];
}else{

$category_map = [
		'Culture' => 0
		,'Religion' => 0
		,'Matter' => 0
		,'Life' => 0
		,'Law' => 0
		,'Industry' => 0
		,'Games' => 0
		,'Arts' => 0
		,'Science_and_technology' => 0
		,'Society' => 0
		,'Humanities' => 0
		,'Health' => 0
		,'Reference_works' => 0
		,'Nature' => 0
		,'Geography' => 0
		,'History' => 0
		,'Philosophy' => 0
		,'People' => 0
		,'Mathematics' => 0
	];

}



$total_categories = 0;
if($response == 200){
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        $categories = json_decode($body );

        $data = $categories->data;
        foreach ($data as $key => $value){
        	$last = sizeof($value[0]) - 2 - $depth;

        	if($last < 0 || $last > sizeof($value[0])){
        		continue;
        	}

            $category = $value[0][$last];
            if(!array_key_exists($category, $category_map)){
            	$category_map[$category] = 0;
            }
			$category_map[$category] = $category_map[$category] + 1;
			$total_categories = $total_categories + 1;
        }
		if($normalize){	
			foreach($category_map as $key => $value){
				$category_map[$key] = $value/ $total_categories ;
			}
		}
}

echo json_encode($category_map);
curl_close($ch);



?>
