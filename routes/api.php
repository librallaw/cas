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




Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');

Route::group(['middleware' => 'auth:api'], function(){

    Route::post('details', 'API\UserController@details');
    Route::post('members', 'API\MembersController@store');
    Route::post('members/edit', 'API\MembersController@edit');

    Route::get('user/profile', 'API\UserController@profile');

    Route::post('members/bulkUpload','API\MembersController@bulkUpload');

    Route::get('members/export','API\MembersController@export');

    Route::get('members/lists', 'API\MembersController@lists');

    Route::post('service','API\ServiceController@store');

    Route::post('attendance','API\AttendanceController@store');


});






