<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class ReportParameterStandardTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'                    => $model->id,
            'grup_aktivitas_id' 	=> $model->grup_aktivitas_id,
            'grup_aktivitas_nama'   => $model->grup_aktivitas_nama,
            'aktivitas_id'          => $model->aktivitas_id,
            'aktivitas_nama'        => $model->aktivitas_nama,
            'nozzle_id'             => $model->nozzle_id,
            'nozzle_nama'           => $model->nozzle_nama,
            'volume_id' 			=> $model->volume_id,
            'volume'                => $model->volume,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
