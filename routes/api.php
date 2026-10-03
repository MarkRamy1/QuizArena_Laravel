<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

Route::get('/health', [ApiController::class, 'health']);
Route::post('/auth/login', [ApiController::class, 'login']);
Route::get('/auth/me', [ApiController::class, 'me']);

Route::get('/quizzes', [ApiController::class, 'quizzes']);
Route::get('/quizzes/code/{code}', [ApiController::class, 'quizByCode']);
Route::get('/quizzes/{id}', [ApiController::class, 'quizDetails']);

Route::post('/attempts/start/{quizId}', [ApiController::class, 'startAttempt']);
Route::get('/attempts/{id}', [ApiController::class, 'getAttempt']);
Route::patch('/attempts/{id}/answers', [ApiController::class, 'saveAnswer']);
Route::post('/attempts/{id}/submit', [ApiController::class, 'submitAttempt']);

Route::get('/certificates/verify/{code}', [ApiController::class, 'verifyCertificate']);
Route::get('/leaderboard', [ApiController::class, 'leaderboard']);
Route::get('/resources', [ApiController::class, 'resources']);
Route::get('/missions', [ApiController::class, 'missions']);
