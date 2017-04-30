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

// \vendor\laravel\framework\src\Illuminate\Routing\Router.php
Auth::routes();

Route::get('/', 'Auth\LoginController@loginForm');
Route::get('/login', 'Auth\LoginController@loginForm')->name('login');
Route::get('/signup', 'Auth\RegisterController@registerform');

Route::group(['middleware' => 'auth'], function() {
	Route::get('/user/activation/pending', 'ActivationController@pending');
	Route::get('/user/activation/resend', 'ActivationController@resend');
	Route::get('/profile/{username}', 'ProfileController@show');
});

Route::group(['middleware' => 'activeAccount'], function() {

	Route::get('/friend/requests', 'FriendController@getPendingRequests');
	Route::post('/friend/requests/{friend}', 'FriendController@resolveFriendRequest');
	Route::get('/profile', 'ProfileController@personal');
	Route::get('/trip/requests', 'TripController@getPendingRequests');
	Route::post('/trip/requests/{trip}', 'TripController@resolveTripRequest');
	Route::post('/trips', 'TripController@store');
	Route::get('/trips/dashboard', 'TripController@index');
	Route::post('/users/search', 'UserController@search');
	Route::post('/users/{friend}/request', 'FriendController@sendRequest');

	Route::group(['middleware' => 'canAccessTrip'], function() {
		Route::get('/trips/{trip}/report/bottomLine', 'ReportController@bottomLine');
		Route::get('/trips/{trip}/report/closeout', 'ReportController@closeout');
		Route::get('/trips/{trip}/report/detailed', 'ReportController@detailed');
		Route::get('/trips/{trip}/report/distribution', 'ReportController@distribution');
		Route::get('/trips/{trip}/report/topSpenders', 'ReportController@topSpenders');

		Route::get('/trips/{trip}', 'TripController@show');
		Route::post('/trips/{trip}', 'TripController@update');
		Route::post('/trips/{trip}/delete', 'TripController@destroy');
		Route::get('/trips/{trip}/data', 'TripController@data');
		Route::post('/trips/{trip}/inviteFriends', 'FriendController@inviteToTrip');
		Route::post('/trips/{trip}/searchEligibleFriends', 'FriendController@searchEligibleFriends');
		Route::post('/trips/{trip}/transactions/', 'TransactionController@store');
		Route::get('/trips/{trip}/travelers', 'TripController@travelers');
	});

	Route::group(['middleware' => ['canAccessTrip', 'tripHasTransaction']], function() {
		Route::get('/trips/{trip}/transactions/{transaction}', 'TransactionController@byTrip');
		Route::post('/trips/{trip}/transactions/{transaction}', 'TransactionController@update');
		Route::post('/trips/{trip}/transactions/{transaction}/delete', 'TransactionController@destroy');
	});
});

Route::get('user/activation/{token}', 'ActivationController@activateUser')->name('user.activate');

Route::group(['middleware' => 'isAdmin'], function() {
	Route::get('/maintenance/loadHashtags', 'MaintenanceController@loadHashtags');
});
