<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class ReportRencanaKerjaDetailTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            	=> $model->id,
            'tgl'        		=> $model->tgl,
            'shift_id'    		=> $model->shift_id,
            'shift_nama'   		=> $model->shift_nama,
            'lokasi_kode'   	=> $model->lokasi_kode,
            'lokasi_nama'   	=> $model->lokasi_nama,
            'lokasi_lsbruto'   	=> $model->lokasi_lsbruto,
            'lokasi_lsnetto'   	=> $model->lokasi_lsnetto,
            'lokasi_grup'       => $model->lokasi_grup,
            'aktivitas_kode'   	=> $model->aktivitas_kode,
            'aktivitas_nama'   	=> $model->aktivitas_nama,
            'nozzle_id'         => $model->nozzle_id,
            'nozzle_nama'       => $model->nozzle_nama,
            'volume'            => $model->volume,
            'unit_id'    		=> $model->unit_id,
            'unit_label'   		=> $model->unit_label,
            'unit_source_device_id'   		=> $model->unit_source_device_id,
            'operator_nama'   	=> $model->operator_nama,
            'mixing_operator_nama'     => $model->mixing_operator_nama,
            'driver_nama'   	=> $model->driver_nama,
            'kasie_nama'   		=> $model->kasie_nama,
            'kualitas'   		=> $model->kualitas,
            'ritase'   		=> $model->ritase,
            'par_1'   		=> $model->par_1,
            'par_2'   		=> $model->par_2,
            'par_3'   		=> $model->par_3,
            'par_4'   		=> $model->par_4,
            'par_5'   		=> $model->par_5,
            'par_6'   		=> $model->par_6,
            'hasil'   		=> $model->hasil,
            'kualitas_detail'   		=> $model->kualitas_detail,
        ];
    }
}
