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



Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');
Route::get('trackAttendance', 'AuthController@trackAttendance');


Route::group(['middleware' => 'auth:api'], function(){



    Route::post('details', 'API\UserController@details');

    Route::get('user/profile', 'AuthController@profile');

    Route::get('service/service_list','API\ServiceController@service_list');
    Route::post('service/create','API\ServiceController@store');
    Route::post('service/compare','API\ServiceController@compareServices');

    Route::get('members/leaders','API\LeadersController@leaders');
    Route::post('members/create', 'API\MembersController@store');
    Route::post('members/edit', 'API\MembersController@edit');
    Route::post('members/bulkUpload','API\MembersController@bulkUpload');
    Route::get('members/export','API\MembersController@export');
    Route::get('members/lists', 'API\MembersController@lists');
    Route::get('members/groups','API\MembersController@groups');
    Route::get('members/active','API\MembersController@active');
    Route::get('members/in_active','API\MembersController@in_active');

    Route::get('members/track/{member_id}','API\MembersController@memberTracking');
    Route::get('members/profile/{member_id}','API\MembersController@memberProfile');

    Route::get('firsttimers/show','API\FirstTimersController@showFirstTimers');
    Route::post('firsttimers/add','API\FirstTimersController@createFirstTimers');
    Route::post('firsttimers/batchUpload','API\FirstTimersController@batchUpload');
    Route::get('firsttimers/firstTimer_lists','API\FirstTimersController@firstTimer_lists');

    Route::get('attendance/attendees','API\AttendanceController@attendees');
    Route::get('attendance/trackAttendance', 'API\AttendanceController@trackAttendance');

    Route::post('attendance/create','API\AttendanceController@store');
    Route::get('attendance/last','API\ServiceController@lastService');

    Route::post('attendance/single','API\AttendanceController@singleAttendance');


    Route::post('/call/personnel/create', 'Call\CallListController@doCreatePersonnel')->name('doCreatePersonnel');
    Route::get('/call/personnel', 'Call\CallListController@showPersonnels')->name('showPersonnels');

    Route::post('/call/create/group', 'Call\CallListController@createList')->name('createList');
    Route::get('/call/groups', 'Call\CallListController@showGroups')->name('showGroups');

    Route::post('/log/add/', 'Call\CallListController@addLog')->name('addLog');
    Route::get('/call/list', 'Call\HomeController@showList')->name('showCallProfile');

    Route::get('/call/group/{id}', 'Call\CallListController@showList')->name('showList');

    Route::post('calls/create','API\CallsController@store');






});






