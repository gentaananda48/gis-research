<?php

namespace App\Transformer;
use League\Fractal\TransformerAbstract;
use App\Model\User;

class UserTransformer extends TransformerAbstract {
    public function transform($model) {
        
        return [
            'id'            => $model->id,
            'username'      => $model->username,
            'name'          => $model->name,
            'email'         => $model->email,
            'phone'         => $model->phone,
            'role_id'       => $model->role_id,
            'role_code'     => $model->role_code,
            'role_name'     => $model->role_name,
            'employee_id'   => $model->employee_id,
            'status'        => $model->status,
            'status_name'   => $model->status,
            'created_at'    => $model->created_at->format('Y-m-d H:i:s'),
            'created_by'    => $model->created_by,
            'updated_at'    => $model->updated_at->format('Y-m-d H:i:s'),
            'updated_by'    => $model->updated_by,
        ];
    }
}
