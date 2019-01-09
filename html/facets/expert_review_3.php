<?php
include($_SERVER['DOCUMENT_ROOT']. "wiki/Lib/phpQuery-onefile.php");
include($_SERVER['DOCUMENT_ROOT']. "wiki/Lib/simple_html_dom.php");
include($_SERVER['DOCUMENT_ROOT']. "wiki/Services/Services.php");
session_start();

if(isset($_SESSION['link_rank_done'])){
	echo 'THANKS';
	return;
}


if(isset($_POST)){
	echo Services::getInstance()->saveLinkRanking(session_id(), $_POST);
	$_SESSION['link_rank_done'] = 1;
}

?>

