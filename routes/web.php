<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/test', 'HomeController@test');
Route::get('/generate_geofence', 'HomeController@generate_geofence');
Route::get('/generate_report_v2', 'HomeController@generate_report_v2');
Route::get('/update_rk', 'HomeController@update_rk');
Route::get('/check_lokasi_rk', 'HomeController@check_lokasi_rk');
Route::get('/privacy', 'HomeController@privacy')->name('privacy');
Route::get('/check_geofence', 'HomeController@check_geofence');
Route::group(['middleware' => ['auth']], function () {

    // ------------------------------- ADMIN -------------------------------- //
    // ROLE
    Route::get('/admin/role', array('uses' => 'Admin\RoleController@index', 'as' => 'admin.role'));
    Route::get('/admin/role/get', array('uses' => 'Admin\RoleController@get_list', 'as' => 'admin.role.get'));
    Route::get('/admin/role/create', array('uses' => 'Admin\RoleController@create', 'as' => 'admin.role.create'));
    Route::post('/admin/role', array('uses' => 'Admin\RoleController@store', 'as' => 'admin.role.store'));
    Route::get('/admin/role/edit/{id}', array('uses' => 'Admin\RoleController@edit', 'as' => 'admin.role.edit'));
    Route::get('/admin/role/permission/{id}', array('uses' => 'Admin\RoleController@permission', 'as' => 'admin.role.permission'));
    Route::put('/admin/role/update/{id}', array('uses' => 'Admin\RoleController@update', 'as' => 'admin.role.update'));
    Route::put('/admin/role/permission/{id}', array('uses' => 'Admin\RoleController@updatePermission', 'as' => 'admin.role.permission.update'));
    Route::delete('/admin/role/delete/{id}', array('uses' => 'Admin\RoleController@destroy', 'as' => 'admin.role.destroy'));

    // USER
    Route::get('/admin/user', array('uses' => 'Admin\UserController@index', 'as' => 'admin.user'));
    Route::get('/admin/user/get', array('uses' => 'Admin\UserController@get_list', 'as' => 'admin.user.get'));
    Route::get('/admin/user/create', array('uses' => 'Admin\UserController@create', 'as' => 'admin.user.create'));
    Route::post('/admin/user', array('uses' => 'Admin\UserController@store', 'as' => 'admin.user.store'));
    Route::get('/admin/user/edit/{id}', array('uses' => 'Admin\UserController@edit', 'as' => 'admin.user.edit'));
    Route::put('/admin/user/update/{id}', array('uses' => 'Admin\UserController@update', 'as' => 'admin.user.update'));
    Route::delete('/admin/user/delete/{id}', array('uses' => 'Admin\UserController@destroy', 'as' => 'admin.user.destroy'));
    Route::put('/admin/user/activate/{id}', array('uses' => 'Admin\UserController@activate', 'as' => 'admin.user.activate'));
    Route::post('/admin/user/import', 'Admin\UserController@import')->name('admin.user.import');

    // SYSTEM CONFIGURATION
    Route::get('/admin/system_configuration', array('uses' => 'Admin\SystemConfigurationController@index', 'as' => 'admin.system_configuration'));
    Route::get('/admin/system_configuration/get', array('uses' => 'Admin\SystemConfigurationController@get_list', 'as' => 'admin.system_configuration.get'));
    Route::get('/admin/system_configuration/create', array('uses' => 'Admin\SystemConfigurationController@create', 'as' => 'admin.system_configuration.create'));
    Route::post('/admin/system_configuration', array('uses' => 'Admin\SystemConfigurationController@store', 'as' => 'admin.system_configuration.store'));
    Route::get('/admin/system_configuration/edit/{id}', array('uses' => 'Admin\SystemConfigurationController@edit', 'as' => 'admin.system_configuration.edit'));
    Route::put('/admin/system_configuration/update/{id}', array('uses' => 'Admin\SystemConfigurationController@update', 'as' => 'admin.system_configuration.update'));
    Route::delete('/admin/system_configuration/delete/{id}', array('uses' => 'Admin\SystemConfigurationController@destroy', 'as' => 'admin.system_configuration.destroy'));



    Route::get('/myprofile', array('uses' => 'Admin\UserController@myprofile', 'as' => 'myprofile'));
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/home', 'HomeController@home')->name('home');
    Route::get('/generate_report', 'HomeController@generate_report');


    // ------------------------------- MASTER -------------------------------- // 
    // UNIT
    Route::get('/master/unit', array('uses' => 'Master\UnitController@index', 'as' => 'master.unit'));
    Route::get('/master/unit/get', array('uses' => 'Master\UnitController@get_list', 'as' => 'master.unit.get'));
    Route::get('/master/unit/sync', array('uses' => 'Master\UnitController@sync', 'as' => 'master.unit.sync'));
    Route::get('/master/unit/track/{id}', array('uses' => 'Master\UnitController@track', 'as' => 'master.track'));
    Route::get('/master/unit/track_json/{id}', array('uses' => 'Master\UnitController@track_json', 'as' => 'master.track_json'));
    Route::get('/master/unit/playback/{id}', array('uses' => 'Master\UnitController@playback', 'as' => 'master.playback'));
    Route::get('/master/unit/playback2/{id}', array('uses' => 'Master\UnitController@playback2', 'as' => 'master.playback2'));
    Route::get('/master/unit/lokasi', array('uses' => 'Master\UnitController@lokasi', 'as' => 'master.lokasi'));

    // LOKASI
    Route::get('/master/lokasi', array('uses' => 'Master\LokasiController@index', 'as' => 'master.lokasi'));
    Route::get('/master/lokasi/get', array('uses' => 'Master\LokasiController@get_list', 'as' => 'master.lokasi.get'));
    Route::get('/master/lokasi/create', array('uses' => 'Master\LokasiController@create', 'as' => 'master.lokasi.create'));
    Route::post('/master/lokasi', array('uses' => 'Master\LokasiController@store', 'as' => 'master.lokasi.store'));
    Route::get('/master/lokasi/edit/{id}', array('uses' => 'Master\LokasiController@edit', 'as' => 'master.lokasi.edit'));
    Route::put('/master/lokasi/update/{id}', array('uses' => 'Master\LokasiController@update', 'as' => 'master.lokasi.update'));
    Route::delete('/master/lokasi/delete/{id}', array('uses' => 'Master\LokasiController@destroy', 'as' => 'master.lokasi.destroy')); 
    Route::post('/master/lokasi/import', 'Master\LokasiController@import_lokasi')->name('master.lokasi.import');
    Route::get('/master/lokasi/map/{id}', array('uses' => 'Master\LokasiController@map', 'as' => 'master.lokasi.map')); 
    Route::get('/master/lokasi/koordinat/{id}', array('uses' => 'Master\LokasiController@koordinat', 'as' => 'master.lokasi.koordinat'));
    Route::get('/master/lokasi/koordinat/{id}/get', array('uses' => 'Master\LokasiController@koordinat_get_list', 'as' => 'master.lokasi.koordinat.get'));

    // AKTIVITAS
    Route::get('/master/aktivitas', array('uses' => 'Master\AktivitasController@index', 'as' => 'master.aktivitas'));
    Route::get('/master/aktivitas/get', array('uses' => 'Master\AktivitasController@get_list', 'as' => 'master.aktivitas.get'));
    Route::get('/master/aktivitas/create', array('uses' => 'Master\AktivitasController@create', 'as' => 'master.aktivitas.create'));
    Route::post('/master/aktivitas', array('uses' => 'Master\AktivitasController@store', 'as' => 'master.aktivitas.store'));
    Route::get('/master/aktivitas/edit/{id}', array('uses' => 'Master\AktivitasController@edit', 'as' => 'master.aktivitas.edit'));
    Route::put('/master/aktivitas/update/{id}', array('uses' => 'Master\AktivitasController@update', 'as' => 'master.aktivitas.update'));
    Route::delete('/master/aktivitas/delete/{id}', array('uses' => 'Master\AktivitasController@destroy', 'as' => 'master.aktivitas.destroy'));
    Route::get('/master/aktivitas/parameter/{id}', array('uses' => 'Master\AktivitasController@parameter', 'as' => 'master.aktivitas.parameter'));
    Route::put('/master/aktivitas/parameter_update/{id}', array('uses' => 'Master\AktivitasController@parameter_update', 'as' => 'master.aktivitas.parameter_update'));
    
    // ALASAN PENDING
    Route::get('/master/alasan_pending', array('uses' => 'Master\AlasanPendingController@index', 'as' => 'master.alasan_pending'));
    Route::get('/master/alasan_pending/get', array('uses' => 'Master\AlasanPendingController@get_list', 'as' => 'master.alasan_pending.get'));
    Route::get('/master/alasan_pending/create', array('uses' => 'Master\AlasanPendingController@create', 'as' => 'master.alasan_pending.create'));
    Route::post('/master/alasan_pending', array('uses' => 'Master\AlasanPendingController@store', 'as' => 'master.alasan_pending.store'));
    Route::get('/master/alasan_pending/edit/{id}', array('uses' => 'Master\AlasanPendingController@edit', 'as' => 'master.alasan_pending.edit'));
    Route::put('/master/alasan_pending/update/{id}', array('uses' => 'Master\AlasanPendingController@update', 'as' => 'master.alasan_pending.update'));
    Route::delete('/master/alasan_pending/delete/{id}', array('uses' => 'Master\AlasanPendingController@destroy', 'as' => 'master.alasan_pending.destroy'));

    // TINDAK LANJUT PENDING
    Route::get('/master/tindak_lanjut_pending', array('uses' => 'Master\TindakLanjutPendingController@index', 'as' => 'master.tindak_lanjut_pending'));
    Route::get('/master/tindak_lanjut_pending/get', array('uses' => 'Master\TindakLanjutPendingController@get_list', 'as' => 'master.tindak_lanjut_pending.get'));
    Route::get('/master/tindak_lanjut_pending/create', array('uses' => 'Master\TindakLanjutPendingController@create', 'as' => 'master.tindak_lanjut_pending.create'));
    Route::post('/master/tindak_lanjut_pending', array('uses' => 'Master\TindakLanjutPendingController@store', 'as' => 'master.tindak_lanjut_pending.store'));
    Route::get('/master/tindak_lanjut_pending/edit/{id}', array('uses' => 'Master\TindakLanjutPendingController@edit', 'as' => 'master.tindak_lanjut_pending.edit'));
    Route::put('/master/tindak_lanjut_pending/update/{id}', array('uses' => 'Master\TindakLanjutPendingController@update', 'as' => 'master.tindak_lanjut_pending.update'));
    Route::delete('/master/tindak_lanjut_pending/delete/{id}', array('uses' => 'Master\TindakLanjutPendingController@destroy', 'as' => 'master.tindak_lanjut_pending.destroy'));
    
    // BAHAN
    Route::get('/master/bahan', array('uses' => 'Master\BahanController@index', 'as' => 'master.bahan'));
    Route::get('/master/bahan/get', array('uses' => 'Master\BahanController@get_list', 'as' => 'master.bahan.get'));
    Route::get('/master/bahan/create', array('uses' => 'Master\BahanController@create', 'as' => 'master.bahan.create'));
    Route::post('/master/bahan', array('uses' => 'Master\BahanController@store', 'as' => 'master.bahan.store'));
    Route::get('/master/bahan/edit/{id}', array('uses' => 'Master\BahanController@edit', 'as' => 'master.bahan.edit'));
    Route::put('/master/bahan/update/{id}', array('uses' => 'Master\BahanController@update', 'as' => 'master.bahan.update'));
    Route::delete('/master/bahan/delete/{id}', array('uses' => 'Master\BahanController@destroy', 'as' => 'master.bahan.destroy')); 
    Route::post('/master/bahan/import', 'Master\BahanController@import')->name('master.bahan.import');

    // REPORT PARAMETER
    Route::get('/master/report_parameter', array('uses' => 'Master\ReportParameterController@index', 'as' => 'master.report_parameter'));
    Route::get('/master/report_parameter/get', array('uses' => 'Master\ReportParameterController@get_list', 'as' => 'master.report_parameter.get'));
    Route::get('/master/report_parameter/create', array('uses' => 'Master\ReportParameterController@create', 'as' => 'master.report_parameter.create'));
    Route::post('/master/report_parameter', array('uses' => 'Master\ReportParameterController@store', 'as' => 'master.report_parameter.store'));
    Route::get('/master/report_parameter/edit/{id}', array('uses' => 'Master\ReportParameterController@edit', 'as' => 'master.report_parameter.edit'));
    Route::put('/master/report_parameter/update/{id}', array('uses' => 'Master\ReportParameterController@update', 'as' => 'master.report_parameter.update'));
    Route::delete('/master/report_parameter/delete/{id}', array('uses' => 'Master\ReportParameterController@destroy', 'as' => 'master.report_parameter.destroy'));

    // REPORT PARAMETER BOBOT
    Route::get('/master/report_parameter_bobot', array('uses' => 'Master\ReportParameterBobotController@index', 'as' => 'master.report_parameter_bobot'));
    Route::get('/master/report_parameter_bobot/get', array('uses' => 'Master\ReportParameterBobotController@get_list', 'as' => 'master.report_parameter_bobot.get'));
    Route::get('/master/report_parameter_bobot/create', array('uses' => 'Master\ReportParameterBobotController@create', 'as' => 'master.report_parameter_bobot.create'));
    Route::post('/master/report_parameter_bobot', array('uses' => 'Master\ReportParameterBobotController@store', 'as' => 'master.report_parameter_bobot.store'));
    Route::get('/master/report_parameter_bobot/edit/{id}', array('uses' => 'Master\ReportParameterBobotController@edit', 'as' => 'master.report_parameter_bobot.edit'));
    Route::put('/master/report_parameter_bobot/update/{id}', array('uses' => 'Master\ReportParameterBobotController@update', 'as' => 'master.report_parameter_bobot.update'));
    Route::delete('/master/report_parameter_bobot/delete/{id}', array('uses' => 'Master\ReportParameterBobotController@destroy', 'as' => 'master.report_parameter_bobot.destroy'));
    
    // REPORT PARAMETER STANDARD
    Route::get('/master/report_parameter_standard', array('uses' => 'Master\ReportParameterStandardController@index', 'as' => 'master.report_parameter_standard'));
    Route::get('/master/report_parameter_standard/get', array('uses' => 'Master\ReportParameterStandardController@get_list', 'as' => 'master.report_parameter_standard.get'));
    Route::get('/master/report_parameter_standard/create', array('uses' => 'Master\ReportParameterStandardController@create', 'as' => 'master.report_parameter_standard.create'));
    Route::post('/master/report_parameter_standard', array('uses' => 'Master\ReportParameterStandardController@store', 'as' => 'master.report_parameter_standard.store'));
    Route::get('/master/report_parameter_standard/edit/{id}', array('uses' => 'Master\ReportParameterStandardController@edit', 'as' => 'master.report_parameter_standard.edit'));
    Route::get('/master/report_parameter_standard/detail/{id}', array('uses' => 'Master\ReportParameterStandardController@detail', 'as' => 'master.report_parameter_standard.detail'));
    Route::put('/master/report_parameter_standard/update/{id}', array('uses' => 'Master\ReportParameterStandardController@update', 'as' => 'master.report_parameter_standard.update'));
    Route::delete('/master/report_parameter_standard/delete/{id}', array('uses' => 'Master\ReportParameterStandardController@destroy', 'as' => 'master.report_parameter_standard.destroy'));
    Route::put('/master/report_parameter_standard/detail_update/{id}', array('uses' => 'Master\ReportParameterStandardController@detail_update', 'as' => 'master.report_parameter_standard.detail_update'));

    // REPORT STATUS
    Route::get('/master/report_status', array('uses' => 'Master\ReportStatusController@index', 'as' => 'master.report_status'));
    Route::get('/master/report_status/get', array('uses' => 'Master\ReportStatusController@get_list', 'as' => 'master.report_status.get'));
    Route::get('/master/report_status/create', array('uses' => 'Master\ReportStatusController@create', 'as' => 'master.report_status.create'));
    Route::post('/master/report_status', array('uses' => 'Master\ReportStatusController@store', 'as' => 'master.report_status.store'));
    Route::get('/master/report_status/edit/{id}', array('uses' => 'Master\ReportStatusController@edit', 'as' => 'master.report_status.edit'));
    Route::put('/master/report_status/update/{id}', array('uses' => 'Master\ReportStatusController@update', 'as' => 'master.report_status.update'));
    Route::delete('/master/report_status/delete/{id}', array('uses' => 'Master\ReportStatusController@destroy', 'as' => 'master.report_status.destroy'));

    // NOZZLE
    Route::get('/master/nozzle', array('uses' => 'Master\NozzleController@index', 'as' => 'master.nozzle'));
    Route::get('/master/nozzle/get', array('uses' => 'Master\NozzleController@get_list', 'as' => 'master.nozzle.get'));
    Route::get('/master/nozzle/create', array('uses' => 'Master\NozzleController@create', 'as' => 'master.nozzle.create'));
    Route::post('/master/nozzle', array('uses' => 'Master\NozzleController@store', 'as' => 'master.nozzle.store'));
    Route::get('/master/nozzle/edit/{id}', array('uses' => 'Master\NozzleController@edit', 'as' => 'master.nozzle.edit'));
    Route::put('/master/nozzle/update/{id}', array('uses' => 'Master\NozzleController@update', 'as' => 'master.nozzle.update'));
    Route::delete('/master/nozzle/delete/{id}', array('uses' => 'Master\NozzleController@destroy', 'as' => 'master.nozzle.destroy')); 

    // VOLUME AIR
    Route::get('/master/volume_air', array('uses' => 'Master\VolumeAirController@index', 'as' => 'master.volume_air'));
    Route::get('/master/volume_air/get', array('uses' => 'Master\VolumeAirController@get_list', 'as' => 'master.volume_air.get'));
    Route::get('/master/volume_air/create', array('uses' => 'Master\VolumeAirController@create', 'as' => 'master.volume_air.create'));
    Route::post('/master/volume_air', array('uses' => 'Master\VolumeAirController@store', 'as' => 'master.volume_air.store'));
    Route::get('/master/volume_air/edit/{id}', array('uses' => 'Master\VolumeAirController@edit', 'as' => 'master.volume_air.edit'));
    Route::put('/master/volume_air/update/{id}', array('uses' => 'Master\VolumeAirController@update', 'as' => 'master.volume_air.update'));
    Route::delete('/master/volume_air/delete/{id}', array('uses' => 'Master\VolumeAirController@destroy', 'as' => 'master.volume_air.destroy')); 

    // ------------------------------- TRANSACTION -------------------------------- //
    // RENCANA KERJA
    Route::get('/transaction/rencana_kerja', array('uses' => 'Transaction\RencanaKerjaController@index', 'as' => 'transaction.rencana_kerja'));
    Route::get('/transaction/rencana_kerja/get', array('uses' => 'Transaction\RencanaKerjaController@get_list', 'as' => 'transaction.rencana_kerja.get'));
    Route::get('/transaction/rencana_kerja/show/{id}', array('uses' => 'Transaction\RencanaKerjaController@show', 'as' => 'transaction.rencana_kerja.show'));
    Route::post('/transaction/rencana_kerja/import', 'Transaction\RencanaKerjaController@import')->name('transaction.rencana_kerja.import');
    Route::get('/transaction/rencana_kerja/export', array('uses' => 'Transaction\RencanaKerjaController@export', 'as' => 'transaction.rencana_kerja.export'));
    Route::get('/transaction/rencana_kerja/playback/{id}', array('uses' => 'Transaction\RencanaKerjaController@playback', 'as' => 'transaction.rencana_kerja.playback'));
    Route::get('/transaction/rencana_kerja/download_template', array('uses' => 'Transaction\RencanaKerjaController@download_template', 'as' => 'transaction.rencana_kerja.download_template'));

    // LAPORAN MASALAH
    Route::get('/transaction/laporan_masalah', array('uses' => 'Transaction\LaporanMasalahController@index', 'as' => 'transaction.laporan_masalah'));
    Route::get('/transaction/laporan_masalah/get', array('uses' => 'Transaction\LaporanMasalahController@get_list', 'as' => 'transaction.laporan_masalah.get'));
    Route::get('/transaction/laporan_masalah/show/{id}', array('uses' => 'Transaction\LaporanMasalahController@show', 'as' => 'transaction.laporan_masalah.show'));

    // ORDER MATERIAL
    Route::get('/transaction/order_material', array('uses' => 'Transaction\OrderMaterialController@index', 'as' => 'transaction.order_material'));
    Route::get('/transaction/order_material/get', array('uses' => 'Transaction\OrderMaterialController@get_list', 'as' => 'transaction.order_material.get'));
    Route::get('/transaction/order_material/show/{id}', array('uses' => 'Transaction\OrderMaterialController@show', 'as' => 'transaction.order_material.show'));

    // PEMELIHARAAN
    Route::get('/transaction/pemeliharaan', array('uses' => 'Transaction\PemeliharaanController@index', 'as' => 'transaction.pemeliharaan'));
    Route::get('/transaction/pemeliharaan/get', array('uses' => 'Transaction\PemeliharaanController@get_list', 'as' => 'transaction.pemeliharaan.get'));
    Route::get('/transaction/pemeliharaan/show/{id}', array('uses' => 'Transaction\PemeliharaanController@show', 'as' => 'transaction.pemeliharaan.show'));
    // ------------------------------- REPORT -------------------------------- //
    // RENCANA KERJA
    Route::get('/report/rencana_kerja', array('uses' => 'Report\RencanaKerjaController@index', 'as' => 'report.rencana_kerja'));
    Route::get('/report/rencana_kerja/get', array('uses' => 'Report\RencanaKerjaController@get_list', 'as' => 'report.rencana_kerja.get'));
    Route::get('/report/rencana_kerja/summary/{id}', array('uses' => 'Report\RencanaKerjaController@summary', 'as' => 'report.rencana_kerja.summary'));
    Route::get('/report/rencana_kerja/export', array('uses' => 'Report\RencanaKerjaController@export', 'as' => 'report.rencana_kerja.export'));
    Route::get('/report/rencana_kerja/playback/{id}', array('uses' => 'Report\RencanaKerjaController@playback', 'as' => 'report.rencana_kerja.playback'));

     // RENCANA KERJA DETAIL
    Route::get('/report/rencana_kerja_detail', array('uses' => 'Report\RencanaKerjaDetailController@index', 'as' => 'report.rencana_kerja_detail'));
    Route::get('/report/rencana_kerja_detail/get', array('uses' => 'Report\RencanaKerjaDetailController@get_list', 'as' => 'report.rencana_kerja_detail.get'));
    Route::get('/report/rencana_kerja_detail/export', array('uses' => 'Report\RencanaKerjaDetailController@export', 'as' => 'report.rencana_kerja_detail.export'));

    // Conformity Unit
    Route::get('/summary/conformity_unit', array('uses' => 'SummaryReportVAT\ConformityUnitController@index', 'as' => 'summary.conformity_unit'));
    Route::get('/summary/conformity_unit/{id}', array('uses' => 'SummaryReportVAT\ConformityUnitController@show', 'as' => 'summary.conformity_unit.show'));
    Route::get('/summary/conformity_unit/{id1}/detail/{id2}', array('uses' => 'SummaryReportVAT\ConformityUnitController@detail', 'as' => 'summary.conformity_unit.detail'));
});


