<?php

use App\Http\Controllers\CpbmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HouseAssessmentController;
use App\Http\Controllers\HouseConditionController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HouseDocumentController;
use App\Http\Controllers\HouseOccupantController;
use App\Http\Controllers\HouseRealizationController;
use App\Http\Controllers\HouseSanitationUtilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SurveyorManagementController;
use App\Http\Controllers\Surveyor\DashboardController as SurveyorDashboardController;
use App\Http\Controllers\Surveyor\HouseController as SurveyorHouseController;
use App\Models\House;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC PORTAL
|--------------------------------------------------------------------------
| Semua route di bagian ini bisa diakses tanpa login.
| Ini adalah area Viewer/public.
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicController::class, 'home'])
    ->name('public.home');

Route::get('/peta', [PublicController::class, 'map'])
    ->name('public.map');

Route::get('/statistik', [PublicController::class, 'statistics'])
    ->name('public.statistics');

Route::get('/data', [PublicController::class, 'datasets'])
    ->name('public.datasets');

Route::get('/viewer', [PublicController::class, 'viewer'])
    ->name('public.viewer');

Route::get('/rtlh/{house}', [PublicController::class, 'detail'])
    ->name('public.rtlh.detail');

Route::get('/download/rtlh.csv', [DownloadController::class, 'csv'])
    ->name('public.download.rtlh');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED AREA
