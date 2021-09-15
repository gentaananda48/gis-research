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

//SURAT JALAN
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
    Route::post('create', 'RencanaKerjaController@create');
    Route::post('update', 'RencanaKerjaController@update');
});
