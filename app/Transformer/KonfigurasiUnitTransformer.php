<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class KonfigurasiUnitTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id' 					=> $model->id,
            'unit' 					=> $model->unit,
            'debit_kiri'         	=> $model->debit_kiri,
            'debit_kanan'         	=> $model->debit_kanan,
            'koefisien_sayap_kiri' 	=> $model->koefisien_sayap_kiri,
            'koefisien_sayap_kanan' => $model->koefisien_sayap_kanan,
            'minimum_spray_kiri' 	=> $model->minimum_spray_kiri,
            'minimum_spray_kanan' 	=> $model->minimum_spray_kanan,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}

