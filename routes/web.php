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

// Route::middleware(['basicAuth'])->get('/', function() {
//     return "Autenticiran si, boy!";
// })->name('successfulAuth');

Route::get('/', 'HomeController@index')->name('index')->middleware('cors');

Route::post('/register', 'UsersController@register')->middleware('cors');

Route::post('/login', 'UsersController@login')->middleware('cors', 'auth:api');

Route::get('/register', function () {
    return view('register');
})->name('register')->middleware('cors');

Route::get('/login', function(){
    Log::info('message');
    
    if (request()->cookie('auth_token') != null){
        \Cookie::queue(cookie('session_id', session()->getId()));
        return app('App\Http\Controllers\ResponseController')::respond(request(), 200, 'Welcome back!', 'index');
    }

    return view('login');
})->name('login', ['message' => ''])->middleware('cors');

Route::get('/logout', 'UsersController@logout')->name('logout')->middleware('auth:api', 'cors');

Route::get('/game', function() {
	return view('game');
})->middleware('auth:api');
