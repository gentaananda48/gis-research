<?php

namespace App\Imports;

use App\Model\Lokasi;
use App\Model\KoordinatLokasi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class LokasiImport implements ToCollection, WithHeadingRow
{
    function __construct() {}

    public function collection(Collection $rows) {
        $prev_memory_limit = ini_get('memory_limit');
        $prev_max_execution_time = ini_get('max_execution_time');
        ini_set('memory_limit', '-1' );
        ini_set('max_execution_time', -1);
        DB::beginTransaction();
        // lokasi::query()->truncate();
        // Koordinatlokasi::query()->truncate();
        DB::table('lokasi')->delete();
        DB::table('koordinat_lokasi')->delete();
        try {
            $bagian = [];
            $lokasi_id = 1;
            $koordinat_lokasi_id = 1;
            foreach ($rows as $k=>$row){
                $kode           = $row["kode"];
                $nama           = $row["nama"];
                $grup           = $row["grup"];
                $wilayah        = $row["wilayah"];
                $lsbruto        = $row["luas_bruto"];
                $lsnetto        = $row["luas_netto"];
                $map_topleft            = !empty($row["map_topleft"])? $row["map_topleft"] : null;
                $map_bottomright        = !empty($row["map_bottomright"]) ? $row["map_bottomright"] : null;
                $geofence       = $row["geofence"];
                $lok = new Lokasi;
                $lok->id            = $lokasi_id;
                $lok->kode          = $kode;
                $lok->nama          = $nama;
                $lok->grup          = $grup;
                $lok->wilayah       = $wilayah;
                $lok->lsbruto       = $lsbruto;
                $lok->lsnetto       = $lsnetto;
                $lok->map_topleft       = $map_topleft;
                $lok->map_bottomright   = $map_bottomright;
                $lok->status        = 'A';
                $lok->save();
                $lokasi_id++;

                if(array_key_exists($kode, $bagian)) {
                    $bagian[$kode] += 1;
                } else {
                    $bagian[$kode] = 1;
                }
                $list_geoloc = explode(' ', trim($geofence));
                $i = 1;
                foreach($list_geoloc AS $geoloc) {
                    if(empty($geoloc)) {
                        continue;
                    }
                    $arr_geoloc = explode(',', $geoloc);
                    if(count($arr_geoloc)<2){
                        continue;
                    }
                    $koor_lok           = new KoordinatLokasi;
                    $koor_lok->id       = $koordinat_lokasi_id;
                    $koor_lok->grup     = $lok->grup;
                    $koor_lok->lokasi   = $lok->kode;
                    $koor_lok->bagian   = $bagian[$kode];
                    $koor_lok->posnr    = $i;
                    $koor_lok->long     = $arr_geoloc[0];
                    $koor_lok->latd     = $arr_geoloc[1];
                    $koor_lok->save();
                    $i++;
                    $koordinat_lokasi_id++;
                }
            }
            $list_koordinat_lokasi = KoordinatLokasi::orderBy('lokasi', 'ASC')
                ->orderBy('bagian', 'ASC')
                ->orderBy('posnr', 'ASC')
                ->get();
            $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT';
            Redis::set($cache_key, json_encode($list_koordinat_lokasi));
            DB::commit();
        } catch(Exception $e){
            DB::rollback(); 
        }
        ini_set('memory_limit', $prev_memory_limit);
        ini_set('max_execution_time', $prev_max_execution_time);
    }
}
