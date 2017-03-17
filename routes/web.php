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

Route::get('/', 'Auth\LoginController@loginForm');
Route::get('/login', 'Auth\LoginController@loginForm');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
Route::get('/signup', 'Auth\RegisterController@registerform');

Route::get('/friend/requests', 'FriendController@getPendingRequests');
Route::post('/friend/requests/{friend}', 'FriendController@resolveFriendRequest');

Route::get('/profile', 'ProfileController@personal');
Route::get('/profile/{username}', 'ProfileController@show');


Route::get('/trip/requests', 'TripController@getPendingRequests');
Route::post('/trip/requests/{trip}', 'TripController@resolveTripRequest');
Route::post('/trips', 'TripController@store');
Route::get('/trips/dashboard', 'TripController@index');

Route::group(['middleware' => 'canAccessTrip'], function() {
	Route::get('/trips/{trip}', 'TripController@show');
	Route::post('/trips/{trip}', 'TripController@update');
	Route::post('/trips/{trip}/delete', 'TripController@destroy');
	Route::get('/trips/{trip}/data', 'TripController@data');
	Route::post('/trips/{trip}/inviteFriends', 'FriendController@inviteToTrip');
	Route::post('/trips/{trip}/searchEligibleFriends', 'FriendController@searchEligibleFriends');
	Route::post('/trips/{trip}/transactions/', 'TransactionController@store');
	Route::get('/trips/{trip}/transactions/{transaction}', 'TransactionController@byTrip');
	Route::post('/trips/{trip}/transactions/{transaction}', 'TransactionController@update');
	Route::post('/trips/{trip}/transactions/{transaction}/delete', 'TransactionController@destroy');
	Route::get('/trips/{trip}/travelers', 'TripController@travelers');
});

Route::post('/users/search', 'UserController@search');
Route::post('/users/{friend}/request', 'FriendController@sendRequest');

