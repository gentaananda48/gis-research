<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class PemeliharaanTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            	=> $model->id,
            'tanggal'         	=> $model->tanggal,
            'unit_id'    		=> $model->unit_id,
            'unit_label'   		=> $model->unit_label,
            'perbaikan'   		=> $model->perbaikan,
            'laporan_driver'   	=> $model->laporan_driver,
            'tenisi_id'  		=> $model->teknisi_id,
            'teknisi_nama'   	=> $model->teknisi_nama,
            'tanggal_mulai'    	=> $model->tanggal_mulai,
            'tanggal_selesai'  	=> $model->tanggal_selesai,
            'catatan_teknisi'   => $model->catatan_teknisi,
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
