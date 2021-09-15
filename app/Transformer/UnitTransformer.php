<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class UnitTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            => $model->id,
            'kode'        	=> $model->kode,
            'nama'    		=> $model->nama,
            'lacak_id'          => $model->lacak_id,
            'movement_status'   => $model->movement_status,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
