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
  Route::get('/topdesk/campuses', [TopDeskDataController::class, 'getCampuses'])->name('getCampuses');
  Route::get('/topdesk/buildings', [TopDeskDataController::class, 'getBuildingsByCampus'])->name('getBuildings');
  Route::get('/topdesk/asset-makes', [TopDeskDataController::class, 'getAssetMakes'])->name('getAssetMakes');
  Route::post('/topdesk/clear-cache', [TopDeskDataController::class, 'clearCache']);
});
