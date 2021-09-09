<?php

namespace App\Event;
use Illuminate\Support\Facades\Auth;

class GlobalEventObserver {
    private $username;

    public function __construct() {
        $this->username = Auth::check()?Auth::user()->email:'';
    }
    public function creating($model) {
        if(!empty($this->username)){
            if ($model->getTable() == 'logs') {
                $model->created_by = $this->username;
            } else {
                $model->created_by = !empty($model->created_by)?$model->created_by:$this->username;
                $model->updated_by = !empty($model->updated_by)?$model->updated_by:$this->username;
            }
        }
    }

    public function saving($model) {
        if(!empty($this->username)){
            if ($model->getTable() == 'logs') {
                $model->created_by = $this->username;
            } else {
                $model->updated_by = (!empty($model->updated_by) and $model->updated_by != $model->getOriginal()['updated_by'])?$model->updated_by:$this->username;
            }
        }
    }

    public function updating($model) {
        if(!empty($this->username)){
            if ($model->getTable() == 'logs') {
                $model->created_by = $this->username;
            } else {
                $model->updated_by = (!empty($model->updated_by) and $model->updated_by != $model->getOriginal()['updated_by'])?$model->updated_by:$this->username;
            }
        }
    }

    public function created($model) {
        $log = new LogEvent($model, 'insert');
        $log->save();
    }

    public function updated($model) {
        $log = new LogEvent($model, 'update');
        $log->save();
    }

    public function deleted($model) {
        $log = new LogEvent($model, 'delete');
        $log->save();
    }
}
