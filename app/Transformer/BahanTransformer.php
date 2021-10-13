<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class BahanTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            => $model->id,
            'kode'        	=> $model->kode,
            'nama'    		=> $model->nama,
            'kategori'    	=> $model->kategori,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
