<?php
include($_SERVER['DOCUMENT_ROOT']. "wiki/Lib/phpQuery-onefile.php");
include($_SERVER['DOCUMENT_ROOT']. "wiki/Lib/simple_html_dom.php");
include($_SERVER['DOCUMENT_ROOT']. "wiki/Services/Services.php");
session_start();



if(isset($_SESSION['link_rank_done'])){
    echo 'THANKS';
    return;
}

if(isset($_SESSION['link_selection_done'])){
     header("Location: http://localhost/wiki/expert_review_2.php");

}


Services::getInstance()->createSession(session_id(), $_SERVER['REMOTE_ADDR'], 4);

$startPoint = 'http://pt.wikipedia.org/wiki/AK-47';
$pageToRender = isset($_REQUEST['page']) ? $_REQUEST['page'] : $startPoint;

renderPage($pageToRender);


function renderPage($page){

    $_SESSION['prev_page']    =   isset($_SESSION['current_page']) ?   $_SESSION['current_page'] : null;
    $_SESSION['current_page'] =   $page;
    $html = file_get_html($page);
    $html = str_replace('href="/', 'href="http://pt.wikipedia.org/', $html);
    $html = str_replace('/static/', 'http://pt.wikipedia.org/static/', $html);
    $html = str_get_html($html);
    $html = phpQuery::newDocumentHTML($html);

    //ALL LINKS
    changeLinkRefs($html);
    $html["head"]->prepend(get_dynamic());
    $html["body"]->prepend(getButton());
    $html["body"]->prepend(
        "<div id='likert-scale' class='hide'>
        <div class='radio'>
          <label><input type='radio' id= 4 name='likert'>Very Important</label>
      </div>
      <div class='radio'>
          <label><input type='radio' id= 3 name='likert'>Important</label>
      </div>
      <div class='radio'>
          <label><input type='radio' id= 2 name='likert'>Moderately Important</label>
      </div>
      <div class='radio'>
          <label><input type='radio' id= 1 name='likert'>Slightly Important</label>
      </div>
      <div class='radio'>
          <label><input type='radio' id= 0 name='likert'>Not Important</label>
      </div>
  </div>");



    $_SESSION['current_page_id'] = Services::getInstance()->trackPageView(session_id(),$_SESSION['prev_page'], $_SESSION['current_page']);
    echo  $html;
}

function changeLinkRefs(){
    $links = pq("a");
    global $pageToRender;
    $linkID = 1;
    foreach ($links as $htmlLinks) {
        $pageLink =  pq($htmlLinks)->attr('href');
        if(strrpos($pageLink, '#') === false  AND strrpos($pageLink, '/w/') === false  AND strrpos($pageLink, ':') == 4 AND $pageToRender != $pageLink AND strrpos($pageLink, 'wikipedia') !== false ) {
           // pq($htmlLinks)->attr('href', "http://localhost/wiki?page=$pageLink");
            pq($htmlLinks)->attr('href', "#!");
            pq($htmlLinks)->attr('id', 'link'.$linkID);
            pq($htmlLinks)->attr('data-placement', "bottom");
            pq($htmlLinks)->attr('data-toggle', "popover");
            pq($htmlLinks)->attr('data-container', "body");
            pq($htmlLinks)->attr('type', "button");
            pq($htmlLinks)->attr('data-trigger', "focus");
            pq($htmlLinks)->attr('style', "background-color:#8c510a;color:white");
            pq($htmlLinks)->addClass('likert-pop');
            //pq($htmlLinks)->attr('data-html', "true");
            $linkID +=1;

        }else{
           pq($htmlLinks)->removeAttr('href');
           pq($htmlLinks)->attr('style', 'text-decoration:none;color:inherit');      

       }
   }
}

//Remove todos os links 
function removeAllLinks(&$doc) {
    $links = pq("a");
    foreach ($links as $htmlLinks) {
        pq($htmlLinks)->removeAttr('href');
        pq($htmlLinks)->attr('style', 'text-decoration:none;color:inherit');      
    }
}

//retorna a string com o código da ferramenta HOTJAR 
function get_dynamic(){
    return "
    <script src='http://code.jquery.com/jquery-1.11.3.min.js'></script>
    <!-- Latest compiled and minified CSS -->
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>

    <!-- Optional theme -->
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css' integrity='sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp' crossorigin='anonymous'>

    <!-- Latest compiled and minified JavaScript -->
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js' integrity='sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa' crossorigin='anonymous'></script>



    <script>
        function goToNextStep(){
           $.ajax(
           {type: 'POST',
           url: 'http://localhost/wiki/expert_review_2.php',
           dataType: 'JSON',
           data: relevanceMap ,
           processData: true,
           success: function(response) {
           $(location).attr('href','http://localhost/wiki/expert_review_2.php')


        },
        error: function(response) {
            console.log('fail', response);
        }
    });
}


function changeColorByRelevance(relevance, link){
    var color = '#8c510a';
    switch (relevance) {
        case '0':
        color = '#ffffcc';
        break;
        case '1':
        color = '#a1dab4';
        break;
        case '2':
        color = '#41b6c4';
        break;
        case '3':
        color = '#2c7fb8';
        break;
        case '4':
        color = '#253494';
        break;

    }

    $('#'+currentLink).css('background-color', color);

}
var currentLink = '';
var relevanceMap = {};
var currentRef = '';
$(function() {
    $('a').popover({
        html: true,     
        delay: { 
           show: '0', 
           hide: '600'
       },
       placement: 'auto right',
       content: function() {
          return $('#likert-scale').html();
      }
  });


  $('a').on('shown.bs.popover', function(){
    $('input[name=likert]:radio').change(function () {
        var relevance = $('input[name=likert]:checked').attr('id');
        changeColorByRelevance(relevance, currentLink);
        relevanceMap[currentRef] = relevance;
    })
})


$('a').click(function() {
    currentLink = $(this).attr('id');
    currentRef = $(this).attr('data-original-title');
});







});



</script>
";
}


function getButton(){
    return "<div 'id='nextStep' onclick='goToNextStep();' style='cursor:pointer; position:fixed;width:100%;height: 30px;text-align: center;background-color: blue;opacity: 0.8;z-index: 3000;color: white;'> <p> Once you have finished reading the article, click here to go to the last step</p> </div>";
}


?>

