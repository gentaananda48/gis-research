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
    <section class="content-header">
        <h1>
            <i class="fa fa-map-marker"></i> 
            {{ $data->kode }}
        </h1>
    </section>

    <section class="content container-fluid">
        <div class="box box-solid">
        	<div class="box-body">
                <div id="map" style="width: 100%; height: 500px;"></div>
            </div>
            <div class="box-footer text-right">
        		<a class="btn btn-sm btn-success" href="/master/lokasi/">BACK</a>
        	</div>
        </div>    
    </section>
@stop

@section("script")
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<script>
	function initMap() {
  var koordinat = {!! $koordinat !!}
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 15,
    center: koordinat[0],
    mapTypeId: "satellite",
  });
  // Define the LatLng coordinates for the polygon's path.
  // Construct the polygon.
  const polygon = new google.maps.Polygon({
    paths: koordinat,
    strokeColor: "#FF0000",
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: "#FF0000",
    fillOpacity: 0.35,
  });
  polygon.setMap(map);
}
</script>
 <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBALLhhVF_c4wQ1CdlsZaDCaCD0ekaJn3Q&callback=initMap&libraries=&v=weekly"
      async
    ></script>
@stop