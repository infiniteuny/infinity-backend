<?php

use App\Http\Controllers\FacultyController;
use App\Http\Controllers\MemberController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/faculties', [FacultyController::class, 'facultiesList'])->name('faculties.list');
Route::get('/faculties/{faculty}/program-studies', [FacultyController::class, 'programStudiesList'])->name('faculties.program-studies.list');
Route::get('/members', [MemberController::class, 'membersList'])->name('members.list');
