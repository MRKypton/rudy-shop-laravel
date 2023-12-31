<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', function () {
    return response()->json(['message' => 'Welcome to Rudy shop API'], Response::HTTP_OK);
});

Route::post('/register', [ApiAuthController::class, 'register']);

Route::post('/login', [ApiAuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->get('/logout', [ApiAuthController::class, 'logout']);

Route::middleware('auth:sanctum')->post('/upload-image', [ImageController::class, 'upload']);

Route::middleware('auth:sanctum')->get('/images/{image_id}', [ImageController::class, 'show']);
