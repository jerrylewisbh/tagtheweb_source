<?php
include($_SERVER['DOCUMENT_ROOT']. "/facets/Lib/phpQuery-onefile.php");
include($_SERVER['DOCUMENT_ROOT']. "/facets/Lib/simple_html_dom.php");
include($_SERVER['DOCUMENT_ROOT']. "/facets/Services/Services.php");
header("Access-Control-Allow-Origin: *");
	
	$categories = ['Culture','Religion','Matter','Life','Law','Industry','Games','Arts','Science_and_technology','Society','Humanities','Health','Reference_works','Nature','Geography','History','Philosophy','People','Mathematics'];
	

	$values = [];
	$map =  isset($_POST["map"]) ? $_POST["map"] : [];
	$page = $_POST["page"];

	$page = str_replace( 'http://en.wikipedia.org/wiki/', '', $page);
	array_push($values, $page);
	foreach ($categories as $key => $value) {
		array_push($values, isset($map[$value]) ? $map[$value]["min"] : 0);		
		array_push($values, isset($map[$value]) ? $map[$value]["max"] : 1);		
	}

	$instance = Services::getInstance();
	//$result = $instance->getFacets(0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	$result = call_user_func_array(array($instance,"getFacets"),  $values);
	echo(json_encode($result));


?>

