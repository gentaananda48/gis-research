@extends('base_theme')
@section("style")
    <style>
	    .table-responsive .table th, .table-responsive .table td {
	        white-space: nowrap !important;
	    }
    </style>
@stop
@section('content')
	<section class="content-header">
		<h1>
			Summary
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					<div class="box-body">
						<div class="box box-default box-solid">
				        	<div class="box-body">
				       		 	<div id="map" style="width: 100%; height: 500px;"></div>
				        	</div>
				        </div>
						<div class="box box-default box-solid">
							<div class="box-body">
								<table class="table">
									<tbody>
										<tr>
											<td width="15%">JENIS APLIKASI</td>
											<td>: {{$rk->aktivitas_nama}}</td>
											<td width="10%">LOKASI</td>
											<td>: {{$rk->lokasi_kode}}</td>
										</tr>
										<tr>
											<td>LUAS NETTO</td>
											<td>: {{$rk->lokasi_lsnetto}} Ha</td>
											<td>LUAS BRUTO</td>
											<td>: {{$rk->lokasi_lsbruto}} Ha</td>
										</tr>
										<tr>
											<td>JENIS APLIKASI</td>
											<td>: {{$rk->aktivitas_nama}}</td>
											<td>LOKASI</td>
											<td>: {{$rk->lokasi_kode}}</td>
										</tr>
										<tr>
											<td>JAM MULAI</td>
											<td>: {{$rk->jam_mulai}}</td>
											<td>JAM SELESAI</td>
											<td>: {{$rk->jam_selesai}}</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="box box-default box-solid">
							<div class="box-body">
								<table class="table">
								<tr>
									<th width="5%">Ritase</th>
									@if(!empty($list_rks[1]))
										@foreach($list_rks[1] as $k2=>$v2)
										<th>{{$v2->parameter_nama}} <br/>{{ $v2->parameter_id<999 ? '('.$v2->standard.')' : '' }}</th>
										@endforeach
									@else
										<th colspan="6"></th>
										<th>Hasil</th>
									@endif
									<th>Kualitas</th>
								</tr>
								@foreach($list_rks as $k=>$v)
								<tr>
									@if($k==999999)
									<th colspan="7">Total Nilai Kualitas Spraying</th>
									@elseif($k==999)
									<th colspan="7">{{$v[0]->parameter_nama}} ({{$v[0]->standard}})</th>
									@else
									<td>{{$k}}</td>
									@endif
									
									@if($k<999)
										@foreach($v as $k2=>$v2)
										<td>{{ $v2->parameter_id<999 ? $v2->realisasi.' '.$v2->uom : '' }} {{$v2->parameter_id<999 ? '('.$v2->nilai.')' : $v2->nilai }} </td>
										@if($k2==6)
										<td>{{$v2->kualitas}}</td>
										@endif
										@endforeach
									@else
										@foreach($v as $k2=>$v2)
										<td>{{$v2->nilai}}</td>
										<td>{{$v2->kualitas}}</td>
										@endforeach
									@endif
								</tr>
								@endforeach
								</table>
							</div>
						</div>
					</div>
					<div class="box-footer">
						<a href="{{ url('/report/rencana_kerja') }}" class="btn btn-warning"> Back </a>
					</div>
				</div>
			</div>
		</div>
	</section>

<div class="modal fade win-info" tabindex="-1" role="dialog" aria-labelledby="winFormMenuLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Information</h4>
                </div>
                <div class="modal-body">
                    <table class="table">
                    	<tbody>
                    		<tr>
                    			<td>Lokasi</td>
                    			<td>: <span>{{$rk->lokasi_kode}}</span></td>
                    			<td>Aktivitas</td>
                    			<td>: <span>{{$rk->aktivitas_nama}}</span></td>
                    		</tr>
                    		<tr>
                    			<td>Nozzle</td>
                    			<td>: <span>{{$rk->nozzle_nama}}</span></td>
                    			<td>Volume</td>
                    			<td>: <span>{{$rk->volume}}</span></td>
                    		</tr>
                    		<tr>
                    			<td>Latitude</td>
                    			<td>: <span id="info-latitude"></span></td>
                    			<td>Kecepatan</td>
                    			<td>: <span id="info-kecepatan"></span> KM/Jam</td>
                    		</tr>
                    		<tr>
                    			<td>Longitude</td>
                    			<td>: <span id="info-longitude"></span></td>
                    			<td>Nozzle Kiri</td>
                    			<td>: <span id="info-nozzle-kanan"></span></td>
                    		</tr>
                    		<tr>
                    			<td>Altitude</td>
                    			<td>: <span id="info-altitude"></span></td>
                    			<td>Nozzle Kanan</td>
                    			<td>: <span id="info-nozzle-kiri"></span></td>
                    		</tr>
                    	</tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
@stop
