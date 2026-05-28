<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Api\Student\GradeController as StudentGrade;
use App\Http\Controllers\Api\Student\AttendanceController as StudentAttendance;
use App\Http\Controllers\Api\Student\ProfileController as StudentProfile;
use App\Http\Controllers\Api\Student\ExamController as StudentExam;
use App\Http\Controllers\Api\Guardian\DashboardController as GuardianDashboard;
use App\Http\Controllers\Api\Guardian\PaymentController as GuardianPayment;
use App\Http\Controllers\Api\Teacher\GradeController as TeacherGrade;
use App\Http\Controllers\Api\Teacher\ExamController as TeacherExam;
use App\Http\Controllers\Api\Teacher\AttendanceController as TeacherAttendance;
use App\Http\Controllers\Api\Teacher\ReportController as TeacherReport;
use App\Http\Controllers\Api\Teacher\InvoiceController as TeacherInvoice;
use App\Http\Controllers\Api\Teacher\PaymentController as TeacherPayment;
use App\Http\Controllers\Backend\SettingsController;
use App\Http\Controllers\Backend\FingerprintController;
use App\Enums\Role;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SIAKAD API Routes
|--------------------------------------------------------------------------
|
| Terbagi menjadi: Auth publik, Portal (Siswa + Ortu), Backend (Guru dll)
|
*/

