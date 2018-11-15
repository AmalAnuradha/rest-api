<?php
use Swagger\Annotations as SWG;
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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/users','UserController@showUsers')->middleware('jwt.auth')->name('all users');



Route::get('/users/{id}','UserController@showUser')->name('get user');




Route::post('/users','UserController@addUser')->name('insert user');


Route::put('/users/{id}','UserController@updateUser')->name('update user');



Route::delete('/users/{id}','UserController@delete')->name('delete user');
