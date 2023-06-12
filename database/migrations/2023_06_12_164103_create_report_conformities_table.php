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
            $table->double('avg_speed')->default(0);
            $table->integer('speed_dibawah_standar')->default(0);
            $table->integer('speed_standar')->default(0);
            $table->integer('speed_diatas_standar')->default(0);
            $table->float('avg_wing_kiri',8,2)->default(0);
            $table->integer('wing_kiri_dibawah_standar')->default(0);
            $table->integer('wing_kiri_standar')->default(0);
            $table->integer('wing_kiri_diatas_standar')->default(0);
            $table->float('avg_wing_kanan',8,2)->default(0);
            $table->integer('wing_kanan_dibawah_standar')->default(0);
            $table->integer('wing_kanan_standar')->default(0);
            $table->integer('wing_kanan_diatas_standar')->default(0);
            $table->float('avg_goldentime',8,2)->default(0);
            $table->integer('goldentime_standar')->default(0);
            $table->integer('goldentime_tidak_standar')->default(0);
            $table->float('avg_spray',8,2)->default(0);
            $table->integer('spray_standar')->default(0);
            $table->integer('spray_tidak_standar')->default(0);
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
