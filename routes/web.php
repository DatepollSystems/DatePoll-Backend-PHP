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

Route::get('/', function () {
    return "Running DatePoll-Backend. Version: " . config('app.version');
});

Route::group(['prefix' => 'api/v1'], function() {
  Route::group(['prefix' => 'cinema'], function() {
    Route::resource('movie', 'MovieController', [
      'except' => ['edit', 'create']
    ]);

    Route::get('notShownMovies', [
      'uses' => 'MovieController@getNotShownMovies'
    ]);

    Route::resource('year', 'MovieYearController', [
      'except' => ['edit', 'create']
    ]);

    Route::group(['prefix' => 'movie'], function() {

      Route::resource('booking', 'MovieBookingController', [
        'only' => ['store', 'destroy']
      ]);

      Route::get('booking/yourBookings', [
        'uses' => 'MovieBookingController@yourBookings'
      ]);
    });
  });
});
