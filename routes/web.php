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

Route::get('/test/{uid}', 'HomeController@test');
Route::group(['middleware' => ['auth']], function () {

    // ------------------------------- ADMIN -------------------------------- //
    // ROLE
    Route::get('/admin/role', array('uses' => 'Admin\RoleController@index', 'as' => 'admin.role'));
    Route::get('/admin/role/get', array('uses' => 'Admin\RoleController@getList', 'as' => 'admin.role.get'));
    Route::get('/admin/role/create', array('uses' => 'Admin\RoleController@create', 'as' => 'admin.role.create'));
    Route::post('/admin/role', array('uses' => 'Admin\RoleController@store', 'as' => 'admin.role.store'));
    Route::get('/admin/role/edit/{id}', array('uses' => 'Admin\RoleController@edit', 'as' => 'admin.role.edit'));
    Route::get('/admin/role/permission/{id}', array('uses' => 'Admin\RoleController@permission', 'as' => 'admin.role.permission'));
    Route::put('/admin/role/update/{id}', array('uses' => 'Admin\RoleController@update', 'as' => 'admin.role.update'));
    Route::put('/admin/role/permission/{id}', array('uses' => 'Admin\RoleController@updatePermission', 'as' => 'admin.role.permission.update'));
    Route::delete('/admin/role/delete/{id}', array('uses' => 'Admin\RoleController@destroy', 'as' => 'admin.role.destroy'));

    // USER
    Route::get('/admin/user', array('uses' => 'Admin\UserController@index', 'as' => 'admin.user'));
    Route::get('/admin/user/get', array('uses' => 'Admin\UserController@getList', 'as' => 'admin.user.get'));
    Route::get('/admin/user/create', array('uses' => 'Admin\UserController@create', 'as' => 'admin.user.create'));
    Route::post('/admin/user', array('uses' => 'Admin\UserController@store', 'as' => 'admin.user.store'));
    Route::get('/admin/user/edit/{id}', array('uses' => 'Admin\UserController@edit', 'as' => 'admin.user.edit'));
    Route::put('/admin/user/update/{id}', array('uses' => 'Admin\UserController@update', 'as' => 'admin.user.update'));
    Route::delete('/admin/user/delete/{id}', array('uses' => 'Admin\UserController@destroy', 'as' => 'admin.user.destroy'));
    Route::put('/admin/user/activate/{id}', array('uses' => 'Admin\UserController@activate', 'as' => 'admin.user.activate'));

    Route::get('/myprofile', array('uses' => 'Admin\UserController@myprofile', 'as' => 'myprofile'));
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/home', 'HomeController@home')->name('home');


    // ------------------------------- MASTER -------------------------------- // 
    // UNIT
    Route::get('/master/unit', array('uses' => 'Master\UnitController@index', 'as' => 'master.unit'));
    Route::get('/master/unit/get', array('uses' => 'Master\UnitController@getList', 'as' => 'master.unit.get'));
    Route::get('/master/unit/create', array('uses' => 'Master\UnitController@create', 'as' => 'master.unit.create'));
    Route::post('/master/unit', array('uses' => 'Master\UnitController@store', 'as' => 'master.unit.store'));
    Route::get('/master/unit/edit/{id}', array('uses' => 'Master\UnitController@edit', 'as' => 'master.unit.edit'));
    Route::put('/master/unit/update/{id}', array('uses' => 'Master\UnitController@update', 'as' => 'master.unit.update'));
    Route::delete('/master/unit/delete/{id}', array('uses' => 'Master\UnitController@destroy', 'as' => 'master.unit.destroy'));

    // LOKASI
    Route::get('/master/lokasi', array('uses' => 'Master\LokasiController@index', 'as' => 'master.lokasi'));
    Route::get('/master/lokasi/get', array('uses' => 'Master\LokasiController@getList', 'as' => 'master.lokasi.get'));
    Route::get('/master/lokasi/create', array('uses' => 'Master\LokasiController@create', 'as' => 'master.lokasi.create'));
    Route::post('/master/lokasi', array('uses' => 'Master\LokasiController@store', 'as' => 'master.lokasi.store'));
    Route::get('/master/lokasi/edit/{id}', array('uses' => 'Master\LokasiController@edit', 'as' => 'master.lokasi.edit'));
    Route::put('/master/lokasi/update/{id}', array('uses' => 'Master\LokasiController@update', 'as' => 'master.lokasi.update'));
    Route::delete('/master/lokasi/delete/{id}', array('uses' => 'Master\LokasiController@destroy', 'as' => 'master.lokasi.destroy')); 
    // ------------------------------- TRANSACTION -------------------------------- //

    // ------------------------------- REPORT -------------------------------- //

});


