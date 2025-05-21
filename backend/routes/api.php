<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\DealController;
use App\Http\Controllers\API\PerformerController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\SocialAuthController;
use App\Http\Controllers\API\FileController;
use Illuminate\Support\Facades\Route;

/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
*/

// Public routes
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

// VK authentication
Route::prefix('auth/vk')->group(function () {
    Route::get('redirect', [SocialAuthController::class, 'redirect']);
    Route::match(['get', 'post'], 'callback', [SocialAuthController::class, 'callback']);
});

// Protected routes
Route::middleware('jwt.auth')->group(function () {
    // Profile management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
    });
    // Authentication
    Route::post('auth/logout', [AuthController::class, 'logout']);
    // Deals management with rate limiting
    Route::middleware(['throttle:120,1'])->group(function () {
        Route::apiResource('deals', DealController::class)->except(['create', 'edit']);
        Route::patch('deals/{deal}/status', [DealController::class, 'updateStatus'])
            ->where('deal', '[0-9]+');
        Route::get('deals/{deal}/comments', [DealController::class, 'getComments'])
            ->where('deal', '[0-9]+');
        Route::post('deals/{deal}/comments', [DealController::class, 'addComment'])
            ->where('deal', '[0-9]+');
    });
    // Role-specific routes
    Route::middleware('role:performer')->get('performer-area', [PerformerController::class, 'index']);
    Route::middleware('role:client')->get('client-area', [ClientController::class, 'index']);
});

// File routes
Route::middleware('auth:api')->group(function () {
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::get('/files/{type}/{filename}', [FileController::class, 'download']);
    Route::delete('/files/{type}/{filename}', [FileController::class, 'delete']);
});
