<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class RoleTransformer extends TransformerAbstract {
    public function transform($model) {
        
        return [
            'id'            => $model->id,
            'code'          => $model->code,
            'name'          => $model->name,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
