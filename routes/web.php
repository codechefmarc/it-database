<?php

/**
 * @file
 * Routing file for app.
 */

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BulkScanController;
use App\Http\Controllers\TopDeskDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication.
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Admin users.
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
  Route::resource('users', UserController::class)->except(['show']);
});

// User profile - users can edit their own profile.
Route::middleware(['auth'])->group(function () {
  Route::get('/profile', [UserController::class, 'editProfile'])->name('profile.edit');
  Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
});

Route::middleware(['auth'])->group(function () {
  Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
  Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
});

// TopDesk API.
Route::middleware(['auth'])->group(function () {
  Route::get('/topdesk/device-types', [TopDeskDataController::class, 'getDeviceTypes'])->name('getDeviceTypes');
  Route::get('/topdesk/campuses', [TopDeskDataController::class, 'getCampuses'])->name('getCampuses');
  Route::get('/topdesk/buildings', [TopDeskDataController::class, 'getBuildingsByCampus'])->name('getBuildings');
  Route::get('/topdesk/asset-makes', [TopDeskDataController::class, 'getAssetMakes'])->name('getAssetMakes');
  Route::get('/topdesk/asset-models', [TopDeskDataController::class, 'getAssetModels'])->name('getAssetModels');
  Route::get('/topdesk/stock-rooms', [TopDeskDataController::class, 'getStockRooms'])->name('getStockRooms');
  Route::get('/topdesk/templates', [TopDeskDataController::class, 'getTemplates'])->name('getTemplates');
  Route::get('/topdesk/search-assets', [TopDeskDataController::class, 'searchAssets'])->name('searchAssets');
  Route::get('/topdesk/clear-cache', [TopDeskDataController::class, 'clearCache']);
});
