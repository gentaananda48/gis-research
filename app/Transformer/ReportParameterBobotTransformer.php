<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class ReportParameterBobotTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            => $model->id,
            'report_parameter_id'    		=> $model->report_parameter_id,
            'report_parameter_nama'    		=> $model->report_parameter_nama,
            'grup_aktivitas_id'    			=> $model->grup_aktivitas_id,
            'grup_aktivitas_nama'    		=> $model->grup_aktivitas_nama,
            'bobot'    		=> $model->bobot,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
