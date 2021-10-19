<?php

namespace App\Imports;

use App\Model\lokasi;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LokasiImport implements ToModel, WithHeadingRow
{
    /*** 
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new lokasi([
            'kode'     => $row["kode"],
            'nama'      => $row["nama"],
            'grup'      => $row["grup"],
            'wilayah'   => $row["wilayah"],
            'lsbruto'   => $row["luas_bruto"],
            'lsnetto'   => $row["luas_netto"],
            'status'   => $row["status"],
        ]);
    }
}
