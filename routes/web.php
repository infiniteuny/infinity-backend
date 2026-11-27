<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group([
    'as' => 'scribe.',
], function () {
    Route::get('/', function () {
        abort_unless(View::exists('scribe.index'), 404);

        return view('scribe.index');
    });

    Route::get('/collection.json', function () {
        $path = storage_path('app/scribe/collection.json');
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/json',
        ]);
    })->name('postman');

    Route::get('/openapi.yaml', function () {
        $path = storage_path('app/scribe/openapi.yaml');
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/yaml',
        ]);
    })->name('openapi');
});
