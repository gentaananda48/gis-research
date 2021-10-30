<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => ['check_app_version', 'api'],
    'namespace' => 'API',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('changePassword', 'AuthController@changePassword');
    Route::get('permission', 'AuthController@permission');
});

// USER
Route::group([
	'middleware' => ['check_app_version', 'api'],
    'namespace' => 'API',
    'prefix' => 'user'
], function ($router) {
    Route::get('list', 'UserController@list');
});

// UNIT
Route::group([
	'middleware' => ['check_app_version', 'api'],
    'namespace' => 'API',
    'prefix' => 'unit'
], function ($router) {
    Route::get('list', 'UnitController@list');
    Route::get('detail', 'UnitController@detail');
    Route::get('playback', 'UnitController@playback');
});

// LOKASI
Route::group([
	'middleware' => ['check_app_version', 'api'],
    'namespace' => 'API',
    'prefix' => 'lokasi'
], function ($router) {
    Route::get('list', 'LokasiController@list');
});

// Aktivitas
Route::group([
	'middleware' => ['check_app_version', 'api'],
    'namespace' => 'API',
    'prefix' => 'aktivitas'
], function ($router) {
    Route::get('list', 'AktivitasController@list');
});

// Alasan Pending
Route::group([
    'middleware' => ['check_app_version', 'api'],
    'namespace' => 'API',
    'prefix' => 'alasan_pending'
], function ($router) {
    Route::get('list', 'AlasanPendingController@list');
});

// Tindak Lanjut Pending
Route::group([
    'middleware' => ['check_app_version', 'api'],
    'namespace' => 'API',
    'prefix' => 'tindak_lanjut_pending'
], function ($router) {
    Route::get('list', 'TindakLanjutPendingController@list');
});

// RENCANA KERJA
Route::group([
    'middleware' 	=> ['check_app_version', 'api'],
    'namespace' 	=> 'API',
    'prefix' 		=> 'rencana_kerja'
], function ($router) {
    Route::get('list', 'RencanaKerjaController@list');
    Route::get('list2', 'RencanaKerjaController@list2');
    Route::get('list3', 'RencanaKerjaController@list3');
    Route::get('get_master_data', 'RencanaKerjaController@get_master_data');
    Route::get('detail', 'RencanaKerjaController@detail');
    Route::get('hasil', 'RencanaKerjaController@hasil');
    Route::get('unit', 'RencanaKerjaController@unit');
    Route::post('create', 'RencanaKerjaController@create');
    Route::post('update', 'RencanaKerjaController@update');
    Route::post('start', 'RencanaKerjaController@start_spraying');
    Route::get('monitor', 'RencanaKerjaController@monitor');
    Route::post('pending', 'RencanaKerjaController@pending_spraying');
    Route::post('finish', 'RencanaKerjaController@finish_spraying');
    Route::post('report', 'RencanaKerjaController@report_spraying');
    Route::get('summary', 'RencanaKerjaController@summary');
});

// LACAK
Route::group([
    'middleware'    => 'api',
    'namespace'     => 'API',
    'prefix'        => 'lacak'
], function ($router) {
    Route::post('create', 'LacakController@create');
});

// LAPORAN MASALAH
Route::group([
    'middleware'    => 'api',
    'namespace'     => 'API',
    'prefix'        => 'laporan_masalah'
], function ($router) {
    Route::get('list', 'LaporanMasalahController@list');
    Route::post('create', 'LaporanMasalahController@create');
    Route::post('update', 'LaporanMasalahController@update');
});

// PEMELIHARAAN
Route::group([
    'middleware'    => 'api',
    'namespace'     => 'API',
    'prefix'        => 'pemeliharaan'
], function ($router) {
    Route::get('list', 'PemeliharaanController@list');
    Route::post('create', 'PemeliharaanController@create');
    Route::post('start', 'PemeliharaanController@start_maintenance');
    Route::post('finish', 'PemeliharaanController@finish_maintenance');

});

// ORDER MATERIAL
Route::group([
    'middleware'    => 'api',
    'namespace'     => 'API',
    'prefix'        => 'order_material'
], function ($router) {
    Route::get('list', 'OrderMaterialController@list');
    Route::get('list2', 'OrderMaterialController@list2');
    Route::get('detail', 'OrderMaterialController@detail');
    Route::get('form_create', 'OrderMaterialController@form_create');
    Route::post('create', 'OrderMaterialController@create');
    Route::post('start', 'OrderMaterialController@start_order_material');
    Route::post('cancel', 'OrderMaterialController@cancel_order_material');
    Route::post('finish', 'OrderMaterialController@finish_order_material');

});
