<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\LoginController;
use App\Http\Controllers\Backend\MasterController;
use App\Http\Controllers\Backend\AcademicController;
use App\Http\Controllers\Backend\ExamController;
use App\Http\Controllers\Backend\FinanceController;
use App\Http\Controllers\Backend\StaffController;
use App\Http\Controllers\Backend\SettingsController;
use App\Http\Controllers\Backend\DapodikController;
use App\Http\Controllers\Backend\AttendanceManualController;
use App\Http\Controllers\Backend\FingerprintController;
use App\Enums\Role;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Backend Panel SIAKAD
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect('/portal/siswa/login'));
require __DIR__ . '/portal.php';

// Login
Route::get('/backend/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/backend/login', [LoginController::class, 'login'])->name('login.submit');

// Pilih Kurikulum (interstitial setelah login)
Route::middleware(['auth'])->group(function () {
    Route::get('/backend/pilih-kurikulum', [LoginController::class, 'selectCurriculum'])->name('curriculum.select.form');
    Route::post('/backend/pilih-kurikulum', [LoginController::class, 'storeCurriculum'])->name('curriculum.select');
});

// Backend panel (session auth)
Route::middleware(['auth', 'role:' . implode(',', Role::internalRoles())])
    ->prefix('backend')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        // ─── SUPERADMIN & ADMIN ONLY (manajemen user) ──────────
        Route::middleware(['role:superadmin,admin'])->group(function () {
            Route::get('/master/users', [MasterController::class, 'users'])->name('master.users');
            Route::post('/master/users', [MasterController::class, 'storeUser'])->name('master.users.store');
            Route::put('/master/users/{user}', [MasterController::class, 'updateUser'])->name('master.users.update');
            Route::post('/master/users/{user}/delete', [MasterController::class, 'deleteUser'])->name('master.users.delete');
            Route::post('/master/users/{user}/toggle-status', [MasterController::class, 'toggleStatus'])->name('master.users.toggle-status');
            // Semester delete
            Route::post('/semesters/{semester}/delete', [MasterController::class, 'deleteSemester'])->name('master.semesters.delete');
            // Academic Year edit & delete
            Route::put('/academic-years/{year}', [MasterController::class, 'updateAcademicYear'])->name('master.academic-years.update');
            Route::post('/academic-years/{year}/delete', [MasterController::class, 'deleteAcademicYear'])->name('master.academic-years.delete');
            // Settings
            Route::get('/settings', [SettingsController::class, 'edit'])->name('backend.settings.edit');
            Route::put('/settings', [SettingsController::class, 'update'])->name('backend.settings.update');
        });

        // ─── ADMIN-ONLY ROUTES (guru tidak bisa akses) ──────────
        Route::middleware(['role:' . implode(',', array_diff(Role::internalRoles(), ['guru']))])->group(function () {
            // Teachers
            Route::get('/master/teachers', [MasterController::class, 'teachers'])->name('master.teachers');

            // Master Data (guru tidak bisa akses)
            Route::prefix('master')->group(function () {
                // Classes
                Route::get('/classes', [MasterController::class, 'classes'])->name('master.classes');
                Route::post('/classes', [MasterController::class, 'storeClass'])->name('master.classes.store');
                Route::put('/classes/{class}', [MasterController::class, 'updateClass'])->name('master.classes.update');
                Route::post('/classes/{class}/delete', [MasterController::class, 'deleteClass'])->name('master.classes.delete');

                // Subjects
                Route::get('/subjects', [MasterController::class, 'subjects'])->name('master.subjects');
                Route::post('/subjects', [MasterController::class, 'storeSubject'])->name('master.subjects.store');
                Route::put('/subjects/{subject}', [MasterController::class, 'updateSubject'])->name('master.subjects.update');
                Route::post('/subjects/{subject}/delete', [MasterController::class, 'deleteSubject'])->name('master.subjects.delete');

                // Students
                Route::get('/students', [MasterController::class, 'students'])->name('master.students');
                Route::get('/students/{student}', [MasterController::class, 'showStudent'])->name('master.students.show');
                Route::post('/students', [MasterController::class, 'storeStudent'])->name('master.students.store');
                Route::put('/students/{student}', [MasterController::class, 'updateStudent'])->name('master.students.update');
                Route::post('/students/{student}/delete', [MasterController::class, 'deleteStudent'])->name('master.students.delete');

                // Guardians (Orang Tua / Wali)
                Route::get('/guardians', [MasterController::class, 'guardians'])->name('master.guardians');
                Route::post('/guardians', [MasterController::class, 'storeGuardian'])->name('master.guardians.store');
                Route::put('/guardians/{guardian}', [MasterController::class, 'updateGuardian'])->name('master.guardians.update');
                Route::post('/guardians/{guardian}/delete', [MasterController::class, 'deleteGuardian'])->name('master.guardians.delete');

                // Academic Setup
                Route::get('/academic-setup', [MasterController::class, 'academicSetup'])->name('master.academic-setup');
                Route::post('/academic-years', [MasterController::class, 'storeAcademicYear'])->name('master.academic-years.store');
                Route::post('/academic-years/{year}/toggle', [MasterController::class, 'toggleAcademicYear'])->name('master.academic-years.toggle');
                Route::post('/semesters/{year}', [MasterController::class, 'storeSemester'])->name('master.semesters.store');
                Route::post('/semesters/{semester}/toggle', [MasterController::class, 'toggleSemester'])->name('master.semesters.toggle');
                Route::put('/semesters/{semester}', [MasterController::class, 'updateSemester'])->name('master.semesters.update');

                // Class-Subject Mapping
                Route::get('/class-subject', [MasterController::class, 'classSubjectMapping'])->name('master.class-subject');
                Route::post('/class-subject', [MasterController::class, 'storeClassSubject'])->name('master.class-subject.store');
                Route::put('/class-subject/{mapping}', [MasterController::class, 'updateClassSubject'])->name('master.class-subject.update');
                Route::post('/class-subject/{mapping}/delete', [MasterController::class, 'deleteClassSubject'])->name('master.class-subject.delete');
            });
            // Academic - admin only
            Route::get('/academic/p5', [AcademicController::class, 'p5'])->name('academic.p5');
            Route::post('/academic/p5/projects', [AcademicController::class, 'storeP5Project'])->name('academic.p5.project.store');
            Route::put('/academic/p5/projects/{project}', [AcademicController::class, 'updateP5Project'])->name('academic.p5.project.update');
            Route::post('/academic/p5/projects/{project}/delete', [AcademicController::class, 'destroyP5Project'])->name('academic.p5.project.destroy');
            Route::post('/academic/p5/assessments', [AcademicController::class, 'storeP5Assessment'])->name('academic.p5.assessment.store');
            // Finance - all
            Route::get('/finance/fee-types', [FinanceController::class, 'feeTypes'])->name('finance.fee-types');
            Route::post('/finance/fee-types', [FinanceController::class, 'storeFeeType'])->name('finance.fee-types.store');
            Route::get('/finance/invoices', [FinanceController::class, 'invoices'])->name('finance.invoices');
            Route::post('/finance/invoices/generate', [FinanceController::class, 'generateInvoices'])->name('finance.invoices.generate');
            Route::get('/finance/payments', [FinanceController::class, 'payments'])->name('finance.payments');
            Route::post('/finance/payments', [FinanceController::class, 'storePayment'])->name('finance.payments.store');
            Route::post('/finance/payments/{payment}/verify', [FinanceController::class, 'verifyPayment'])->name('finance.payments.verify');
            Route::get('/finance/reports', [FinanceController::class, 'reports'])->name('finance.reports');
            // Staff - data pegawai only
            Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
            Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
            Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
            Route::post('/staff/{staff}/toggle', [StaffController::class, 'toggleActive'])->name('staff.toggle');
            Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

            // Absensi Pegawai
            Route::get('/staff/attendance', [StaffController::class, 'attendanceGrid'])->name('staff.attendance.grid');
            Route::post('/staff/attendance/store', [StaffController::class, 'storeAttendance'])->name('staff.attendance.store');
            Route::post('/staff/attendance/bulk', [StaffController::class, 'bulkAttendance'])->name('staff.attendance.bulk');
            Route::get('/staff/attendance/recap', [StaffController::class, 'attendanceRecap'])->name('staff.attendance.recap');
            Route::get('/staff/attendance/export', [StaffController::class, 'exportAttendance'])->name('staff.attendance.export');
            Route::get('/staff/attendance/import', [StaffController::class, 'importForm'])->name('staff.attendance.import');
            Route::post('/staff/attendance/import', [StaffController::class, 'importAttendance'])->name('staff.attendance.import');
        });

        // ─── AKADEMIK (shared — guru bisa akses) ──────────────────
        // Curriculum
        Route::get('/academic/curriculum', [AcademicController::class, 'curriculum'])->name('academic.curriculum');
        Route::post('/academic/curricula', [AcademicController::class, 'storeCurriculum'])->name('academic.curricula.store');
        Route::post('/academic/learning-outcomes', [AcademicController::class, 'storeCP'])->name('academic.cp.store');
        Route::post('/academic/learning-objectives', [AcademicController::class, 'storeTP'])->name('academic.tp.store');
        Route::post('/academic/atp-mapping', [AcademicController::class, 'storeATP'])->name('academic.atp.store');

        // Attendance
        Route::get('/academic/attendance', [AcademicController::class, 'attendance'])->name('academic.attendance');
        Route::post('/academic/attendance', [AcademicController::class, 'storeAttendance'])->name('academic.attendance.store');
        Route::post('/academic/attendance/bulk', [AcademicController::class, 'bulkAttendance'])->name('academic.attendance.bulk');
        Route::get('/academic/attendance/recap', [AcademicController::class, 'attendanceRecap'])->name('academic.attendance.recap');

        // Grades
        Route::get('/academic/grades', [AcademicController::class, 'grades'])->name('academic.grades');
        Route::post('/academic/grades', [AcademicController::class, 'storeGrade'])->name('academic.grades.store');
        Route::post('/academic/grades/bulk', [AcademicController::class, 'bulkStore'])->name('academic.grades.bulk');
        Route::post('/academic/grades/{grade}/delete', [AcademicController::class, 'deleteGrade'])->name('academic.grades.delete');

        // Reports
        Route::get('/academic/reports', [AcademicController::class, 'reports'])->name('academic.reports');
        Route::post('/academic/reports/store', [AcademicController::class, 'storeReport'])->name('academic.reports.store');
        Route::post('/academic/reports/lock', [AcademicController::class, 'lockReports'])->name('academic.reports.lock');
        Route::post('/academic/reports/unlock', [AcademicController::class, 'unlockReports'])->name('academic.reports.unlock');
        Route::post('/academic/reports/{student}/toggle-lock', [AcademicController::class, 'toggleLock'])->name('academic.reports.toggle-lock');
        Route::post('/academic/reports/{report}/delete', [AcademicController::class, 'deleteReport'])->name('academic.reports.delete');
        Route::get('/academic/reports/{student}/edit', [AcademicController::class, 'editReport'])->name('academic.reports.edit');
        Route::get('/academic/reports/{student}', [AcademicController::class, 'showReport'])->name('academic.reports.show');

        // ─── EXAM ────────────────────────────────────────────────
        Route::get('/exam/banks', [ExamController::class, 'banks'])->name('exam.banks');
        Route::post('/exam/banks', [ExamController::class, 'storeBank'])->name('exam.banks.store');
        Route::get('/exam/questions', [ExamController::class, 'questions'])->name('exam.questions');
        Route::post('/exam/questions', [ExamController::class, 'storeQuestion'])->name('exam.questions.store');
        Route::post('/exam/questions/{question}/delete', [ExamController::class, 'deleteQuestion'])->name('exam.questions.delete');
        Route::put('/exam/questions/{question}', [ExamController::class, 'updateQuestion'])->name('exam.questions.update');
        Route::get('/exam/list', [ExamController::class, 'exams'])->name('exam.list');
        Route::post('/exam/list', [ExamController::class, 'storeExam'])->name('exam.list.store');
        Route::post('/exam/list/{exam}/questions', [ExamController::class, 'addExamQuestions'])->name('exam.list.questions');
        Route::put('/exam/list/{exam}', [ExamController::class, 'updateExam'])->name('exam.list.update');
        Route::delete('/exam/list/{exam}', [ExamController::class, 'destroyExam'])->name('exam.list.delete');
        Route::get('/exam/results', [ExamController::class, 'results'])->name('exam.results');
        Route::post('/exam/results/{result}/grade', [ExamController::class, 'gradeEssay'])->name('exam.results.grade');
        Route::post('/exam/results/{result}/grade-answers', [ExamController::class, 'gradeAnswers'])->name('exam.results.grade-answers');
        Route::delete('/exam/results/{result}', [ExamController::class, 'deleteResult'])->name('exam.results.delete');

        // ─── ABSENSI MANUAL (shared — guru bisa akses) ──────────
        Route::get('/attendance/siswa-manual', [AttendanceManualController::class, 'siswaForm'])->name('attendance.siswa.manual');
        Route::post('/attendance/siswa-manual', [AttendanceManualController::class, 'siswaStore'])->name('attendance.siswa.store');
        Route::get('/attendance/pegawai-manual', [AttendanceManualController::class, 'pegawaiForm'])->name('attendance.pegawai.form');
        Route::post('/attendance/pegawai-manual', [AttendanceManualController::class, 'pegawaiStore'])->name('attendance.pegawai.store');

        // ─── FINGERPRINT & DAPODIK (admin-only) ──────────────────
        Route::middleware(['role:superadmin,admin'])->group(function () {
            Route::get('/fingerprint', [FingerprintController::class, 'index'])->name('fingerprint.index');
            Route::post('/fingerprint/devices', [FingerprintController::class, 'storeDevice'])->name('fingerprint.device.store');
            Route::put('/fingerprint/devices/{id}', [FingerprintController::class, 'updateDevice'])->name('fingerprint.device.update');
            Route::delete('/fingerprint/devices/{id}', [FingerprintController::class, 'deleteDevice'])->name('fingerprint.device.delete');
            Route::get('/fingerprint/upload', [FingerprintController::class, 'uploadLogForm'])->name('fingerprint.upload');
            Route::post('/fingerprint/upload', [FingerprintController::class, 'uploadLog'])->name('fingerprint.log.upload');
            Route::post('/fingerprint/process', [FingerprintController::class, 'processLogs'])->name('fingerprint.log.process');
            Route::post('/fingerprint/pin', [FingerprintController::class, 'storePinMapping'])->name('fingerprint.pin.store');
            Route::delete('/fingerprint/pin/{id}', [FingerprintController::class, 'deletePinMapping'])->name('fingerprint.pin.delete');

            Route::get('/dapodik', [DapodikController::class, 'index'])->name('dapodik.index');
            Route::post('/dapodik/export', [DapodikController::class, 'export'])->name('dapodik.export');
            Route::get('/dapodik/download', [DapodikController::class, 'download'])->name('dapodik.download');
            Route::get('/dapodik/status', [DapodikController::class, 'status'])->name('dapodik.status');
            Route::get('/dapodik/import', [DapodikController::class, 'importForm'])->name('dapodik.import.form');
            Route::post('/dapodik/import', [DapodikController::class, 'import'])->name('dapodik.import');
            Route::get('/dapodik/mappings', [DapodikController::class, 'mappings'])->name('dapodik.mappings');
            Route::post('/dapodik/mappings', [DapodikController::class, 'updateMapping'])->name('dapodik.mappings.update');
            Route::get('/dapodik/logs', [DapodikController::class, 'logs'])->name('dapodik.logs');
        });
    });
