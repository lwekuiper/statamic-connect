<?php

use Illuminate\Support\Facades\Route;
use Lwekuiper\StatamicConnect\Http\Controllers\AddonConfigController;
use Lwekuiper\StatamicConnect\Http\Controllers\DashboardController;
use Lwekuiper\StatamicConnect\Http\Controllers\FormConfigController;
use Lwekuiper\StatamicConnect\Http\Controllers\GetFormFieldsController;
use Lwekuiper\StatamicConnect\Http\Controllers\GetRemoteFieldsController;
use Lwekuiper\StatamicConnect\Http\Controllers\GetRemoteListsController;
use Lwekuiper\StatamicConnect\Http\Controllers\GetRemoteTagsController;

Route::name('connect.')->prefix('connect')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/settings', [AddonConfigController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [AddonConfigController::class, 'update'])->name('settings.update');

    Route::name('form-config.')->prefix('{integration}')->group(function () {
        Route::get('/{form}/edit', [FormConfigController::class, 'edit'])->name('edit');
        Route::patch('/{form}', [FormConfigController::class, 'update'])->name('update');
        Route::delete('/{form}', [FormConfigController::class, 'destroy'])->name('destroy');
    });

    // API routes for Vue components
    Route::get('api/form-fields/{form}', GetFormFieldsController::class)->name('api.form-fields');
    Route::get('api/{integration}/remote-fields', GetRemoteFieldsController::class)->name('api.remote-fields');
    Route::get('api/{integration}/remote-lists', GetRemoteListsController::class)->name('api.remote-lists');
    Route::get('api/{integration}/remote-tags', GetRemoteTagsController::class)->name('api.remote-tags');
});
