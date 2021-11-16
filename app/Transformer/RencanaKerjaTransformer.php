<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class RencanaKerjaTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            	=> $model->id,
            'tgl'        		=> $model->tgl,
            'shift_id'    		=> $model->shift_id,
            'shift_nama'   		=> $model->shift_nama,
            'lokasi_id'    		=> $model->lokasi_id,
            'lokasi_kode'   	=> $model->lokasi_kode,
            'lokasi_nama'   	=> $model->lokasi_nama,
            'lokasi_lsbruto'   	=> $model->lokasi_lsbruto,
            'lokasi_lsnetto'   	=> $model->lokasi_lsnetto,
            'lokasi_grup'       => $model->lokasi_grup,
            'aktivitas_id'  	=> $model->aktivitas_id,
            'aktivitas_kode'   	=> $model->aktivitas_kode,
            'aktivitas_nama'   	=> $model->aktivitas_nama,
            'nozzle_id'         => $model->nozzle_id,
            'nozzle_nama'       => $model->nozzle_nama,
            'volume_id'         => $model->volume_id,
            'volume'            => $model->volume,
            'unit_id'    		=> $model->unit_id,
            'unit_label'   		=> $model->unit_label,
            'unit_source_device_id'   		=> $model->unit_source_device_id,
            'operator_id'  		=> $model->operator_id,
            'operator_nama'   	=> $model->operator_nama,
            'mixing_operator_id'       => $model->mixing_operator_id,
            'mixing_operator_nama'     => $model->mixing_operator_nama,
            'driver_id'  		=> $model->kasie_id,
            'driver_nama'   	=> $model->driver_nama,
            'kasie_id'  		=> $model->kasie_id,
            'kasie_nama'   		=> $model->kasie_nama,
            'status_id'    		=> $model->status_id,
            'status_nama'  		=> $model->status_nama,
            'om_status_id'      => $model->om_status_id,
            'om_status_nama'    => $model->om_status_nama,
            'created_at'    	=> $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    	=> $model->created_by,
            'updated_at'    	=> $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    	=> $model->updated_by,
        ];
    }
}
