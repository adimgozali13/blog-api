<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/store', [PostController::class, 'store']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
        Route::post('/{id}', [PostController::class, 'update']);
    });

    Route::prefix('comments')->group(function () {
        Route::get('/', [CommentController::class, 'index']);
        Route::get('/{id}', [CommentController::class, 'show']);
        Route::post('/store', [CommentController::class, 'store']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

