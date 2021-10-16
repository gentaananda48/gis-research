<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class OrderMaterialTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            	=> $model->id,
            'tanggal'         	=> $model->tanggal,
            'lokasi_id'    		=> $model->lokasi_id,
            'lokasi_kode'   	=> $model->lokasi_kode,
            'lokasi_nama'   	=> $model->lokasi_nama,
            'aktivitas_id'  	=> $model->aktivitas_id,
            'aktivitas_kode'   	=> $model->aktivitas_kode,
            'aktivitas_nama'   	=> $model->aktivitas_nama,
            'unit_id'    		=> $model->unit_id,
            'unit_label'   		=> $model->unit_label,
            'operator_id'  		=> $model->operator_id,
            'operator_nama'   	=> $model->operator_nama,
            'ritase'   			=> $model->ritase,
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
