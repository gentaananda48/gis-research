<?php
namespace App\Event;
use App\Model\Log;

class LogEvent {
    private $model;
    private $event;

    public function __construct($model, $event) {
        $this->model = $model;
        $this->event = $event;
    }

    public function save() {
        $exc_log = array('logs');
        if (!in_array($this->model->getTable(), $exc_log)) {
            Log::create([
                'reference_type' => $this->model->getTable(),
                'reference_id' => $this->model->getKey(),
                //'object' => ($this->event == 'update') ? json_encode($this->model->getOriginal()) : json_encode($this->model),
                'object' => json_encode($this->model),
                'event' => $this->event,
                'created_at' => time()
            ]);
        }
    }
}
