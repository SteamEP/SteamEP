<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

// General pages
Route::get('/', 'HomeController@getIndex');
Route::get('disclaimer', 'HomeController@getDisclaimer');
Route::get('policy', 'HomeController@getPolicy');
Route::any('donate', 'HomeController@getDonate');

// Trading pages
Route::any('matches/{matchType?}', 'MatchController@matches');
Route::any('list/{listType?}', 'ItemController@getList');

// User related
Route::controller('user', 'UserController');
Route::get('/{steamid}', 'ItemController@getProfile')->where('steamid', '[0-9]+');

// Ajax
Route::get('editlist/{newvalue?}/{itemtype}', 'ItemController@updateItem');

// Not released
Route::get('library', 'LibraryController@getList');

// Errors
if (!Config::get('app.debug')) {
	App::error(function($exception, $code) {
		if ($code != 404) {
			Log::error($exception);
		}
		return Response::view('errors.default', array('code'=> $code), $code);
	});
}