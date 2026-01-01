<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [LoginController::class, 'login']);
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::group(['prefix' => 'tasks'], function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/store', [TaskController::class, 'store']);
        Route::put('/update/{id}', [TaskController::class, 'update']);
        Route::post('/addDependencies/{id}', [TaskController::class, 'addDependencies']);
        Route::post('/removeDependency/{id}', [TaskController::class, 'removeDependency']);
        Route::post('/assign/{id}', [TaskController::class, 'assign']);
        Route::delete('delete/{id}', [TaskController::class, 'destroy']);
        Route::post('/statusUpdate/{id}', [TaskController::class, 'statusUpdate']);
        Route::get('/show/{id}', [TaskController::class, 'show']);

    });
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
