 google.charts.load('current', {packages: ['corechart', 'bar']});


function start(json) {
      var data = new  google.visualization.DataTable();

     data.addColumn("string","Category");
     data.addColumn("number","%");
     var arrayData = [];
      for(element in json){
        arrayData.push([element.split("_").join("\n"), json[element]*100]);
      }

      data.addRows(arrayData);

      var options = {
        title: 'Composition of your tag',
          width: 950,
          height: 200,
        vAxis: {
          title: '%',
        },
        hAxis:{
          slantedText:false, slantedTextAngle:40,
          maxTextLines: 3, textPosition:'out'
        },
        chartArea: {width: '100%', height: '60%', top:10},
      };

      var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
      chart.draw(data, options);

      var hash = btoa(arrayData.toString());
      $("#qr_tag").attr("src", "http://tagtheweb.com.br/qrcode/php/qr_img.php?d=" + hash);


    }

