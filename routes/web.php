<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

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
    Route::view('/', 'scribe.index');

    Route::get('/collection.json', function () {
        return new JsonResponse(
            file_get_contents(storage_path('app/scribe/collection.json')),
            json: true,
        );
    })->name('postman');

    Route::get('/openapi.yaml', function () {
        return response()->file(storage_path('app/scribe/openapi.yaml'));
    })->name('openapi');
});
