<!DOCTYPE html>
<html>
<head>
    <title>Daily Report VAT</title>
    <style>
        /* Add some basic styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
        }
        h1 {
            color: #333333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #ffffff;
            border: 1px solid #cccccc;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #cccccc;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Daily Report VAT</h1>
    <h4>Yth <br> Leader VAT Championship <br> Perlu kami informasikan hasil spraying yang dilakukan </h4>
    <table>
        <thead>
            <tr>
                @foreach($emailData['tableHeaders'] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
      <tbody>
        @foreach($emailData['tableData'] as $key => $row)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $row->unit }}</td>
                <td>{{ $row->pg }}</td>
                <td>{{ $row->shift }}</td>
                <td>{{ $row->lokasi }}</td>
                <td>{{ $row->activity }}</td>
                <td>{{ $row->speed_standar }}</td>
                <td>{{ $row->wing_kiri_standar }}</td>
                <td>{{ $row->wing_kanan_standar }}</td>
            </tr>
        @endforeach
    </tbody>
    </table>
    <br>
    <p>Demikian informasi yang dapat kami sampaikan, bila ada pertanyaan dapat menghubungi kami kembali</p>
    <p>Untuk area PG1 : Al Hamid Yusuf (wa.me/6285381289985)</p>
    <p>Untuk area PG2 : Riyan Chandra Kurniawan (wa.me/6289629125290)</p>
    <p>Untuk area PG3 : Doni Agus Adila (wa.me/628991265917)</p>
    <br>
    <p>Terima kasih untuk tetap menggunakan tablet saat aplikasi Spraying App GGP.</p>
    <hr>
    <br>
    <p>--Digital Innovation--</p>
</body>
</html>
