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
											<td>UNIT</td>
											<td>: {{$rk->unit_label}}</td>
										</tr>
										<tr>
											<td>NOZZLE</td>
											<td>: {{$rk->nozzle_nama}}</td>
											<td>VOLUME AIR</td>
											<td>: {{$rk->volume}}</td>
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
								<table class="table table-bordered">
							        <tbody>
								        <tr>
									        <th>Ritase</th>
									        @foreach($summary->header as $v)
									        <th>{{$v}}</th>
									        @endforeach
									        <th rowspan="{{count($summary->ritase) + 2}}"></th>
								        </tr>
							        	@foreach($summary->ritase as $v)
							            <tr>
								            <td>{{$v['ritase']}}</td>
								            @foreach($summary->header as $k2=>$v2)
								            @if($k2==4 || $rk==5)
									        <th>{{ doubleval($v['parameter_'.$k2]) <= 2 ? 'N/A': $v['parameter_'.$k2] }}</th>
								            @else
									        <th>{{$v['parameter_'.$k2]}}</th>
									        @endif
									        @endforeach
							            </tr>
							        	@endforeach
								        <tr>
									        <th>Rata-rata</th>
									        @foreach($summary->rata2 as $k=>$v)
									        @if($k==4 || $rk==5)
									        <th>{{ doubleval($v) <= 2 ? 'N/A': $v }}</th>
								            @else
									        <th>{{$v}}</th>
									        @endif
									        @endforeach
								        </tr>
								        <tr>
									        <th>Poin</th>
									        @foreach($summary->poin as $v)
									        <th>{{$v}}</th>
									        @endforeach
								        </tr>
								        <tr>
									        <th colspan=4>Kategori</th>
									        <th>{{$summary->kualitas}}</th>
								        </tr>
							        </tbody>
						        </table>
							</div>
						</div>
						<div class="box box-default box-solid">
							<div class="box-body table-responsive">
								<table class="table table-bordered">
							        <thead>
							            <tr>
								            <th rowspan="2" style="vertical-align: middle; text-align: center;">Ritase</th>
								            <th colspan="5" style="vertical-align: middle; text-align: center;">Speed</th>
								            <th colspan="5" style="vertical-align: middle; text-align: center;">Wing Level Kanan</th>
								            <th colspan="5" style="vertical-align: middle; text-align: center;">Wing Level Kiri</th>
								            <th rowspan="2" style="vertical-align: middle; text-align: center;">Suhu</th>
							            </tr>
							            <tr>
								            <th style="vertical-align: middle; text-align: center;">Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Average</th>
								            <th style="vertical-align: middle; text-align: center;">Dibawah Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Dalam Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Diatas Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Average</th>
								            <th style="vertical-align: middle; text-align: center;">Dibawah Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Dalam Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Diatas Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Average</th>
								            <th style="vertical-align: middle; text-align: center;">Dibawah Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Dalam Standard</th>
								            <th style="vertical-align: middle; text-align: center;">Diatas Standard</th>
							            </tr>
							        </thead>
							        <tbody>
							        	@foreach($list_percentage as $v)
							            <tr>
								            <td>{{$v->ritase}}</td>
								            <td>{{$v->std_speed}}</td>
								            <td>{{$v->avg_speed}}</td>
								            <td>{{$v->prc_speed_under_standard}}</td>
								            <td>{{$v->prc_speed_standard}}</td>
								            <td>{{$v->prc_speed_upper_standard}}</td>
								            <td>{{$v->std_arm_height_right}}</td>
								            <td>{{$v->avg_arm_height_right}}</td>
								            <td>{{$v->prc_arm_height_right_under_standard}}</td>
								            <td>{{$v->prc_arm_height_right_standard}}</td>
								            <td>{{$v->prc_arm_height_right_upper_standard}}</td>
								            <td>{{$v->std_arm_height_left}}</td>
								            <td>{{$v->avg_arm_height_left}}</td>
								            <td>{{$v->prc_arm_height_left_under_standard}}</td>
								            <td>{{$v->prc_arm_height_left_standard}}</td>
								            <td>{{$v->prc_arm_height_left_upper_standard}}</td>
								            <td>{{$v->avg_temperature_right}}</td>
							            </tr>
							        	@endforeach
							        </tbody>
						        </table>
							</div>
						</div>
					</div>
					<div class="box-footer">
						<a href="{{ url('/report/rencana_kerja') }}" class="btn btn-warning"> Back </a>
						<a href="{{ url('/report/rencana_kerja/playback/'.$rk->id) }}" class="btn btn-info"> Playback </a>
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
		    center: {lng: lacak[0].position_longitude, lat: lacak[0].position_latitude},
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
				if(lacak[i-1].pump_switch_main == 1 && (lacak[i-1].pump_switch_right==1 || lacak[i-1].pump_switch_left==1)) {
					var strokeColor = lacak[i-1].pump_switch_right==1 && lacak[i-1].pump_switch_left==1 ? "#00FF00" : lacak[i-1].pump_switch_right==1 && lacak[i-1].pump_switch_left==0 ? "#FFA500" : "#FFFF00";
					var strokeWeight = lacak[i-1].pump_switch_right==1 && lacak[i-1].pump_switch_left==1 ? 12 : 7;
					var poly = new google.maps.Polyline({
					    path: [new google.maps.LatLng(lacak[i-1].position_latitude, lacak[i-1].position_longitude), position],
					    geodesic: true,
					    strokeColor: strokeColor,
					    strokeOpacity: 0.5,
					    strokeWeight: strokeWeight,
			    		zIndex: 999999,
					});
			    	poly.setMap(map);
				} else {
					var poly = new google.maps.Polyline({
					    path: [new google.maps.LatLng(lacak[i-1].position_latitude, lacak[i-1].position_longitude), position],
					    geodesic: true,
					    strokeColor: "#FF0000",
					    strokeOpacity: 0.5,
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
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBALLhhVF_c4wQ1CdlsZaDCaCD0ekaJn3Q&callback=initMap&libraries=&v=weekly"
      async
    ></script>
@stop
