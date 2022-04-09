<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class LokasiTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'            => $model->id,
            'kode'         => $model->kode,
            'nama'         => $model->nama,
            'grup'         => $model->grup,
            'wilayah'         => $model->wilayah,
            'lsbruto'       => number_format($model->lsbruto,2),
            'lsnetto'       => number_format($model->lsnetto,2),
            'status'       => $model->status,
            'map_topleft'       => $model->map_topleft,
            'map_bottomright'   => $model->map_bottomright,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}

