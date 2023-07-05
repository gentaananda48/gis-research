<!DOCTYPE html>
<html>
<head>
    <title>Daily Report VAT</title>
</head>
<body>
    <h1>Daily Report VAT</h1>

    <table>
        <thead>
            <tr>
                @foreach($emailData['tableHeaders'] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($emailData['tableData'] as $row)
                <tr>
                    <td>{{ $row->no }}</td>
                    <td>{{ $row->unit }}</td>
                    <td>{{ $row->shift }}</td>
                    <td>{{ $row->location }}</td>
                    <td>{{ $row->activity }}</td>
                    <td>{{ $row->rencana_kerja }}</td>
                    <td>{{ $row->speed_on_standard }}</td>
                    <td>{{ $row->wing_left_on_standard }}</td>
                    <td>{{ $row->wing_right_on_standard }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
