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
// header('Access-Control-Allow-Origin:*');
// header('Access-Control-Allow-Methods:GET, POST, PUT, DELETE, OPTIONS');

Route::get('/user', function(Request $request) {

	return $request->user();
})->middleware('auth:api');

Route::group(['middleware' => 'cors'], function() {

	Route::resource('book','BookController');
	
	// Pages
	Route::resource('page','PagesController');

	// User, login, registration
	Route::resource('user', 'UserController', [
		'except' => [
			'create', 'edit', 'update', 'destroy'
		]
	]);
	Route::post('/user/identify', 'UserController@authenticate');
	Route::post('/user/guest', 'UserController@createGuestToken');
	Route::post('/user/authenticate', '\App\User@authenticate');
	Route::post('/user/password', 'UserController@hashPassword');

	// Text based adventure
	// Route::get('message/question', 'MessageStreamController@question'); // Create new GET route
	// Route::post('message/answer', 'MessageStreamController@answer'); // Create new POST route
	Route::resource('message','MessageStreamController', [
		'except' => [
			'create', 'edit', 'update', 'destroy'
		]
	]);
});