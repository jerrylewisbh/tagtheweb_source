<?php
include($_SERVER['DOCUMENT_ROOT']. "wiki/Lib/phpQuery-onefile.php");
include($_SERVER['DOCUMENT_ROOT']. "wiki/Lib/simple_html_dom.php");
include($_SERVER['DOCUMENT_ROOT']. "wiki/Services/Services.php");
session_start();
//echo session_id();
/*foreach($_POST as $key=>$value) {
  echo $key;
}*/

if(isset($_SESSION['link_rank_done'])){
	echo 'THANKS';
	return;
}


if(!isset($_SESSION['link_selection_done'])  and !isset($_SESSION['link_rank_done'])){
	echo Services::getInstance()->saveRelevantLinks(session_id(), $_POST);
	$_SESSION['link_selection_done'] = 1;

}else if(isset($_SESSION['link_selection_done'])  and !isset($_SESSION['link_rank_done'])){


	$result =  Services::getInstance()->getSelectedLinks(session_id());
	$base = '<li id=:link class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>:link</li>';

	$list = '';
	foreach($result as $key=>$value) {
		$element = str_replace(':link', $value['link'], $base );
		$list = $list.' '.$element;
	}

	echo 

	"<html lang='en'>
	<head>
		<meta charset='utf-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1'>
		<title>jQuery UI Sortable - Default functionality</title>
		<link rel='stylesheet' href='//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'>
		<link rel='stylesheet' href='/resources/demos/style.css'>
		<style>
		#sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
		#sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
		#sortable li span { position: absolute; margin-left: -1.3em; }
		</style>
		<script src='https://code.jquery.com/jquery-1.12.4.js'></script>
		<script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
		<script>
			var rankMap = {};
			function goToNextStep(){
				var rank = $('#sortable').sortable('toArray');
				var n = rank.length;
				for(var i=0; i<n; i++){
					console.log(rank[i]);
					rankMap[i] = rank[i]; 
				}
				console.log(rankMap);
				$.ajax(
				{type: 'POST',
				url: 'http://localhost/wiki/expert_review_3.php',
				dataType: 'JSON',
				data: rankMap ,
				processData: true,
				success: function(response) {
					$(location).attr('href','http://localhost/wiki/expert_review_2.php')


				},
				error: function(response) {
					console.log('fail', response);
				}
			});

		}

		$( function() {
			$( '#sortable' ).sortable();
			$( '#sortable' ).disableSelection();
		} );
	</script>
</head>
<body>
	<div 'id='nextStep' onclick='goToNextStep();' style='cursor:pointer; margin-bottom:30px; position:relative;width:100%;height: 30px;text-align: center;background-color: blue;opacity: 0.8;z-index: 3000;color: white;'> <p> Once you have finished ranking the itens, click here to finish</p> </div>
	<div style='text-align:center'>
		<ul id='sortable' style ='display:inline-block'>
			$list
		</ul>
	</div>


</body>
</html>";


}else{
	
}




?>

