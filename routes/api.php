<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\HousePhotoApiController;
use App\Http\Controllers\Api\SurveyorApiController;
use App\Http\Controllers\Api\SyncApiController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\Api\V1\MobileAuthController;
use App\Http\Controllers\Api\V1\MobileMasterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC API — tanpa autentikasi
|--------------------------------------------------------------------------
*/

Route::prefix('v1/public')->group(function () {

    Route::get('/regions', [PublicApiController::class, 'regions'])
        ->name('api.public.regions');

    Route::get('/rtlh', [PublicApiController::class, 'rtlh'])
        ->name('api.public.rtlh');

    Route::get('/rtlh/{house}', [PublicApiController::class, 'show'])
        ->name('api.public.rtlh.show');

    Route::get('/statistics', [PublicApiController::class, 'statistics'])
        ->name('api.public.statistics');

});

/*
|--------------------------------------------------------------------------
| AUTH API — login & logout (tidak perlu token)
|--------------------------------------------------------------------------
*/

Route::prefix('v1/auth')->middleware(['throttle:6,1'])->group(function () {

    Route::post('/login', [AuthApiController::class, 'login'])
        ->name('api.auth.login');

});

/*
|--------------------------------------------------------------------------
| MOBILE API
|--------------------------------------------------------------------------
*/

Route::prefix('v1/mobile')->group(function () {

    Route::post('/auth/login', [
        MobileAuthController::class,
        'login'
    ])->middleware(['throttle:6,1'])->name('api.mobile.login');

    Route::middleware('auth:sanctum')->group(function () {

    Route::get('/master', [
        MobileMasterController::class,
        'index'
    ])->name('api.mobile.master');

    // route me + logout yang sebelumnya
});

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/auth/me', [
            MobileAuthController::class,
            'me'
        ])->name('api.mobile.me');

        Route::post('/auth/logout', [
            MobileAuthController::class,
            'logout'
        ])->name('api.mobile.logout');

    });

});

/*
|--------------------------------------------------------------------------
| SURVEYOR API — butuh token Sanctum
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthApiController::class, 'logout'])
        ->name('api.auth.logout');

    Route::get('/auth/me', [AuthApiController::class, 'me'])
        ->name('api.auth.me');

    // Sync master data & wilayah (untuk cache lokal Android)
    Route::prefix('surveyor/sync')->group(function () {

        Route::get('/master-data', [SyncApiController::class, 'masterData'])
            ->name('api.sync.master-data');

        Route::get('/regions', [SyncApiController::class, 'regions'])
            ->name('api.sync.regions');

    });

    // CRUD rumah
    Route::prefix('surveyor/houses')->group(function () {

        Route::get('/', [SurveyorApiController::class, 'index'])
            ->name('api.surveyor.houses.index');

        Route::post('/', [SurveyorApiController::class, 'store'])
            ->name('api.surveyor.houses.store');

        Route::get('/{house}', [SurveyorApiController::class, 'show'])
            ->name('api.surveyor.houses.show');

        Route::put('/{house}', [SurveyorApiController::class, 'update'])
            ->name('api.surveyor.houses.update');

        Route::post('/{house}/submit', [SurveyorApiController::class, 'submit'])
            ->name('api.surveyor.houses.submit');

        // Foto
        Route::post('/{house}/photos', [HousePhotoApiController::class, 'store'])
            ->name('api.surveyor.houses.photos.store');

        Route::delete('/{house}/photos/{photo}', [HousePhotoApiController::class, 'destroy'])
            ->name('api.surveyor.houses.photos.destroy');

    });

});

