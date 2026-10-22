<?php

use App\Http\Controllers\ImageJobController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'show'])->name('home');
Route::get('/robots.txt', [PageController::class, 'robots']);
Route::get('/sitemap.xml', [PageController::class, 'sitemap']);

Route::prefix('image-jobs')->name('image-jobs.')->group(function () {
    Route::post('/', [ImageJobController::class, 'store'])->middleware('throttle:uploads')->name('store');
    Route::middleware('throttle:poll')->group(function () {
        Route::get('{imageJob}', [ImageJobController::class, 'show'])->name('show');
        Route::get('{imageJob}/original', [ImageJobController::class, 'original'])->name('original');
        Route::get('{imageJob}/result', [ImageJobController::class, 'result'])->name('result');
        Route::get('{imageJob}/download', [ImageJobController::class, 'download'])->name('download');
    });
    Route::delete('{imageJob}', [ImageJobController::class, 'destroy'])->name('destroy');
});

Route::get('/background-remover', fn () => redirect()->route('home'));
Route::get('/{page}', [PageController::class, 'show'])->where('page', '[a-z-]+')->name('page');
