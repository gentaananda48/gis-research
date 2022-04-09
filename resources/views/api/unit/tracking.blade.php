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
    <div id="map" style="width: 100%; height: 75vh;"></div>
    <div style="padding: 10px 20px 10px 20px">
		<table width="100%" style="margin-top: 4px;">
			<tbody>
				<tr>
					<td width="10%">Lokasi</td>
					<td width="30%">: <span class="text-bold" id="lokasi">{{ $unit->lokasi }}</span></td>
					<td width="20%"></td>
					<td width="10%">Status</td>
					<td width="30%">: <span class="text-bold" id="movement_status_desc">{{ $unit->movement_status_desc }}</span></td>
				</tr>
				<tr>
					<td>Signal Level</td>
					<td>: <span class="text-bold" id="gsm_signal_level">{{ $unit->gsm_signal_level }}</span></td>
					<td></td>
					<td>Kecepatan</td>
					<td>: <span class="text-bold" id="position_speed">{{ $unit->position_speed }} KM/Jam</span></td>
				</tr>
				<tr>
					<td>Latitude</td>
					<td>: <span class="text-bold" id="position_latitude">{{ $unit->position_latitude }}</span></td>
					<td></td>
					<td>Nozzle Kiri</td>
					<td>: <span class="text-bold" id="nozzle_kiri">{{ $unit->nozzle_kiri }}</span></td>
				</tr>
				<tr>
					<td>Longitude</td>
					<td>: <span class="text-bold" id="position_longitude">{{ $unit->position_longitude }}</span></td>
					<td></td>
					<td>Nozzle Kanan</td>
					<td>: <span class="text-bold" id="nozzle_kanan">{{ $unit->nozzle_kanan }}</span></td>
				</tr>
				<tr>
					<td>Altitude</td>
					<td>: <span class="text-bold" id="position_altitude">{{ $unit->position_altitude }}</span></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>
	</div>
{!! Html::script('AdminLTE-2.4.18/bower_components/jquery/dist/jquery.min.js') !!}
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<script>
	var map;
	var marker;
	var unit_id = {!! $unit->id !!};
	function updateLocation(){
		$.ajax({
            type: 'GET',
            url: BASE_URL + '/master/unit/track_json/' + unit_id,
        }).then(function (data) {
            data = JSON.parse(data)
			var icon = marker.getIcon();
		    icon.rotation = data.position_direction;
		    marker.setIcon(icon);
		    var position = new google.maps.LatLng(data.position_latitude, data.position_longitude);
			marker.setPosition(position);
			map.setCenter(position);
			$("#lokasi").text(data.lokasi);
			$("#gsm_signal_level").text(data.gsm_signal_level);
			$("#position_latitude").text(data.position_latitude);
			$("#position_longitude").text(data.position_longitude);
			$("#position_altitude").text(data.position_altitude);
			$("#movement_status_desc").text(data.movement_status_desc);
			$("#position_speed").text(data.position_speed);
			$("#nozzle_kanan").text(data.nozzle_kanan);
			$("#nozzle_kiri").text(data.nozzle_kiri);
        });
	    setTimeout(updateLocation, 3 * 1000);
	}

	function initMap() {
  		var unit = {!! $unit !!}
		map = new google.maps.Map(document.getElementById("map"), {
		    zoom: 30,
		    center: {lng: unit.position_longitude, lat: unit.position_latitude},
		    mapTypeId: "satellite",
		});

		marker = new google.maps.Marker({
		    position: {lng: unit.position_longitude, lat: unit.position_latitude},
		    //label: 'TEST',
		    map: map,
		    //animation: google.maps.Animation.DROP,
		    icon: {
		        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
		        scale: 4,
		        strokeColor: '#00F',
		        rotation: 0,
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
			    fillOpacity: 0.4,
		  	});
	    	polygon.infoWindow = new google.maps.InfoWindow({
	      		content: lokasi.nama
	    	});
		  	polygon.setMap(map);
	    	google.maps.event.addListener(polygon, "mouseover", function(event){
	      		this.setOptions({fillColor: "#00FF00"});
	      		polygon.infoWindow.setPosition(event.latLng);
	      		polygon.infoWindow.open({ map, shouldFocus: false});
	    	}); 
	    	google.maps.event.addListener(polygon, "mouseout", function(event){
	      		this.setOptions({fillColor: '#964B00'});
	      		polygon.infoWindow.close();
	    	});
		});

	  // Define the LatLng coordinates for the polygon's path.
	  // Construct the polygon.
	  // const polygon = new google.maps.Polygon({
	  //   paths: koordinat,
	  //   strokeColor: "#FF0000",
	  //   strokeOpacity: 0.8,
	  //   strokeWeight: 2,
	  //   fillColor: "#FF0000",
	  //   fillOpacity: 0.35,
	  // });
	  // polygon.setMap(map);
		updateLocation();
	}
</script>
 <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBALLhhVF_c4wQ1CdlsZaDCaCD0ekaJn3Q&callback=initMap&libraries=&v=weekly"
      async
    ></script>
  </body>
</html>