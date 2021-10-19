<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class KoordinatLokasiTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            => $model->id,
            'group'         => $model->group,
            'lokasi'         => $model->lokasi,
            'bagian'         => $model->bagian,
            'posnr'         => $model->posnr,
            'long'          => $model->long,
            'latd'          => $model->latd, 
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}

