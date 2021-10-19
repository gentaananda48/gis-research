<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class ReportStatusTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            => $model->id,
            'status'    	=> $model->status,
            'range_1'    	=> $model->range_1,
            'range_2'       => $model->range_2,
            'icon'    		=> $model->icon,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
