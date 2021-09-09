<?php
namespace App\Center;
use App\Contract\FormatPaginate;

class GridCenter {
    use FormatPaginate;
    private $model;
    private $request;

    /**
     * GridCenter constructor.
     * @param $model
     */
    public function __construct($model, $request) {
        $this->model   = $model;
        $this->request = $request;
    }

    public function render($transformer = null) {
        if (array_key_exists('sort', $this->request)) {
            $sort = $this->request['sort'];
            if (is_array($sort)) {
                foreach ($sort as $key => $value) {
                    $this->model = $this->model->orderBy($key, $value);
                }
            }
        }

        $perRow = array_key_exists('rowCount',$this->request) ? $this->request['rowCount'] : 10;
        $current = array_key_exists('current',$this->request) ? $this->request['current'] : 1;
        $paginate = $this->model->paginate($perRow, $columns = array( '*' ), $pagename = 'current', $current);
        return $this->bootGrid($paginate,$transformer);
    }
}