<?php
include($_SERVER['DOCUMENT_ROOT']. "/facets/Lib/phpQuery-onefile.php");
include($_SERVER['DOCUMENT_ROOT']. "/facets/Lib/simple_html_dom.php");
session_start();


$startPoint = 'http://en.wikipedia.org/wiki/Diplomacy';
$pageToRender = isset($_REQUEST['page']) ? $_REQUEST['page'] : $startPoint;


renderPage($pageToRender);


function renderPage($page){

	$categories = ['Culture','Religion','Matter','Life','Law','Industry','Games','Arts','Science_and_technology','Society','Humanities','Health','Reference_works','Nature','Geography','History','Philosophy','People','Mathematics'];
    $_SESSION['prev_page']    =   isset($_SESSION['current_page']) ?   $_SESSION['current_page'] : null;
    $_SESSION['current_page'] =   $page;
    $html = file_get_html($page);
    $html = str_replace('href="/', 'href="http://en.wikipedia.org/', $html);
    $html = str_replace('/static/', 'http://en.wikipedia.org/static/', $html);
    $html = str_get_html($html);
    $html = phpQuery::newDocumentHTML($html);
	changeLinkRefs($html);
	
	$box = pq('#mw-panel');
	$box->html('');  
	$box->attr('style', 'position:fixed; overflow:auto; height:100%');  
	$box->append('<button id="apply"> Apply </button>');
	foreach ( $categories as $c){
		$box->append(getRange($c));
	}
	$head = pq('head');
	$head->append(getIncludes());
	$head->append(getCode($page));
	
	$body = pq('body');
	$head->append('<div id="slider-range"></div>');
    echo  $html;

}


function changeLinkRefs(){
    $links = pq("a");
    foreach ($links as $htmlLinks) {
        $pageLink =  pq($htmlLinks)->attr('href');
        if(strrpos($pageLink, '#') === false ) {
            pq($htmlLinks)->attr('href', "http://tagtheweb.com.br/facets?page=$pageLink");
        }
    }
}


function getRange($title){
	return '<p>

  <label for="amount">'.$title.':</label>
  <input type="text" class ="amount" id="amount-'.$title.'" readonly style="border:0; color:#f6931f; font-weight:bold;">
</p> 	<div id="slider-range-'.$title.'"class="slider"></div>';
}

function getIncludes(){
	return ' 
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>';
}

function getCode($page){
	return '
	<script>
	
	$(function(){
		apply();
	})
	
	
	map = {};
	page = "'.$page.'";

	function updateLinks(data){
		console.log(data);

		links = [];
		for(res in data){
			links.push(data[res].link);
		}

		$("a").each(function() {
    		text = $(this).attr("title");
    		if(text && text.indexOf("file:") == -1 && text.indexOf("Category:") ==-1 && text.indexOf("Wikipedia:") == -1 
    			&& text.indexOf("File:") == -1 ){
    			if( links.indexOf(text) >= 0){
    				$(this).attr("style", "background-color:#8c510a;color:white");
    			}else{
    				$(this).attr("style", "text-decoration:none;color:inherit");
    			}
    		}

		})
	}
	function apply(){
		$.ajax({
		    type: "POST",
		    url: "http://tagtheweb.com.br/facets/getFacets.php",
		    dataType: "JSON",
		    data: {"page": page, "map": map},
		    processData: true,
		    success: function(response) {
		        updateLinks(response);


		    },
		    error: function(response) {
		        console.log("fail", response);
		    }
		});
	}
	$( function() {
			var element = document.getElementById("apply");
	element.onclick = function() {apply()};
    $( ".slider" ).each(
			function(){
			    var id = $(this)[0].id;
				console.log(id);
				$(this).slider({
				  range: true,
				  min: 0,
				  max: 1,
				  step:0.01,
				  values: [ 0, 1 ],
				  slide: function( event, ui ) {
					  var name = id.replace("slider-range-","");
					$( "#amount-"+ name ).val(ui.values[ 0 ] + "  - " + ui.values[ 1 ] );
					map[name] = {"min":ui.values[ 0 ], "max": ui.values[ 1 ]}
					apply()
				   }
				});
				    $( "#amount"+ id.replace("slider-range","") ).val($( ".slider" ).slider( "values", 0 ) +
					" - " + $( "#"+id ).slider( "values", 1 ) );
			}
		)
	}
	
  )
  </script>
  '
  ;
  
}


?>

