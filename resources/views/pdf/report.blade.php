<!DOCTYPE html>
<html>
<head>
    <title>Poor Quality Report</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid black;
            border-spacing: 0;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border: 1px solid black;
            border-spacing: 0;
            vertical-align: top;
            font-size: 14px;
            font-weight: normal;
        }
        th {
            background-color: #ddd;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Poor Quality Report</h1>
    <table>
        <thead>
            <tr>
                <th>Aktivitas</th>
                <th>Nama Unit</th>
                <th>Kualitas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row->pg }}</td>
                <td>{{ $row->name_unit }}</td>
                <td>{{ $row->kualitas }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>