<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class CronLogTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            => $model->id,
            'name'        	=> $model->name,
            'status'   		=> $model->status,
            'remarks'    	=> $model->remarks,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
