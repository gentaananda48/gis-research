<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLacakSegmentBscTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $list_unit_table = [];
        $get_label = DB::table('unit')->get();

        foreach($get_label as $label) {
            $table_name2 = "lacak_segment_".str_replace('-', '_', trim($label->box_id));
            array_push($list_unit_table, $table_name2);
        }

        $label_unit = array_count_values($list_unit_table);

        foreach (array_keys($label_unit) as $value) {
            Schema::create($value, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('lacak_bsc_id')->unsigned();
                $table->string('kode_lokasi');
                $table->string('segment');
                $table->string('overlapping_route');
                $table->string('overlapping_left');
                $table->string('overlapping_right');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $list_unit_table = [];
        $get_label = DB::table('unit')->get();

        foreach($get_label as $label) {
            $table_name2 = "lacak_segment_".str_replace('-', '_', trim($label->box_id));
            array_push($list_unit_table, $table_name2);
        }
        
        $label_unit = array_count_values($list_unit_table);


        foreach (array_keys($label_unit) as $value) {
            Schema::dropIfExists($value);
        }
    }
}
