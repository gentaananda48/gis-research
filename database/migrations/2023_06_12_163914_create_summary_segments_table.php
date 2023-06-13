<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummarySegmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summary_segments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unit');
            $table->string('segment');
            $table->string('lokasi');
            $table->double('total_luasan',16,0)->default(0);
            $table->dateTime('created_date');
            $table->double('avg_speed',8,2)->default(0);
            $table->float('speed_dibawah_standar')->default(0);
            $table->float('speed_standar')->default(0);
            $table->float('speed_diatas_standar')->default(0);
            $table->double('avg_wing_kiri',8,2)->default(0);
            $table->float('wing_kiri_dibawah_standar')->default(0);
            $table->float('wing_kiri_standar')->default(0);
            $table->float('wing_kiri_diatas_standar')->default(0);
            $table->double('avg_wing_kanan',8,2)->default(0);
            $table->float('wing_kanan_dibawah_standar')->default(0);
            $table->float('wing_kanan_standar')->default(0);
            $table->float('wing_kanan_diatas_standar')->default(0);
            $table->double('avg_goldentime',8,2)->default(0);
            $table->float('goldentime_standar')->default(0);
            $table->float('goldentime_tidak_standar')->default(0);
            $table->double('avg_spray',8,2)->default(0);
            $table->float('spray_standar')->default(0);
            $table->float('spray_tidak_standar')->default(0);
            $table->float('total_data_point')->default(0);
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
        Schema::dropIfExists('summary_segments');
    }
}