// ─── AUTH ──────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    // Public: Info sekolah
    Route::get('/school/info', [SettingsController::class, 'apiShow']);

    // Public: Waktu server
    Route::get('/server-time', function () {
        return response()->json([
            'server_time' => now()->toISOString(),
            'timestamp' => now()->timestamp,
            'timezone' => config('app.timezone'),
        ]);
    });

    // Public: Fingerprint push (dari mesin absen)
    Route::post('/fingerprint/push', [FingerprintController::class, 'apiPush']);

    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // ─── PORTAL SISWA ─────────────────────────────────────
        Route::middleware('role:siswa')->prefix('student')->group(function () {
            Route::get('/dashboard', [StudentDashboard::class, 'index']);
            Route::get('/profile', [StudentProfile::class, 'show']);
            Route::put('/profile', [StudentProfile::class, 'update']);
            Route::get('/grades', [StudentGrade::class, 'index']);
            Route::get('/grades/{grade}', [StudentGrade::class, 'show']);
            Route::get('/attendance', [StudentAttendance::class, 'index']);
            Route::get('/attendance/summary', [StudentAttendance::class, 'summary']);
            Route::get('/reports/{semesterId}', [StudentDashboard::class, 'report']);
            Route::get('/exam/schedule', [StudentDashboard::class, 'examSchedule']);
            Route::post('/exam/{exam}/start', [StudentExam::class, 'startExam']);
            Route::get('/exam/{exam}/questions', [StudentExam::class, 'getQuestions']);
            Route::post('/exam/{exam}/answer', [StudentExam::class, 'submitAnswer']);
            Route::post('/exam/{exam}/finish', [StudentExam::class, 'finishExam']);
            Route::put('/exam/{exam}/time', [StudentExam::class, 'saveTime']);
            Route::get('/payments', [StudentDashboard::class, 'payments']);
        });

        // ─── PORTAL ORANG TUA ─────────────────────────────────
        Route::middleware('role:orang_tua')->prefix('guardian')->group(function () {
            Route::get('/dashboard', [GuardianDashboard::class, 'index']);
            Route::get('/children', [GuardianDashboard::class, 'children']);
            Route::get('/children/{student}/grades', [GuardianDashboard::class, 'childGrades']);
            Route::get('/children/{student}/attendance', [GuardianDashboard::class, 'childAttendance']);
            Route::get('/bills', [GuardianPayment::class, 'bills']);
            Route::get('/bills/{invoice}', [GuardianPayment::class, 'billDetail']);
            Route::post('/payments/{invoice}/pay', [GuardianPayment::class, 'pay']);
            Route::get('/payments/history', [GuardianPayment::class, 'history']);
        });

        // ─── BACKEND ADMIN ─────────────────────────────────────
        require __DIR__ . '/admin-api.php';

        // ─── BACKEND GURU + WALI KELAS ────────────────────────
        Route::middleware('role:guru,walikelas')->prefix('teacher')->group(function () {
            // Nilai
            Route::get('/class-subjects', [TeacherGrade::class, 'classSubjects']);
            Route::get('/grades', [TeacherGrade::class, 'index']);
            Route::post('/grades', [TeacherGrade::class, 'store']);
            Route::put('/grades/{grade}', [TeacherGrade::class, 'update']);
            Route::delete('/grades/{grade}', [TeacherGrade::class, 'destroy']);

            // Ujian
            Route::get('/exams', [TeacherExam::class, 'index']);
            Route::post('/exams', [TeacherExam::class, 'store']);
            Route::get('/exams/{exam}', [TeacherExam::class, 'show']);
            Route::put('/exams/{exam}', [TeacherExam::class, 'update']);
            Route::post('/exams/{exam}/questions', [TeacherExam::class, 'addQuestions']);
            Route::get('/exams/{exam}/results', [TeacherExam::class, 'results']);
            Route::post('/exam-results/{result}/grade', [TeacherExam::class, 'grade']);
            Route::delete('/exam-results/{result}', [TeacherExam::class, 'destroyResult']);

            // Presensi
            Route::get('/attendance/jadwal-hari-ini', [TeacherAttendance::class, 'jadwalHariIni']);
            Route::get('/attendance', [TeacherAttendance::class, 'index']);
            Route::post('/attendance', [TeacherAttendance::class, 'store']);
            Route::post('/attendance/bulk', [TeacherAttendance::class, 'bulkStore']);
            Route::put('/attendance/{id}', [TeacherAttendance::class, 'update']);
            Route::get('/attendance/recap', [TeacherAttendance::class, 'recap']);

            // Rapor (Wali Kelas only)
            Route::get('/reports/preview', [TeacherReport::class, 'preview']);
            Route::post('/reports/lock', [TeacherReport::class, 'lock']);
        });

        // ─── BACKEND BENDAHARA ─────────────────────────────────
        Route::middleware('role:bendahara,admin')->prefix('finance')->group(function () {
            Route::get('/fee-types', [TeacherInvoice::class, 'feeTypes']);
            Route::post('/fee-types', [TeacherInvoice::class, 'createFeeType']);
            Route::get('/invoices', [TeacherInvoice::class, 'index']);
            Route::post('/invoices/generate', [TeacherInvoice::class, 'generate']);
            Route::get('/invoices/{invoice}', [TeacherInvoice::class, 'show']);
            Route::post('/invoices/{invoice}/void', [TeacherInvoice::class, 'void']);
            Route::get('/payments', [TeacherPayment::class, 'index']);
            Route::post('/payments/{payment}/verify', [TeacherPayment::class, 'verify']);
            Route::post('/payments/{payment}/reject', [TeacherPayment::class, 'reject']);
            Route::get('/payments/report', [TeacherPayment::class, 'report']);
        });

        // ─── BACKEND KEPALA SEKOLAH ────────────────────────────
        Route::middleware('role:kepsek')->prefix('principal')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Api\Principal\DashboardController::class, 'index']);
        });

        // ─── STAFF / PEGAWAI ──────────────────────────────────
        Route::middleware('role:' . implode(',', Role::internalRoles()))
            ->prefix('staff')->group(function () {
                // Pegawai lihat absensi pribadi
                Route::get('/attendance', [App\Http\Controllers\Api\Staff\StaffAttendanceController::class, 'index']);
                Route::get('/attendance/summary', [App\Http\Controllers\Api\Staff\StaffAttendanceController::class, 'summary']);
                // Pegawai isi absensi sendiri (self-service)
                Route::post('/attendance/self', [App\Http\Controllers\Api\Staff\StaffAttendanceController::class, 'selfStore']);
                Route::put('/attendance/self', [App\Http\Controllers\Api\Staff\StaffAttendanceController::class, 'selfUpdate']);
                // Kepsek/admin: rekap & daily grid
                Route::get('/attendance/recap', [App\Http\Controllers\Api\Staff\StaffAttendanceController::class, 'recap']);
                Route::get('/attendance/daily', [App\Http\Controllers\Api\Staff\StaffAttendanceController::class, 'daily']);
            });

    }); // end auth:sanctum
}); // end v1 prefix
