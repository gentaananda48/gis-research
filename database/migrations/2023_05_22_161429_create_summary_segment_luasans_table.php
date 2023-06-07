<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummarySegmentLuasansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summary_segment_luasan', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lacak_segment_id')->unsigned();
            $table->datetime('tgl')->nullable();
            $table->string('pg_nama')->nullable();
            $table->string('lokasi_kode')->nullable();
            $table->string('unit_label')->nullable();
            $table->double('luasan_m2')->nullable();
            $table->double('total_luasan_m2')->nullable();
            $table->datetime('waktu_spray')->nullable();
            $table->double('speed_standard')->nullable();
            $table->double('speed_dibawah_standard')->nullable();
            $table->double('speed_diatas_standard')->nullable();
            $table->double('avg_speed')->nullable();
            $table->double('arm_height_left_standard')->nullable();
            $table->double('arm_height_left_dibawah_standard')->nullable();
            $table->double('arm_height_left_diatas_standard')->nullable();
            $table->double('avg_height_left')->nullable();
            $table->double('arm_height_right_standard')->nullable();
            $table->double('arm_height_right_dibawah_standard')->nullable();
            $table->double('arm_height_right_diatas_standard')->nullable();
            $table->double('avg_arm_height_right')->nullable();
            $table->double('temperature_standard')->nullable();
            $table->double('temperature_not_standard')->nullable();
            $table->double('gloden_time_good')->nullable();
            $table->double('gloden_time_poor')->nullable();
            $table->double('ritase')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('summary_segment_luasan');
    }
}
