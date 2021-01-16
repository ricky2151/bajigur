<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix'=>'auth'], function()
{
    Route::post('register', 'AuthController@registerAsUser');
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

Route::group(['prefix' => 'admin', 'middleware' => 'RoleAdmin', 'as'=>'admin.'], function()
{
    Route::get('users/history_transactions/{user}', 'UserController@historyTransaction');
    Route::resource('users', 'UserController')->only([
        'index','store', 'destroy', 'update'
    ]);

    Route::resource('categories', 'CategoryController')->except([
        'create', 'show'
    ]);
    Route::resource('products', 'ProductController')->except([
        'create', 'show'
    ]);
    Route::resource('transactions', 'TransactionController')->only([
        'index', 'show'
    ]);
});

Route::group(['prefix' => 'user', 'middleware' => 'RoleUser', 'as'=>'user.'], function()
{
    Route::patch('users/update_me', 'UserController@updateMe');
    Route::get('transactions/my_history_transaction', 'TransactionController@myHistoryTransaction');

    Route::resource('products', 'ProductController')->only([
        'index'
    ]);

    Route::resource('transactions', 'TransactionController')->only([
        'store'
    ]);
});


Route::any('{any}', function(){
    return response()->json([
        'status'    => false,
        'message'   => 'API Not Found.',
    ], 404);
})->where('any', '.*');