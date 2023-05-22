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
        $bsc = array(
            '352093086366266',
            '359632102139695',
            '359632102139695_021222',
            '860264050856559',
            '860264050863753',
            '860264050864116',
            '860264050890814',
            '860264051968288',
            '860264055462585',
            '860264058610701',
            '860264058610701_021222',
            '866728060518544',
            '866728060519237',
            '866728060519237_021222',
            '866728061277918',
            '867648046909857',
            '867648046910707',
            '867648046911317',
            '867648047826969',
            '867648047826969_021222',
            '867648047841752',
            '867648048049835',
            '867648048074056',
            '867648048718405',
            '867648048718405_021222',
            '867648048718405_211122',
            '867648048788143'
        );

        foreach ($bsc as $value) {
            Schema::create('lacak_segment_'.$value, function (Blueprint $table) {
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
        $bsc = array(
            '352093086366266',
            '359632102139695',
            '359632102139695_021222',
            '860264050856559',
            '860264050863753',
            '860264050864116',
            '860264050890814',
            '860264051968288',
            '860264055462585',
            '860264058610701',
            '860264058610701_021222',
            '866728060518544',
            '866728060519237',
            '866728060519237_021222',
            '866728061277918',
            '867648046909857',
            '867648046910707',
            '867648046911317',
            '867648047826969',
            '867648047826969_021222',
            '867648047841752',
            '867648048049835',
            '867648048074056',
            '867648048718405',
            '867648048718405_021222',
            '867648048718405_211122',
            '867648048788143'
        );

        foreach ($bsc as $value) {
            Schema::dropIfExists('lacak_segment_'.$value);
        }
    }
}
