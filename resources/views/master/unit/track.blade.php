@extends('base_theme')

@section("style")
    <style>
    	 /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }

      /* Optional: Makes the sample page fill the window. */
      html,
      body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
@stop

@section('content')
    <section class="content container-fluid">
        <div class="box box-solid">
        	<div class="box-body">
        		<div id="map" style="width: 100%; height: 400px;"></div>
        		<div>
        			<table width="100%" style="margin-top: 4px;">
        				<tbody>
        					<tr>
        						<td width="10%">Lokasi</td>
        						<td width="30%">: <span class="text-bold">{{ $unit->lokasi }}</span></td>
        						<td width="20%"></td>
        						<td width="10%">Status</td>
        						<td width="30%">: <span class="text-bold">{{ $unit->movement_status_desc }}</span></td>
        					</tr>
        					<tr>
        						<td>Signal Level</td>
        						<td>: <span class="text-bold">{{ $unit->gsm_signal_level }}</span></td>
        						<td></td>
        						<td>Kecepatan</td>
        						<td>: <span class="text-bold">{{ $unit->position_speed }} KM/Jam</span></td>
        					</tr>
        					<tr>
        						<td>Latitude</td>
        						<td>: <span class="text-bold">{{ $unit->position_latitude }}</span></td>
        						<td></td>
        						<td>Nozzle Kiri</td>
        						<td>: <span class="text-bold">{{ $unit->nozzle_kiri }}</span></td>
        					</tr>
        					<tr>
        						<td>Longitude</td>
        						<td>: <span class="text-bold">{{ $unit->position_longitude }}</span></td>
        						<td></td>
        						<td>Nozzle Kanan</td>
        						<td>: <span class="text-bold">{{ $unit->nozzle_kanan }}</span></td>
        					</tr>
        					<tr>
        						<td>Altitude</td>
        						<td>: <span class="text-bold">{{ $unit->position_altitude }}</span></td>
        						<td></td>
        						<td></td>
        						<td></td>
        					</tr>
        				</tbody>
        			</table>
        		</div>
        	</div>
        	<div class="box-footer text-right">
        		<a class="btn btn-sm btn-success" href="/master/unit/playback/{{$unit->id}}">PLAYBACK</a>
        	</div>
        </div>
    </section>
@stop

@section("script")
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <link
      href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet"
    />
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<script>
	var map;
	var marker;

	function updateLocation(){
		var icon = marker.getIcon();
		var position_direction = icon.rotation + 45;
		if(position_direction > 360) {
			position_direction = position_direction - 360
		}
	    icon.rotation = position_direction;
	    //marker.setIcon(icon);
	    var position_longitude = Number(marker.getPosition().lng()) + 0.000001;
	    var position_latitude = marker.getPosition().lat();
	    var position = new google.maps.LatLng(position_latitude, position_longitude);
        marker.setPosition(position);
		console.log(marker.icon.rotation);
		console.log(position_longitude);
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
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBQxxTv2PALE4bBtAuNr6qYgsLp5ZDKmRY&callback=initMap&libraries=&v=weekly"
      async
    ></script>
@stop