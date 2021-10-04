<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class UnitTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'                    => $model->id,
            'label'                 => $model->label,
            'group_id'              => $model->group_id,
            'source_id'             => $model->source_id,
            'source_device_id'      => $model->source_device_id,
            'source_model'          => $model->source_model,
            'source_phone'          => $model->source_phone,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
