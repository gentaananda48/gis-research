<?php

namespace App\Contract;
use Spatie\Fractal\Fractal;
use League\Fractal\Manager;

Trait FormatPaginate {
    public function bootGrid($model, $transformer = NULL) {
        $grid = [
            'total'         => $model->total(),
            'rowCount'      => $model->perPage(),
            'current'       => $model->currentPage(),
            'last_page'     => $model->lastPage(),
            'next_page_url' => $model->nextPageUrl(),
            'prev_page_url' => $model->previousPageUrl(),
            'from'          => $model->firstItem(),
            'to'            => $model->lastItem()
        ];
        if ( ! is_null( $transformer ) ) {
            $items = new Fractal(new Manager());
            $result = $items->collection( $model->items(), $transformer )->toArray();
            $grid['rows'] = $result['data'];
        } else {
            $grid['rows'] = $model->items();
        }
        return $grid;
    }
}