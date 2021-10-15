<?php

namespace App\Imports;

use App\Model\RencanaKerja;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Model\Shift;


class RencanaKerjaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    { 
        // dd($row);    
        $tgl = ($row["tanggal"] - 25569) * 86400;
            return new RencanaKerja([
                'tgl'             => gmdate('Y-m-d',$tgl),
                'shift_nama'      => $row["shift"],
                'lokasi_nama'     => $row["lokasi"],
                'lokasi_lsbruto'  => $row["luas_bruto"],
                'lokasi_lsnetto'  => $row["luas_netto"],
                'aktivitas_nama'  => $row["aktivitas"],
                'unit_label'      => $row["unit"],
                'operator_nama'   => $row["operator"],
                'driver_nama'     => $row["driver"],
                'kasie_nama'      => $row["kasie"],
                'status_nama'     => $row["status"],
            ]);
        
    }
}
