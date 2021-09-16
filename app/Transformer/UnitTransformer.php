<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class UnitTransformer extends TransformerAbstract {
    public function transform($model) {
        return [
            'id'                    => $model->id,
            'kode'                  => $model->kode,
            'nama'    		        => $model->nama,
            'lacak_id'              => $model->lacak_id,
            'gps_updated'           => $model->gps_updated,
            'gps_signal_level'      => $model->gps_signal_level,
            'gps_location_lat'      => $model->gps_location_lat,
            'gps_location_lng'      => $model->gps_location_lng,
            'gps_speed'             => $model->gps_speed,
            'gps_heading'           => $model->gps_heading,
            'gps_alt'               => $model->gps_alt,
            'movement_status'       => $model->movement_status,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
