<?php

use Illuminate\Support\Facades\Route;
use Lwekuiper\StatamicConnect\Http\Controllers\ActiveCampaign\GetFormFieldsController as ACGetFormFieldsController;
use Lwekuiper\StatamicConnect\Http\Controllers\ActiveCampaign\GetMergeFieldsController;
use Lwekuiper\StatamicConnect\Http\Controllers\Cp\AddonConfigController;
use Lwekuiper\StatamicConnect\Http\Controllers\Cp\FormConfigController;
use Lwekuiper\StatamicConnect\Http\Controllers\HubSpot\GetContactPropertiesController;
use Lwekuiper\StatamicConnect\Http\Controllers\HubSpot\GetFormFieldsController as HSGetFormFieldsController;

Route::name('connect.')->prefix('connect')->group(function () {
    // Global settings
    Route::name('settings.')->prefix('settings')->group(function () {
        Route::get('/', [AddonConfigController::class, 'edit'])->name('edit');
        Route::patch('/', [AddonConfigController::class, 'update'])->name('update');
    });

    // ActiveCampaign integration
    Route::name('activecampaign.')->prefix('activecampaign')->group(function () {
        Route::get('/', [FormConfigController::class, 'index'])->name('index')->defaults('integration', 'activecampaign');

        Route::name('form-config.')->group(function () {
            Route::get('/{form}/edit', [FormConfigController::class, 'edit'])->name('edit')->defaults('integration', 'activecampaign');
            Route::patch('/{form}', [FormConfigController::class, 'update'])->name('update')->defaults('integration', 'activecampaign');
            Route::delete('/{form}', [FormConfigController::class, 'destroy'])->name('destroy')->defaults('integration', 'activecampaign');
        });

        Route::get('form-fields/{form}', [ACGetFormFieldsController::class, '__invoke'])->name('form-fields');
        Route::get('merge-fields', [GetMergeFieldsController::class, '__invoke'])->name('merge-fields');
    });

    // HubSpot integration
    Route::name('hubspot.')->prefix('hubspot')->group(function () {
        Route::get('/', [FormConfigController::class, 'index'])->name('index')->defaults('integration', 'hubspot');

        Route::name('form-config.')->group(function () {
            Route::get('/{form}/edit', [FormConfigController::class, 'edit'])->name('edit')->defaults('integration', 'hubspot');
            Route::patch('/{form}', [FormConfigController::class, 'update'])->name('update')->defaults('integration', 'hubspot');
            Route::delete('/{form}', [FormConfigController::class, 'destroy'])->name('destroy')->defaults('integration', 'hubspot');
        });

        Route::get('form-fields/{form}', [HSGetFormFieldsController::class, '__invoke'])->name('form-fields');
        Route::get('contact-properties', [GetContactPropertiesController::class, '__invoke'])->name('contact-properties');
    });
});
