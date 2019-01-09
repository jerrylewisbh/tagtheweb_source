<?php
include($_SERVER['DOCUMENT_ROOT']. "wiki/Services/Services.php");
session_start();
ini_set("always_populate_raw_post_data", -1);
$response = array();
$response['success'] = "OK!";


$testType = (isset($_SESSION['completed_pre_test']) and   $_SESSION['completed_pre_test'] ==1) ? 2 : 1;
$_SESSION['completed_pre_test']=1;


Services::getInstance()->saveAnswers(session_id(), $testType, json_encode($_POST['answers']), $_POST['started_at'], $_POST['ended_at']);

echo json_encode($response);

if($testType == 2){
	$_SESSION['completed']= 1;

	$now = new DateTime('America/Sao_Paulo');
	$now =	$now->format('c');    
	Services::getInstance()->updateSessionEndTime(session_id(), $now);

}

?>