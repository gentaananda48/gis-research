<?php

namespace App\Imports;

use App\Model\Bahan;
use App\Model\Aktivitas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BahanImport implements ToCollection, WithHeadingRow
{
    function __construct() {}

    public function collection(Collection $rows) {
        set_time_limit(0);
        DB::beginTransaction();
        try {
            foreach ($rows as $k=>$row){
                $kode 		= $row["kode"];
                $nama 		= $row["nama"];
                $list_kategori_nama = !empty($row["kategori"]) ? explode(',', $row["kategori"]) : [];
                $list_kategori_id = [];
                foreach($list_kategori_nama as $v){
                	$akt = Aktivitas::where('nama', $v)->first(['id']);
                	if($akt!=null) {
                		$list_kategori_id[] = $akt->id;
                	}
                }
                $kategori 	= implode(',', $list_kategori_id);
                $uom 		= $row["uom"];

                $bahan 		= new Bahan;
                $bahan->kode 		= $kode;
                $bahan->nama 		= $nama;
                $bahan->kategori 	= $kategori;
                $bahan->uom 		= $uom;
                $bahan->save();
            }
            DB::commit();
        } catch(Exception $e){
            DB::rollback(); 
        }
    }
}