|--------------------------------------------------------------------------
| Semua route internal membutuhkan login.
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | HOUSE CONDITION
    |--------------------------------------------------------------------------
    */

    Route::get('/houses/{house}/condition', [HouseConditionController::class, 'edit'])
        ->name('houses.condition.edit')
        ->can('update', 'house');

    Route::put('/houses/{house}/condition', [HouseConditionController::class, 'update'])
        ->name('houses.condition.update')
        ->can('update', 'house');


    /*
    |--------------------------------------------------------------------------
    | HOUSE SANITATION & UTILITY
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/houses/{house}/sanitation-utility',
        [HouseSanitationUtilityController::class, 'edit']
    )
        ->name('houses.sanitation-utility.edit')
        ->can('update', 'house');

    Route::put(
        '/houses/{house}/sanitation-utility',
        [HouseSanitationUtilityController::class, 'update']
    )
        ->name('houses.sanitation-utility.update')
        ->can('update', 'house');


    /*
    |--------------------------------------------------------------------------
    | CPBM (CALON PENERIMA BANTUAN MASYARAKAT)
    |--------------------------------------------------------------------------
    | Diproteksi hanya untuk Admin dan Petugas Surveyor resmi
    */

    Route::middleware('role:admin,surveyor')->group(function () {
        Route::get('/cpbm-search', [CpbmController::class, 'search'])
            ->name('cpbm.search');

        Route::resource('cpbm', CpbmController::class);
    });


    /*
    |--------------------------------------------------------------------------
    | HOUSES
    |--------------------------------------------------------------------------
    */

    Route::get('/houses', [HouseController::class, 'index'])
        ->name('houses.index')
        ->can('viewAny', House::class);

    Route::get('/houses/create', [HouseController::class, 'create'])
        ->name('houses.create')
        ->can('create', House::class);

    Route::post('/houses', [HouseController::class, 'store'])
        ->name('houses.store')
        ->can('create', House::class);

    Route::get('/houses/{house}/edit', [HouseController::class, 'edit'])
        ->name('houses.edit')
        ->can('update', 'house');

    Route::put('/houses/{house}', [HouseController::class, 'update'])
        ->name('houses.update')
        ->can('update', 'house');

    Route::delete('/houses/{house}', [HouseController::class, 'destroy'])
        ->name('houses.destroy');

    Route::get('/houses/{house}', [HouseController::class, 'show'])
        ->name('houses.show')
        ->can('view', 'house');

    Route::get('/houses/{house}/documents/{type}', [HouseDocumentController::class, 'show'])
        ->name('houses.documents.show');

    Route::post('/houses/{house}/realization', [HouseRealizationController::class, 'update'])
        ->name('houses.realization.update');


    /*
    |--------------------------------------------------------------------------
    | HOUSE OCCUPANTS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/houses/{house}/occupants',
        [HouseOccupantController::class, 'edit']
    )
        ->name('houses.occupants.edit')
        ->can('update', 'house');

    Route::put(
        '/houses/{house}/occupants',
        [HouseOccupantController::class, 'update']
    )
        ->name('houses.occupants.update')
        ->can('update', 'house');


    /*
    |--------------------------------------------------------------------------
    | HOUSE ASSESSMENT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/houses/{house}/assessment',
        [HouseAssessmentController::class, 'edit']
    )
        ->name('houses.assessment.edit')
        ->can('update', 'house');

    Route::put(
        '/houses/{house}/assessment',
        [HouseAssessmentController::class, 'update']
    )
        ->name('houses.assessment.update')
        ->can('update', 'house');


    /*
    |--------------------------------------------------------------------------
    | REGIONS
    |--------------------------------------------------------------------------
    */

    Route::get('/regions', [RegionController::class, 'index'])
        ->name('regions.index');

    Route::get('/regions/create', [RegionController::class, 'create'])
        ->name('regions.create');

    Route::post('/regions', [RegionController::class, 'store'])
        ->name('regions.store');


    /*
    |--------------------------------------------------------------------------
    | DEVELOPMENT / TEST ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/test-admin', function () {
        return 'Admin OK';
    })->middleware('role:admin');

    Route::get('/test-surveyor', function () {
        return 'Surveyor OK';
    })->middleware('role:surveyor');
});


/*
|--------------------------------------------------------------------------
| ADMIN WORKSPACE
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin',
])->prefix('admin')->group(function () {

    Route::get('/dashboard', [
        AdminDashboardController::class,
        'index',
    ])->name('admin.dashboard');

    Route::get('/review', [
        ReviewController::class,
        'index',
    ])->name('admin.review.index');

    Route::post('/houses/{house}/verify', [
        ReviewController::class,
        'verify',
    ])->name('admin.review.verify');

    Route::post('/houses/{house}/publish', [
        ReviewController::class,
        'publish',
    ])->name('admin.review.publish');

    Route::post('/houses/{house}/revision', [
        ReviewController::class,
        'revision',
    ])->name('admin.review.revision');

    /*
    |--------------------------------------------------------------------------
    | SURVEYOR MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::get('/surveyors', [
        SurveyorManagementController::class,
        'index',
    ])->name('admin.surveyors.index');

    Route::get('/surveyors/create', [
        SurveyorManagementController::class,
        'create',
    ])->name('admin.surveyors.create');

    Route::get('/surveyors/bulk-create', [
        SurveyorManagementController::class,
        'bulkCreate',
    ])->name('admin.surveyors.bulk-create');

    Route::post('/surveyors/bulk-store', [
        SurveyorManagementController::class,
        'bulkStore',
    ])->name('admin.surveyors.bulk-store');

    Route::get('/surveyors/template-csv', [
        SurveyorManagementController::class,
        'downloadCsvTemplate',
    ])->name('admin.surveyors.download-csv-template');

    Route::post('/surveyors', [
        SurveyorManagementController::class,
        'store',
    ])->name('admin.surveyors.store');

    Route::get('/surveyors/{surveyor}', [
        SurveyorManagementController::class,
        'show',
    ])->name('admin.surveyors.show');

    Route::get('/surveyors/{surveyor}/edit', [
        SurveyorManagementController::class,
        'edit',
    ])->name('admin.surveyors.edit');

    Route::put('/surveyors/{surveyor}', [
        SurveyorManagementController::class,
        'update',
    ])->name('admin.surveyors.update');

    Route::post('/surveyors/{surveyor}/toggle-status', [
        SurveyorManagementController::class,
        'toggleStatus',
    ])->name('admin.surveyors.toggle-status');

    Route::post('/surveyors/{surveyor}/toggle-admin', [
        SurveyorManagementController::class,
        'toggleAdmin',
    ])->name('admin.surveyors.toggle-admin');
});


/*
|--------------------------------------------------------------------------
| SURVEYOR WORKSPACE
|--------------------------------------------------------------------------
|
| Admin boleh melihat workspace Surveyor.
| Surveyor hanya bisa mengelola data miliknya.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,surveyor',
])->prefix('surveyor')->group(function () {

    Route::get('/dashboard', [
        SurveyorDashboardController::class,
        'index',
    ])->name('surveyor.dashboard');

    Route::get('/rtlh', [
        SurveyorHouseController::class,
        'index',
    ])->name('surveyor.rtlh.index');

    Route::get('/rtlh/{house}', [
        SurveyorHouseController::class,
        'show',
    ])->name('surveyor.rtlh.show');
});


/*
|--------------------------------------------------------------------------
| SURVEYOR WRITE ACTIONS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:surveyor',
])->prefix('surveyor')->group(function () {

    Route::get('/rtlh-create', [
        SurveyorHouseController::class,
        'create',
    ])->name('surveyor.rtlh.create');

    Route::post('/rtlh', [
        SurveyorHouseController::class,
        'store',
    ])->name('surveyor.rtlh.store');

    Route::get('/rtlh/{house}/edit', [
        SurveyorHouseController::class,
        'edit',
    ])->name('surveyor.rtlh.edit');

    Route::put('/rtlh/{house}', [
        SurveyorHouseController::class,
        'update',
    ])->name('surveyor.rtlh.update');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
