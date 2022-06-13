<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class ReportRencanaKerjaDetailTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'                    => $model->id,
            'rk_id'                 => $model->rk_id,
            'ritase'                => $model->ritase,
            'parameter_id'          => $model->parameter_id,
            'parameter_nama'   		=> $model->parameter_nama,
            'standard'              => $model->standard,
            'realisasi'             => $model->realisasi,
            'nilai'                 => $model->nilai,
            'bobot'                 => $model->bobot,
            'nilai_bobot'   		=> $model->nilai_bobot,
            'kualitas'              => $model->kualitas,
        ];
    }
}
