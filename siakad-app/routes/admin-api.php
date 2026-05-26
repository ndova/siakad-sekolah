<?php

use App\Http\Controllers\Api\Admin\MasterController;
use Illuminate\Support\Facades\Route;

// ─── ADMIN MASTER DATA ────────────────────────────────────────
Route::middleware('role:superadmin,admin')->prefix('admin')->group(function () {
    Route::get('/users', [MasterController::class, 'users']);
    Route::get('/classes', [MasterController::class, 'classes']);
    Route::get('/subjects', [MasterController::class, 'subjects']);
    // Students CRUD
    Route::get('/students', [MasterController::class, 'students']);
    Route::get('/students/{student}', [MasterController::class, 'showStudent']);
    Route::post('/students', [MasterController::class, 'storeStudent']);
    Route::put('/students/{student}', [MasterController::class, 'updateStudent']);
    Route::delete('/students/{student}', [MasterController::class, 'destroyStudent']);
});
