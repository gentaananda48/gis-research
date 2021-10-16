<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class LaporanMasalahTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            	=> $model->id,
            'tanggal'        	=> $model->tanggal,
            'unit_id'    		=> $model->unit_id,
            'unit_label'   		=> $model->unit_label,
            'lokasi_id'    		=> $model->lokasi_id,
            'lokasi_kode'   	=> $model->lokasi_kode,
            'lokasi_nama'   	=> $model->lokasi_nama,
            'laporan'  			=> $model->laporan,
            'driver_id'  		=> $model->kasie_id,
            'driver_nama'   	=> $model->driver_nama,
            'kasie_id'  		=> $model->kasie_id,
            'kasie_nama'   		=> $model->kasie_nama,
            'status_id'    		=> $model->status_id,
            'status_nama'  		=> $model->status_nama,
            'created_at'    	=> $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    	=> $model->created_by,
            'updated_at'    	=> $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    	=> $model->updated_by,
        ];
    }
}
