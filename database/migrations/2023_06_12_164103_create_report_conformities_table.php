<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportConformitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_conformities', function (Blueprint $table) {
            $table->increments('id');
            $table->date('tanggal');
            $table->string('pg');
            $table->string('wilayah');
            $table->string('lokasi');
            $table->string('unit');
            $table->string('activity')->nullable();
            $table->string('shift')->nullable();
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
            $table->double('total_luasan',16,2)->default(0);
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
        Schema::dropIfExists('report_conformities');
    }
}
