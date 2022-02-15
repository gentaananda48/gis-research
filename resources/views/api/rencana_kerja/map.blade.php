<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boom Sprayer</title>
    <link rel="shortcut icon" type="img/png" href="{{ url('/img/logo.png') }}"/>
    {!! Html::style('AdminLTE-2.4.18/bower_components/bootstrap/dist/css/bootstrap.min.css') !!}
    <!--head-->
    <style>
    	html, body, #map {
		    width: 100%;
		    height: 100vh;
		    margin: 0;
		    padding: 0;
		}
		#map {
		    position: relative;
		}
    </style>
</head>
<body>
    <div id="map" style="width: 100%; height: 100vh;"></div>
{!! Html::script('AdminLTE-2.4.18/bower_components/jquery/dist/jquery.min.js') !!}
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<script>
	var map;
	var marker;
	var poly;
	var lacak = {!! $list_lacak !!};
	var i = 0;

	function initMap() {
		map = new google.maps.Map(document.getElementById("map"), {
		    zoom: 17,
		    //center: {lng: lacak[0].position_longitude, lat: lacak[0].position_latitude},
		    mapTypeId: "satellite",
		});

		google.maps.Polygon.prototype.my_getBounds=function(){
		    var bounds = new google.maps.LatLngBounds()
		    this.getPath().forEach(function(element,index){bounds.extend(element)})
		    return bounds
		}

		marker = new google.maps.Marker({
		    position: {lng: lacak[0].position_longitude, lat: lacak[0].position_latitude},
		    //label: 'TEST',
		    map: map,
		    //animation: google.maps.Animation.DROP,
		    icon: {
		        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
		        fillColor: '#04F8E5',
			    fillOpacity: 1,
			    strokeWeight: 1,
		        strokeColor: '#04F8E5',
			    scale: 5,
		        rotation: lacak[0].position_direction,
		        anchor: new google.maps.Point(0, 5),
		    }
		});

		var list_lokasi = {!! $list_lokasi !!} || []
	  	list_lokasi.forEach(function(lokasi) {
		  	const polygon = new google.maps.Polygon({
			    paths: lokasi.koordinat,
			    strokeColor: '#964B00',
			    strokeOpacity: 0.9,
			    strokeWeight: 2,
			    fillColor: '#964B00',
			    fillOpacity: 0.4
		  	});
	    	polygon.infoWindow = new google.maps.InfoWindow({
	      		content: lokasi.nama
	    	});
		  	polygon.setMap(map);
		  	map.setCenter(polygon.my_getBounds().getCenter());
		});
		for (var i = 0, len = lacak.length; i < len; i += 1) {
		    var icon = marker.getIcon();
			icon.rotation = lacak[i].position_direction;
	    	marker.setIcon(icon);
	    	var position = new google.maps.LatLng(lacak[i].position_latitude, lacak[i].position_longitude);
			marker.setPosition(position);
			if(i>0){
				if(lacak[i-1].din_3 == 1 && (lacak[i-1].din_1==1 || lacak[i-1].din_2==1)) {
					var strokeColor = lacak[i-1].din_1==1 && lacak[i-1].din_2==1 ? "#0CF704" : lacak[i-1].din_1==1 && lacak[i-1].din_2==0 ? "#F0FF01" : "#05FAE4";
					var strokeWeight = lacak[i-1].din_1==1 && lacak[i-1].din_2==1 ? 12 : 7;
					var poly = new google.maps.Polyline({
					    path: [new google.maps.LatLng(lacak[i-1].position_latitude, lacak[i-1].position_longitude), position],
					    geodesic: true,
					    strokeColor: strokeColor,
					    strokeOpacity: 1.0,
					    strokeWeight: strokeWeight,
			    		zIndex: 999999,
					});
			    	poly.setMap(map);
				} else {
					var poly = new google.maps.Polyline({
					    path: [new google.maps.LatLng(lacak[i-1].position_latitude, lacak[i-1].position_longitude), position],
					    geodesic: true,
					    strokeColor: "#FF0000",
					    strokeOpacity: 1.0,
					    strokeWeight: 3,
			    		zIndex: 999999,
					});
			    	poly.setMap(map);
				}
			}
		}
	}
</script>
 <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
<script
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBQxxTv2PALE4bBtAuNr6qYgsLp5ZDKmRY&callback=initMap&libraries=&v=weekly"
  async
></script>
  </body>
</html>