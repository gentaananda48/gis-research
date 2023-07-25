<table class="table table-hover table-bordered rounded" width="100%" id="table-down">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>PG</th>
            <th>Unit</th>
            <th>Total Luasan (Ha)</th>
            <th>On Standar Speed</th>
            <th>On Standar Wing Kiri</th>
            <th>On Standar Wing Kanan</th>
            <th>On Standar Golden Time</th>
            <th>Lokasi</th>
            <th>Rencana Kerja</th>
            <th>Shift</th>
            <th>Jenis Aplikasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($report_conformities as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration  }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                <td class="text-center">{{ $item->pg }}</td>
                <td class="text-center">{{ $item->unit }}</td>
                <td class="text-center">{{ $item->total_spraying != 0 ? round($item->total_spraying/10000,2):0 }}</td>
                <td class="text-center" style="{{ $item->getStandardColor($item->speed_standar) }}">{{ $item->speed_standar }}%</td>
                <td class="text-center" style="{{ $item->getStandardColor($item->wing_kiri_standar) }}">{{ $item->avg_wing_kiri > 2 ? $item->wing_kiri_standar.'%':'N/A' }}</td>
                <td class="text-center" style="{{ $item->getStandardColor($item->wing_kanan_standar) }}">{{ $item->avg_wing_kanan > 2 ? $item->wing_kanan_standar.'%':'N/A' }}</td>
                <td class="text-center" style="{{ $item->getStandardColor($item->goldentime_standar) }}">{{ $item->goldentime_standar }}%</td>
                <td class="text-center">{{ $item->lokasi }}</td>
                <td class="text-center">{{ $item->shift != null && $item->activity != null ? 'Y' : 'N' }}</td>
                <td class="text-center">{{ $item->shift }}</td>
                <td class="text-center">{{ $item->activity }}</td>
            </tr>
        @empty
        <tr>
            <td colspan="100" class="text-center">No data available</td>
        </tr>
        @endforelse
    </tbody>
</table>