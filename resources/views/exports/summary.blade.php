
<table class="table table-hover table-bordered" width="100%">
        <thead>
            <tr>
                <th>NO</th>
                <th>PG</th>
                <th>Unit</th>
                <th>Tanggal</th>
                <th>Speed standar</th>
                <th>Speed dibawah standar</th>
                <th>Speed diatas standar</th>
                <th>Wing Kiri standar</th>
                <th>Wing Kiri dibawah standar</th>
                <th>Wing Kiri ditas standar</th>
                <th>Wing Kanan standar</th>
                <th>Wing Kanan dibawah standar</th>
                <th>Wing Kanan diatas standar</th>
                <th>Golden Time standar</th>
                <th>Golden Time tidak standar</th>
                <th>Suhu standar</th>
                <th>Suhu tidak standar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($summary as $key => $item)
            <tr>
                <td class="text-center">{{ $loop->iteration}}</td>
                <td>{{ $item->pg }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $date }}</td>
                <td>{{ $item->speed_standar}}</td>
                <td>{{ $item->speed_dibawah_standar}}</td>
                <td>{{ $item->speed_diatas_standar}}</td>
                <td>{{ $item->wing_kiri_standar}}</td>
                <td>{{ $item->wing_kiri_dibawah_standar}}</td>
                <td>{{ $item->wing_kiri_diatas_standar}}</td>
                <td>{{ $item->wing_kanan_standar}}</td>
                <td>{{ $item->wing_kanan_dibawah_standar}}</td>
                <td>{{ $item->wing_kanan_diatas_standar}}</td>
                <td>{{ $item->goldentime_standar}}</td>
                <td>{{ $item->goldentime_tidak_standar}}</td>
                <td>{{ $item->suhu_standar}}</td>
                <td>{{ $item->suhu_tidak_standar}}</td>
            </tr>
            @empty
            <tr>
                <td colspan="100" class="text-center">No data available</td>
            </tr>
            @endforelse
        </tbody>
</table>