<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include($_SERVER['DOCUMENT_ROOT']. "wiki/Services/Services.php");
ini_set("always_populate_raw_post_data", -1);
session_start();
return Services::getInstance()->updatePageView( $_SESSION['current_page_id'], $_POST['ended_at'], $_POST['scroll_percentage']);

?>



