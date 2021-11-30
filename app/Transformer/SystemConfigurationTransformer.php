<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class SystemConfigurationTransformer extends TransformerAbstract {
    public function transform($model) {
        
        return [
            'id'            => $model->id,
            'code'          => $model->code,
            'description' 	=> $model->description,
            'value' 		=> $model->value,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
