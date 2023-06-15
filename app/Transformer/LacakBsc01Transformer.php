<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;

class LacakBsc01Transformer extends TransformerAbstract {
    public function transform($model) {
        
        return [
            'id' => $model->id,
            'latitude' => $model->latitude,
            'longitude' => $model->longitude,
            'speed' => $model->speed,
            'altitude' => $model->altitude,
            'arm_height_left' => $model->arm_height_left,
            'arm_height_right' => $model->arm_height_right,
            'temperature_left' => $model->temperature_left,
            'temperature_right' => $model->temperature_right,
            'pump_switch_main' => $model->pump_switch_main,
            'pump_switch_left' => $model->pump_switch_left,
            'pump_switch_right' => $model->pump_switch_right,
            'flow_meter_left' => $model->flow_meter_left,
            'flow_meter_right' => $model->flow_meter_right,
            'tank_level' => $model->tank_level,
            'oil' => $model->oil,
            'gas' => $model->gas,
            'homogenity' => $model->homogenity,
            'bearing' => $model->bearing,
            'microcontroller_id' => $model->microcontroller_id,
            'utc_timestamp' => $model->utc_timestamp,
            'created_at' => $model->created_a->format('Y-m-d H:i:s'),
            'box_id' => $model->box_id,
            'unit_label' => $model->unit_label,
            'source_device_id' => $model->source_device_id,
            'lokasi_kode' => $model->lokasi_kode,
            'processed' => $model->processed,
            'report_date' => $model->report_date->format('Y-m-d'),
            'device_timestamp' => $model->device_timestamp,
        ];
    }
}
