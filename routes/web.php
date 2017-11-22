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
Route::get('login', 'Auth\LoginController@loginForm')->name('login');
Route::get('signup', 'Auth\RegisterController@registerForm');

Route::group(['middleware' => 'auth'], function() {
	Route::get('user/activation/pending', 'ActivationController@pending');
	Route::get('user/activation/resend', 'ActivationController@resend');
	Route::get('profile/{username}', 'ProfileController@show');
});

Route::group(['middleware' => ['auth', 'activeAccount']], function() {
	Route::get('profile', 'ProfileController@personal');
	Route::get('trips', 'TripController@index');
	Route::post('trips', 'TripController@store');
	Route::get('user', 'UserController@info');
	Route::patch('user', 'UserController@update');
	Route::post('user/notifications', 'UserController@notifications');
	Route::post('user/notifications/see', 'UserController@seeNotifications');
	Route::get('user/requests', 'UserController@requests');
	Route::post('user/search', 'UserController@search');

	Route::get('friends', 'FriendController@friends');
	Route::post('friend/{friend}/request', 'FriendController@sendRequest');
	Route::delete('friend/{friend}/unfriend', 'FriendController@unfriend');
	Route::post('friend/{friend}/resolveRequest', 'FriendController@resolveRequest');

	Route::get('trip/{trip}/removeRequest', 'TripController@removeRequest');

	Route::group(['middleware' => 'tripIsActive'], function() {
		Route::post('trip/{trip}/resolveRequest', 'TripController@resolveRequest')
			->name('resolveTripRequest');
	});

	Route::group(['middleware' => 'canAccessTrip'], function() {
		Route::get('trip/{trip}', 'TripController@show');
		Route::post('trip/{trip}/activities', 'TripController@activities');
		Route::get('trip/{trip}/advancedSettings', 'TripController@getAdvancedSettings');
		Route::patch('trip/{trip}/advancedSettings', 'TripController@updateAdvancedSettings');
		Route::post('trip/{trip}/closeout', 'TripController@userCloseout');
		Route::get('trip/{trip}/data', 'TripController@data');
		Route::get('trip/{trip}/travelers', 'TripController@travelers');
		Route::get('trip/{trip}/virtualUsers', 'VirtualUserController@index');
		Route::get('trip/{trip}/report/bottomLine', 'ReportController@bottomLine');
		Route::get('trip/{trip}/report/closeout', 'ReportController@closeout');
		Route::get('trip/{trip}/report/detailed', 'ReportController@detailed');
		Route::get('trip/{trip}/report/extended', 'ReportController@extended');
		Route::get('trip/{trip}/report/distribution', 'ReportController@distribution');
		Route::get('trip/{trip}/report/topSpenders', 'ReportController@topSpenders');

		Route::group(['middleware' => 'tripIsActive'], function() {
			Route::post('trip/{trip}/transactions', 'TransactionController@store');

			Route::group(['middleware' => 'tripHasTransaction'], function() {
				Route::patch('trip/{trip}/transaction/{transaction}', 'TransactionController@update');
				Route::delete('trip/{trip}/transaction/{transaction}', 'TransactionController@destroy');
			});
		});

		Route::group(['middleware' => 'tripHasTransaction'], function() {
			Route::get('trip/{trip}/transaction/{transaction}', 'TransactionController@show');
		});

		Route::group(['middleware' => 'tripIsActive'], function() {
			Route::patch('trip/{trip}', 'TripController@update');
			Route::delete('trip/{trip}', 'TripController@destroy');
			Route::post('trip/{trip}/eligibleFriends', 'TripController@eligibleFriends');
			Route::post('trip/{trip}/inviteFriends', 'FriendController@inviteToTrip');

			Route::group(['middleware' => 'virtualUsersEnabled'], function() {
				Route::post('trip/{trip}/virtualUsers', 'VirtualUserController@store');
				Route::patch('trip/{trip}/virtualUser/{virtualUser}', 'VirtualUserController@update');
				Route::delete('trip/{trip}/virtualUser/{virtualUser}', 'VirtualUserController@destroy');
				Route::post('trip/{trip}/virtualUser/{virtualUser}/merge', 'VirtualUserController@merge');
			});
		});
	});

	Route::group(['middleware' => 'ownsComment'], function() {
		Route::patch('comment/{comment}', 'CommentController@update');
	});

	Route::group(['middleware' => 'canDeleteComment'], function() {
		Route::delete('comment/{comment}', 'CommentController@destroy');
	});

	Route::group(['middleware' => 'canAccessPost'], function() {
		Route::get('post/{post}', 'PostController@show');
	});

	// Trip Posts
	Route::group(['middleware' => 'canAccessTrip'], function() {
		Route::post('trip/{trip}/posts', 'PostController@storeForTrip');
		Route::post('trip/{trip}/post/{post}/comment', 'PostController@commentOnTrip');

		Route::group(['middleware' => 'ownsPost'], function() {
			Route::patch('trip/{trip}/post/{post}', 'PostController@updateForTrip');
			Route::delete('trip/{trip}/post/{post}', 'PostController@destroyForTrip');
		});
	});

	// Profile Posts
	Route::group(['middleware' => 'hasActiveFriendshipWith'], function() {
		Route::post('profile/{user}/posts/fetch', 'ProfileController@fetchMorePosts');
		Route::post('profile/{user}/posts', 'PostController@storeForProfile');
		Route::post('profile/{user}/post/{post}/comment', 'PostController@commentOnProfile');

		Route::group(['middleware' => 'ownsPost'], function() {
			Route::patch('profile/{user}/post/{post}', 'PostController@updateForProfile');
		});

		Route::group(['middleware' => 'canDeleteProfilePost'], function() {
			Route::delete('profile/{user}/post/{post}', 'PostController@destroyForProfile');
		});

		Route::group(['middleware' => 'isCurrentUser'], function() {
			Route::post('user/{user}/photos/upload', 'UserController@uploadPhoto');
		});
	});
});

// Placed at bottom. Conflicts with user/activation/pending and resend
Route::get('user/activation/{token}', 'ActivationController@activateUser')->name('user.activate');

Route::group(['middleware' => 'auth'], function() {
	Route::get('user/{user}', 'UserController@profile');
});

Route::group(['middleware' => 'isAdmin'], function() {
	Route::get('maintenance/loadHashtags', 'MaintenanceController@loadHashtags');
});
