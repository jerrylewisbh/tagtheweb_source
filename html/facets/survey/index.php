<?php
include(dirname(dirname(__FILE__)) . "../Services/Services.php");
session_start();

if(	isset($_SESSION['completed']) && $_SESSION['completed'] == 1){
	echo 'THANKS!!';
	return;
}
 $answerData= '';

if(isset($_SESSION["completed_pre_test"]) and $_SESSION["completed_pre_test"] == 1 and !isset($_GET['test_type'])){
	 header("Location: http://localhost/wiki");
}
else if(isset($_GET['test_type']) && $_GET['test_type'] ==2){

	 $answerData  =  Services::getInstance()->getAnswers(session_id(), 1);
	// print_r( $answerData);
	// header("Location: http://localhost/survey");
}
else{
	$_SESSION["completed_pre_test"] = 0;

	$_SESSION['session_type'] = Services::getInstance()->getMinSessionType();

	Services::getInstance()->createSession(session_id(), $_SERVER['REMOTE_ADDR'], $_SESSION['session_type']);
}

?>


<!doctype html>
<html>
<head>

<script type="text/javascript">
var answerData = <?php echo json_encode($answerData ); ?>;
</script>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Teste</title>
<link href="css/stylesheet.css" type="text/css" rel="stylesheet">
<link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
</head>

<body>
<div class="wrapper">
<div class="main">

<div><h1 class="title">Teste</h1><br>

<div class="question-container"></div>
<a id="backBtn" href="#" class="button">« Back</a>
<a id="nextBtn" href="#" class="button">Continue »</a>
<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="survey.js" type="text/javascript"></script>

</div><div class="completed-message"></div>
<a id="step2Btn" href="#" class="button">Continue »</a>
</div></div>


<div class="footer"></div>
</div>
</body>
</html>
