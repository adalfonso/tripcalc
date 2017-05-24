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

// located @ \vendor\laravel\framework\src\Illuminate\Routing\Router.php
Auth::routes();

Route::get('/', 'Auth\LoginController@loginForm');
Route::get('/login', 'Auth\LoginController@loginForm')->name('login');
Route::get('/signup', 'Auth\RegisterController@registerForm');

Route::group(['middleware' => 'auth'], function() {
	Route::get('/user/activation/pending', 'ActivationController@pending');
	Route::get('/user/activation/resend', 'ActivationController@resend');
	Route::get('/profile/{username}', 'ProfileController@show');
});

Route::group(['middleware' => 'activeAccount'], function() {

	Route::get('/profile', 'ProfileController@personal');
	Route::get('/trip/requests', 'TripController@getPendingRequests');
	Route::post('/trip/{trip}/resolveRequest', 'TripController@resolveRequest');
	Route::get('/trips', 'TripController@index');
	Route::post('/trips', 'TripController@store');
	Route::get('/user', 'UserController@info');
	Route::patch('/user', 'UserController@update');
	Route::post('/user/search', 'UserController@search');
	Route::get('/friend/requests', 'FriendController@getPendingRequests');
	Route::post('/friend/{friend}/request', 'FriendController@sendRequest');
	Route::post('/friend/{friend}/resolveRequest', 'FriendController@resolveRequest');

	Route::group(['middleware' => 'canAccessTrip'], function() {
		Route::get('/trip/{trip}', 'TripController@show');
		Route::patch('/trip/{trip}', 'TripController@update');
		Route::post('/trip/{trip}/activities', 'TripController@activities');
	    Route::delete('/trip/{trip}', 'TripController@destroy');
		Route::get('/trip/{trip}/data', 'TripController@data');
		Route::post('/trip/{trip}/eligibleFriends', 'TripController@eligibleFriends');
		Route::post('/trip/{trip}/inviteFriends', 'FriendController@inviteToTrip');
		Route::post('/trip/{trip}/posts', 'PostController@store');
		Route::post('/trip/{trip}/transactions', 'TransactionController@store');
		Route::get('/trip/{trip}/travelers', 'TripController@travelers');
		Route::get('/trip/{trip}/report/bottomLine', 'ReportController@bottomLine');
		Route::get('/trip/{trip}/report/closeout', 'ReportController@closeout');
		Route::get('/trip/{trip}/report/detailed', 'ReportController@detailed');
		Route::get('/trip/{trip}/report/extended', 'ReportController@extended');
		Route::get('/trip/{trip}/report/distribution', 'ReportController@distribution');
		Route::get('/trip/{trip}/report/topSpenders', 'ReportController@topSpenders');

		Route::group(['middleware' => 'ownsPost'], function() {
			Route::patch('/trip/{trip}/post/{post}', 'PostController@update');
			Route::delete('/trip/{trip}/post/{post}', 'PostController@destroy');
		});
	});

	Route::group(['middleware' => ['canAccessTrip', 'tripHasTransaction']], function() {
		Route::get('/trip/{trip}/transaction/{transaction}', 'TransactionController@show');
		Route::patch('/trip/{trip}/transaction/{transaction}', 'TransactionController@update');
		Route::delete('/trip/{trip}/transaction/{transaction}', 'TransactionController@destroy');
	});
});

// Placed at bottom. Conflicts with user/activation/pending and resent
Route::get('/user/activation/{token}', 'ActivationController@activateUser')->name('user.activate');

Route::group(['middleware' => 'isAdmin'], function() {
	Route::get('/maintenance/loadHashtags', 'MaintenanceController@loadHashtags');
});
