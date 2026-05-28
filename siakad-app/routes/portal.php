<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Routes — Siswa & Orang Tua (SPA via API)
|--------------------------------------------------------------------------
*/

// Portal Siswa
Route::prefix('portal/siswa')->group(function () {
    Route::get('/login', fn() => view('portal.siswa.login'))->name('portal.siswa.login');
    Route::get('/dashboard', fn() => view('portal.siswa.dashboard'))->name('portal.siswa.dashboard');
    Route::get('/grades', fn() => view('portal.siswa.grades'))->name('portal.siswa.grades');
    Route::get('/attendance', fn() => view('portal.siswa.attendance'))->name('portal.siswa.attendance');
    Route::get('/exams', fn() => view('portal.siswa.exams'))->name('portal.siswa.exams');
    Route::get('/exam/{exam}/take', fn($exam) => view('portal.siswa.exam-take', ['examId' => $exam]))->name('portal.siswa.exam.take');
    Route::get('/payments', fn() => view('portal.siswa.payments'))->name('portal.siswa.payments');
    Route::get('/profile', fn() => view('portal.siswa.profile'))->name('portal.siswa.profile');
});

// Portal Orang Tua
Route::prefix('portal/ortu')->group(function () {
    Route::get('/login', fn() => view('portal.ortu.login'))->name('portal.ortu.login');
    Route::get('/dashboard', fn() => view('portal.ortu.dashboard'))->name('portal.ortu.dashboard');
    Route::get('/children', fn() => view('portal.ortu.children'))->name('portal.ortu.children');
    Route::get('/attendance', fn() => view('portal.ortu.attendance'))->name('portal.ortu.attendance');
    Route::get('/bills', fn() => view('portal.ortu.bills'))->name('portal.ortu.bills');
    Route::get('/profile', fn() => view('portal.ortu.profile'))->name('portal.ortu.profile');
});
