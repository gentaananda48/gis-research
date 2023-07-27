<div class="box-header text-center">
    <h3><strong>Detail Per Ritase</strong></h3>
</div>
<div class="box-body">
    <table class="table table-hover table-bordered rounded" width="100%" id="table-down">
        <thead>
            <tr>
                <th>Ritase</th>
                <th>Kecepatan Operasi</th>
                <th>Golden Time</th>
                <th>Waktu Spray</th>
                <th>Wing Level Kiri</th>
                <th>Wing Level Kanan</th>
                <th>Suhu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($list_rrk as $v)
                    <tr>
                            <td class="text-center">{{ $v->ritase }}</td>
                            @foreach($header as $k2 => $v2)
                                @php
                                    $param = 'parameter_'.$k2;
                                @endphp
                                    @if ($k2 == 4 || $k2 == 5 || $k2 == 6)
                                            <td class="text-center">{{ doubleval($v->$param) <= 2 ? 'N/A' : $v->$param }}</td>
                                    @else
                                            <td class="text-center">{{ $v->$param }}</td>
                                    @endif
                            @endforeach
                    </tr>
            @empty
            <tr>
                <td colspan="100" class="text-center">No data available</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>