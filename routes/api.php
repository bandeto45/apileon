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

// Performance and health monitoring routes
Route::get('/health', function () {
    $metrics = performance_metrics();
    
    return [
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
        'environment' => env('APP_ENV', 'production'),
        'performance' => $metrics['performance'] ?? []
    ];
});

Route::get('/metrics', function () {
    if (!app_debug()) {
        return abort(403, 'Metrics endpoint only available in debug mode');
    }
    
    return performance_metrics();
});

// Cache testing routes (debug only)
Route::get('/cache/test', function () {
    if (!app_debug()) {
        return abort(403, 'Cache test only available in debug mode');
    }
    
    $key = 'test_' . time();
    $value = 'cached_value_' . rand(1000, 9999);
    
    cache($key, $value, 60);
    $retrieved = cache($key);
    
    return [
        'cache_set' => $value,
        'cache_retrieved' => $retrieved,
        'match' => $value === $retrieved
    ];
});

// Event testing route (debug only)
Route::get('/events/test', function () {
    if (!app_debug()) {
        return abort(403, 'Events test only available in debug mode');
    }
    
    $results = event('test.event', ['message' => 'Hello from event system!']);
    
    return [
        'event_fired' => 'test.event',
        'results' => $results
    ];
});
