<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

// Default Homepage (Shows "Laravel" page)
Route::get('/', function () {
    return view('welcome');
});

// --- UTILITY ROUTES FOR HOSTINGER (Shared Hosting) ---
Route::get('/clear-cache', function () {
    try {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return '<h1>Cache, Config, Route, and Views Cleared Successfully!</h1>';
    } catch (\Exception $e) { return 'Error: ' . $e->getMessage(); }
});

Route::get('/migrate', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return '<h1>Database Migrations Run Successfully!</h1>';
    } catch (\Exception $e) { return 'Error: ' . $e->getMessage(); }
});

Route::get('/optimize', function () {
    try {
        Artisan::call('optimize:clear');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        return '<h1>System Optimized & Cached!</h1>';
    } catch (\Exception $e) { return 'Error: ' . $e->getMessage(); }
});
