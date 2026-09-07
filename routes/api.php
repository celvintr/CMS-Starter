<?php

use App\Http\Controllers\Api\ContentApiController;
use App\Http\Middleware\ApiKeyAuth;
use Illuminate\Support\Facades\Route;

/*
| API de solo lectura del CMS (headless). Requiere API key:
|   Authorization: Bearer <token>   o   X-API-Key: <token>
*/
Route::middleware([ApiKeyAuth::class, 'throttle:60,1'])->group(function () {
    Route::get('/settings', [ContentApiController::class, 'settings']);

    Route::get('/pages', [ContentApiController::class, 'pages']);
    Route::get('/pages/{slug}', [ContentApiController::class, 'page']);

    Route::get('/posts', [ContentApiController::class, 'posts']);
    Route::get('/posts/{slug}', [ContentApiController::class, 'post']);

    Route::get('/modules', [ContentApiController::class, 'modules']);
    Route::get('/modules/{slug}', [ContentApiController::class, 'module']);
    Route::get('/modules/{slug}/entries', [ContentApiController::class, 'entries']);
    Route::get('/modules/{slug}/entries/{entry}', [ContentApiController::class, 'entry']);
});
