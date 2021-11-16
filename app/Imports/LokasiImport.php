<?php

namespace App\Imports;

use App\Model\lokasi;
use App\Model\Koordinatlokasi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LokasiImport implements ToCollection, WithHeadingRow
{
    function __construct() {}

    public function collection(Collection $rows) {
        set_time_limit(0);
        DB::beginTransaction();
        lokasi::query()->truncate();
        Koordinatlokasi::query()->truncate();
        try {
            $bagian = [];
            foreach ($rows as $row){
                $kode           = $row["kode"];
                $nama           = $row["nama"];
                $grup           = $row["grup"];
                $wilayah        = $row["wilayah"];
                $lsbruto        = $row["luas_bruto"];
                $lsnetto        = $row["luas_netto"];
                $geofence       = $row["geofence"];
                $lok = new Lokasi;
                $lok->kode          = $kode;
                $lok->nama          = $nama;
                $lok->grup          = $grup;
                $lok->wilayah       = $wilayah;
                $lok->lsbruto       = $lsbruto;
                $lok->lsnetto       = $lsnetto;
                $lok->status        = 'A';
                $lok->save();

                if(array_key_exists($kode, $bagian)) {
                    $bagian[$kode] += 1;
                } else {
                    $bagian[$kode] = 1;
                }
                $list_geoloc = explode(' ', $geofence);
                $i = 1;
                foreach($list_geoloc AS $geoloc) {
                    if(empty($geoloc)) {
                        continue;
                    }
                    $arr_geoloc = explode(',', $geoloc);
                    $koor_lok           = new Koordinatlokasi;
                    $koor_lok->grup     = $lok->grup;
                    $koor_lok->lokasi   = $lok->kode;
                    $koor_lok->bagian   = $bagian[$kode];
                    $koor_lok->posnr    = $i;
                    $koor_lok->long     = $arr_geoloc[0];
                    $koor_lok->latd     = $arr_geoloc[1];
                    $koor_lok->save();
                    $i++;
                }
            }
            DB::commit();
        } catch(Exception $e){
            DB::rollback(); 
        }
    }
}
