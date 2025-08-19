<?php

use Apileon\Routing\Route;

// Basic hello world route
Route::get('/hello', function () {
    return ['message' => 'Hello from Apileon!'];
});

// Route with parameter
Route::get('/hello/{name}', function ($request) {
    return [
        'message' => 'Hello ' . $request->param('name') . '!',
        'timestamp' => date('Y-m-d H:i:s')
    ];
});

// Example API endpoints
Route::get('/api/users', 'App\Controllers\UserController@index');
Route::get('/api/users/{id}', 'App\Controllers\UserController@show');
Route::post('/api/users', 'App\Controllers\UserController@store');
Route::put('/api/users/{id}', 'App\Controllers\UserController@update');
Route::delete('/api/users/{id}', 'App\Controllers\UserController@destroy');

// Protected routes group
Route::group(['prefix' => 'api/v1', 'middleware' => ['auth']], function () {
    Route::get('/profile', 'App\Controllers\UserController@profile');
    Route::put('/profile', 'App\Controllers\UserController@updateProfile');
});

// Routes with CORS middleware
Route::group(['middleware' => ['cors']], function () {
    Route::get('/public/status', function () {
        return [
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => time()
        ];
    });
});

// Rate limited routes
Route::group(['middleware' => ['throttle']], function () {
    Route::post('/api/contact', function ($request) {
        return [
            'message' => 'Thank you for your message!',
            'data' => $request->all()
        ];
    });
});
