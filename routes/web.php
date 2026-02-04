<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterMapelController;
use App\Http\Controllers\MasterStudentController;
use App\Http\Controllers\MasterTeacherController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\SettingsController;

// Auth Routes (Public)
// SECURITY: Login URL changed from /login to /portal-masuk
Route::get('/portal-masuk', [AuthController::class, 'showLogin'])->name('login');
Route::post('/portal-masuk', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post'); // Max 5 attempts/min
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Login Required)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/remind-wali', [DashboardController::class, 'remindWali'])->name('dashboard.remind-wali');
    Route::post('/dashboard/remind-bulk', [DashboardController::class, 'remindBulk'])->name('dashboard.remind-bulk');

    Route::post('/dashboard/notification/read/{id}', [DashboardController::class, 'markNotificationRead'])->name('dashboard.notification.read');

    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // ADMIN ROUTES
    Route::middleware(['role:admin'])->group(function () {
        // Consolidated Settings
        Route::get('settings/users', [App\Http\Controllers\SettingsController::class, 'users'])->name('settings.users.index');
        Route::post('settings/users/export', [App\Http\Controllers\SettingsController::class, 'massGenerateAndExport'])->name('settings.users.export');
        Route::delete('settings/users/bulk-destroy', [App\Http\Controllers\SettingsController::class, 'bulkDestroyUsers'])->name('settings.users.bulk_destroy');
        Route::post('settings/users/{id}/generate', [App\Http\Controllers\SettingsController::class, 'generateUserAccount'])->name('settings.users.generate');
        Route::post('settings/users/{id}/generate', [App\Http\Controllers\SettingsController::class, 'generateUserAccount'])->name('settings.users.generate');
        Route::post('settings/users/{id}/impersonate', [App\Http\Controllers\SettingsController::class, 'impersonate'])->name('settings.users.impersonate');

        // Database Backup
        Route::get('settings/backup/download', [App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
        
        // Role Update
        Route::patch('settings/users/{id}/role', [App\Http\Controllers\SettingsController::class, 'updateRole'])->name('settings.users.role');
        Route::post('settings/users/permissions', [App\Http\Controllers\SettingsController::class, 'updatePermissions'])->name('settings.users.permissions');
        Route::post('settings/users/sync-teacher', [App\Http\Controllers\SettingsController::class, 'syncTeacherAccount'])->name('settings.users.sync-teacher');

        Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/school', [App\Http\Controllers\SchoolSettingController::class, 'index'])->name('settings.school');
        Route::post('/settings/school', [App\Http\Controllers\SchoolSettingController::class, 'update'])->name('settings.school.update');
        
        // Monitoring (Moved to Shared Group)

        Route::post('/settings/year', [App\Http\Controllers\SettingsController::class, 'storeYear'])->name('settings.year.store');
        Route::delete('/settings/year/{id}', [App\Http\Controllers\SettingsController::class, 'destroyYear'])->name('settings.year.destroy'); // NEW
        Route::post('/settings/year/toggle/{id}', [App\Http\Controllers\SettingsController::class, 'toggleYear'])->name('settings.year.toggle');
        Route::post('/settings/weights', [App\Http\Controllers\SettingsController::class, 'storeWeights'])->name('settings.weights.store');
        Route::post('/settings/period/{id}', [App\Http\Controllers\SettingsController::class, 'togglePeriod'])->name('settings.period.toggle');
        Route::post('/settings/period/regenerate/{id}', [App\Http\Controllers\SettingsController::class, 'regeneratePeriods'])->name('settings.period.regenerate');
        Route::post('/settings/kkm', [App\Http\Controllers\SettingsController::class, 'storeKkm'])->name('settings.kkm.store');
        
        // Grading Rules JSON API (Legacy/Transitional)
        Route::get('/settings/grading-rules/{jenjang}', [App\Http\Controllers\SettingsController::class, 'getGradingRules'])->name('settings.grading-rules.json');
        Route::post('/settings/grading-rules', [App\Http\Controllers\SettingsController::class, 'updateGradingRules'])->name('settings.grading-rules.update'); // Old usage
        
        // NEW Standard Form Route
        Route::post('/settings/grading-store', [App\Http\Controllers\SettingsController::class, 'storeGradingRules'])->name('settings.grading.store');

        Route::post('/settings/grading-rules/recalculate', [App\Http\Controllers\SettingsController::class, 'recalculateGrades'])->name('settings.grading-rules.recalculate');

        Route::post('/settings/academic/update-weights', [AcademicSettingController::class, 'updateWeights'])->name('settings.academic.update-weights');
        
        // Deadline & Locking
        Route::get('/settings/deadline', [App\Http\Controllers\DeadlineController::class, 'index'])->name('settings.deadline.index');
        Route::post('/settings/deadline', [App\Http\Controllers\DeadlineController::class, 'update'])->name('settings.deadline.update');
        Route::get('/settings/deadline/toggle/{id}', [App\Http\Controllers\DeadlineController::class, 'toggleLock'])->name('settings.deadline.toggle');
        Route::post('/settings/deadline/update', [SettingsController::class, 'updateDeadline'])->name('settings.deadline.update');
        Route::post('/settings/maintenance/force-calcs', [SettingsController::class, 'recalculateGrades'])->name('settings.maintenance.force-calcs');
        Route::post('/settings/maintenance/update-app', [SettingsController::class, 'updateApplication'])->name('settings.maintenance.update-app');
        Route::get('/settings/deadline/whitelist-remove/{id}', [SettingsController::class, 'removeWhitelist'])->name('settings.deadline.whitelist.remove');
        Route::post('/settings/deadline/whitelist', [App\Http\Controllers\DeadlineController::class, 'storeWhitelist'])->name('settings.deadline.whitelist.store');
        Route::delete('/settings/deadline/whitelist/{id}', [App\Http\Controllers\DeadlineController::class, 'removeWhitelist'])->name('settings.deadline.whitelist.remove');

        // Grading Rules (Deprecated / Duplicate removed to use SettingsController)
        Route::get('/settings/grading-rules', [App\Http\Controllers\GradingRuleController::class, 'index'])->name('settings.grading');
        // Route::get('/settings/grading-rules/json/{jenjang}', [App\Http\Controllers\GradingRuleController::class, 'getRules'])->name('settings.grading-rules.json');
        // Route::post('/settings/grading-rules', [App\Http\Controllers\GradingRuleController::class, 'updateAll'])->name('settings.grading-rules.update');

        // Report Templates
        Route::get('settings/templates', [TemplateController::class, 'index'])->name('settings.templates.index');
        Route::get('settings/templates/create', [TemplateController::class, 'create'])->name('settings.templates.create');
        Route::post('settings/templates', [TemplateController::class, 'store'])->name('settings.templates.store');
        Route::post('settings/templates/config', [App\Http\Controllers\TemplateController::class, 'updateSettings'])->name('settings.templates.config'); // NEW
        Route::post('settings/templates/preview', [TemplateController::class, 'preview'])->name('settings.templates.preview'); // NEW Preview Route
        Route::get('settings/templates/{template}/edit', [TemplateController::class, 'edit'])->name('settings.templates.edit');
        Route::put('settings/templates/{template}', [TemplateController::class, 'update'])->name('settings.templates.update');
        Route::delete('settings/templates/{template}', [TemplateController::class, 'destroy'])->name('settings.templates.destroy');
        Route::post('settings/templates/{template}/activate', [TemplateController::class, 'activate'])->name('settings.templates.activate');

        // Ujian Ijazah Settings
        Route::get('settings/ujian-ijazah', [App\Http\Controllers\IjazahController::class, 'settings'])->name('settings.ijazah.index');
        Route::post('settings/ujian-ijazah', [App\Http\Controllers\IjazahController::class, 'updateSettings'])->name('settings.ijazah.update');

        // Dynamic Menu Manager
        Route::get('settings/menus', [App\Http\Controllers\DynamicMenuController::class, 'index'])->name('settings.menus.index');
        Route::post('settings/menus', [App\Http\Controllers\DynamicMenuController::class, 'store'])->name('settings.menus.store');
        Route::put('settings/menus/{menu}', [App\Http\Controllers\DynamicMenuController::class, 'update'])->name('settings.menus.update');
        Route::delete('settings/menus/{menu}', [App\Http\Controllers\DynamicMenuController::class, 'destroy'])->name('settings.menus.destroy');
        // Dynamic Page Manager
        Route::resource('settings/pages', App\Http\Controllers\DynamicPageController::class, ['as' => 'settings']);

        // Class Management (Moved here for Security)
        Route::get('/classes', [ClassroomController::class, 'index'])->name('classes.index');
        Route::post('/classes', [ClassroomController::class, 'store'])->name('classes.store');
        Route::put('/classes/{id}', [ClassroomController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{id}', [ClassroomController::class, 'destroy'])->name('classes.destroy');
        Route::get('/classes/{id}', [ClassroomController::class, 'show'])->name('classes.show');
        Route::get('/classes/{class}/candidates', [App\Http\Controllers\ClassroomController::class, 'getCandidates'])->name('classes.candidates');
        Route::post('/classes/{class}/add-student', [App\Http\Controllers\ClassroomController::class, 'addStudent'])->name('classes.add-student');
        Route::delete('/classes/{class}/remove-student/{studentId}', [App\Http\Controllers\ClassroomController::class, 'removeStudent'])->name('classes.remove-student');
        Route::post('/classes/{class}/assign-subject', [App\Http\Controllers\ClassroomController::class, 'assignSubject'])->name('classes.assign-subject');
        Route::post('/classes/{class}/update-subject-teacher', [App\Http\Controllers\ClassroomController::class, 'updateSubjectTeacher'])->name('classes.update-subject-teacher');
        Route::post('/classes/{class}/auto-assign-subjects', [App\Http\Controllers\ClassroomController::class, 'autoAssignSubjects'])->name('classes.auto-assign-subjects');
        Route::post('/classes/{class}/reset-subjects', [App\Http\Controllers\ClassroomController::class, 'resetSubjects'])->name('classes.reset-subjects');
        
        // Class Data Pull (Enrollment)
        Route::get('/classes/{class}/sources', [App\Http\Controllers\ClassroomController::class, 'getSourceClasses'])->name('classes.sources');
        Route::post('/classes/{class}/pull', [App\Http\Controllers\ClassroomController::class, 'pullStudents'])->name('classes.pull');
        Route::post('/classes/bulk-promote', [App\Http\Controllers\ClassroomController::class, 'bulkPromote'])->name('classes.bulk-promote');
        Route::post('/classes/reset', [App\Http\Controllers\ClassroomController::class, 'resetActiveClasses'])->name('classes.reset');
    });

    // Public Page Handler (Catch-all for pages)
    Route::get('page/{slug}', [App\Http\Controllers\DynamicPageController::class, 'show'])->name('pages.show');

    // Impersonate Leave (Accessible by anyone, logic handles checking session)
    Route::post('impersonate/leave', [App\Http\Controllers\SettingsController::class, 'stopImpersonating'])->name('impersonate.leave');

    // Teacher Area
    Route::middleware(['role:teacher,admin'])->prefix('teacher')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
        
        // Input Nilai Routes
        Route::get('/input-nilai/{kelas}/{mapel}', [App\Http\Controllers\TeacherDashboardController::class, 'inputNilai'])->name('teacher.input-nilai');
        Route::post('/teacher/input-nilai/store', [App\Http\Controllers\TeacherDashboardController::class, 'storeNilai'])->name('teacher.store-nilai');
        Route::post('/teacher/input-nilai/unlock', [App\Http\Controllers\TeacherDashboardController::class, 'unlockNilai'])->name('teacher.unlock-nilai');
        
        // Import Nilai
        Route::get('/teacher/input-nilai/template/{kelas}/{mapel}', [App\Http\Controllers\TeacherDashboardController::class, 'downloadTemplate'])->name('teacher.input-nilai.template');
        Route::post('/input-nilai/{kelas}/{mapel}/import', [App\Http\Controllers\TeacherDashboardController::class, 'importGrades'])->name('teacher.input-nilai.import');
        Route::post('/input-nilai/process-import', [App\Http\Controllers\TeacherDashboardController::class, 'processImportGrades'])->name('teacher.input-nilai.process');
    });

    // TU Area (New)
    Route::middleware(['role:staff_tu,admin'])->prefix('tu')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\TuController::class, 'index'])->name('tu.dashboard');
        Route::get('/monitoring', [App\Http\Controllers\TuController::class, 'globalMonitoring'])->name('tu.monitoring.global'); 
        Route::get('/dkn', [App\Http\Controllers\TuController::class, 'dkn'])->name('tu.dkn.index');
        
        // DKN Routes (Simple Default + Archive)
        Route::get('/dkn/{kelas}', [App\Http\Controllers\TuController::class, 'showDknSimple'])->name('tu.dkn.show'); // Default simple
        Route::post('/dkn/{kelas}/store', [App\Http\Controllers\TuController::class, 'storeNilaiIjazah'])->name('tu.dkn.store'); // NEW
        Route::get('/dkn/{kelas}/archive', [App\Http\Controllers\TuController::class, 'showDknArchive'])->name('tu.dkn.archive');
        
        Route::get('/rekap/{kelas}', [App\Http\Controllers\WaliKelasController::class, 'legerRekap'])->name('tu.rekap'); 
    });

    // Global Grade Import (Admin)
    Route::middleware(['role:admin'])->prefix('admin/grade-import')->group(function () {
        Route::get('/global', [App\Http\Controllers\GradeImportController::class, 'indexGlobal'])->name('grade.import.global.index');
        Route::get('/global/template/{jenjang}', [App\Http\Controllers\GradeImportController::class, 'downloadTemplateGlobal'])->name('grade.import.global.template');
        Route::post('/global/preview/{jenjang}', [App\Http\Controllers\GradeImportController::class, 'previewGlobal'])->name('grade.import.global.preview');
        Route::post('/global/store', [App\Http\Controllers\GradeImportController::class, 'storeGlobal'])->name('grade.import.global.store');
    });

    // Bulk Grade Import (Wali Kelas & Admin)
    Route::middleware(['role:teacher,admin'])->prefix('grade-import')->group(function () {
        // STATIC ROUTES FIRST
        Route::post('/store', [App\Http\Controllers\GradeImportController::class, 'store'])->name('grade.import.store');

        // DYNAMIC ROUTES ({kelas})
        Route::get('/{kelas}', [App\Http\Controllers\GradeImportController::class, 'index'])->name('grade.import.index');
        Route::get('/{kelas}/template', [App\Http\Controllers\GradeImportController::class, 'downloadTemplate'])->name('grade.import.template');
        Route::get('/{kelas}/preview', function($kelas) {
            return redirect()->route('grade.import.index', $kelas)->with('error', 'Halaman Preview hanya bisa diakses setelah upload file.');
        });
        Route::post('/{kelas}/preview', [App\Http\Controllers\GradeImportController::class, 'preview'])->name('grade.import.preview');
    });

    // Wali Kelas Area
    Route::middleware(['role:teacher,admin'])->prefix('wali-kelas')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\WaliKelasController::class, 'dashboard'])->name('walikelas.dashboard');
        Route::get('/absensi', [App\Http\Controllers\WaliKelasController::class, 'inputAbsensi'])->name('walikelas.absensi');
        Route::post('/absensi', [App\Http\Controllers\WaliKelasController::class, 'storeAbsensi'])->name('walikelas.absensi.store');
        Route::get('/absensi/template', [App\Http\Controllers\WaliKelasController::class, 'downloadAbsensiTemplate'])->name('walikelas.absensi.template');
        Route::post('/absensi/import', [App\Http\Controllers\WaliKelasController::class, 'importAbsensi'])->name('walikelas.absensi.import');
        
        // Unified Import (Leger)
        Route::get('/unified-import/{kelas}', [App\Http\Controllers\UnifiedImportController::class, 'index'])->name('unified.import.index');
        Route::get('/unified-import/{kelas}/template', [App\Http\Controllers\UnifiedImportController::class, 'downloadTemplate'])->name('unified.import.template');
        Route::post('/unified-import/{kelas}/process', [App\Http\Controllers\UnifiedImportController::class, 'processImport'])->name('unified.import.process');
        // Settings (TU)
        Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/year', [App\Http\Controllers\SettingsController::class, 'storeYear'])->name('settings.year.store');
        Route::post('/settings/year/{id}/toggle', [App\Http\Controllers\SettingsController::class, 'toggleYear'])->name('settings.year.toggle');
        Route::post('/settings/period/{id}/update', [App\Http\Controllers\SettingsController::class, 'updatePeriod'])->name('settings.period.update');
        Route::post('/settings/year/{id}/regenerate', [App\Http\Controllers\SettingsController::class, 'regeneratePeriods'])->name('settings.year.regenerate');
        // Identity
        Route::post('/settings/identity', [App\Http\Controllers\SettingsController::class, 'updateIdentity'])->name('settings.identity.update');
        
        // Maintenance (System Health)
        Route::prefix('settings/maintenance')->group(function () {
            Route::post('/reset-promotion', [App\Http\Controllers\MaintenanceController::class, 'resetPromotion'])->name('settings.maintenance.reset-promotion');
            Route::post('/fix-orphans', [App\Http\Controllers\MaintenanceController::class, 'cleanupOrphans'])->name('settings.maintenance.fix-orphans');
            Route::post('/deduplicate-grades', [App\Http\Controllers\MaintenanceController::class, 'deduplicateGrades'])->name('settings.maintenance.deduplicate-grades');
            Route::post('/force-calcs', [App\Http\Controllers\MaintenanceController::class, 'forceFullRecalculation'])->name('settings.maintenance.force-calcs');
            Route::post('/fix-student-status', [App\Http\Controllers\MaintenanceController::class, 'syncStudentStatus'])->name('settings.maintenance.fix-student-status');
            Route::post('/generate-accounts', [App\Http\Controllers\MaintenanceController::class, 'generateMissingAccounts'])->name('settings.maintenance.generate-accounts');
            Route::post('/generate-accounts', [App\Http\Controllers\MaintenanceController::class, 'generateMissingAccounts'])->name('settings.maintenance.generate-accounts');
            Route::post('/sync-history', [App\Http\Controllers\MaintenanceController::class, 'syncPromotionHistory'])->name('settings.maintenance.sync-history');
            Route::post('/system-detox', [App\Http\Controllers\MaintenanceController::class, 'clearSystemCache'])->name('settings.maintenance.system-detox');
            Route::post('/system-reset', [App\Http\Controllers\MaintenanceController::class, 'resetSystem'])->name('settings.maintenance.reset-system');
            Route::post('/fix-jenjang', [App\Http\Controllers\MaintenanceController::class, 'fixClassLevel'])->name('settings.maintenance.fix-jenjang');
            Route::post('/trim-data', [App\Http\Controllers\MaintenanceController::class, 'trimData'])->name('settings.maintenance.trim-data');
            Route::post('/clear-logs', [App\Http\Controllers\MaintenanceController::class, 'clearLogs'])->name('settings.maintenance.clear-logs');
            
            // IJAZAH / DKN ROUTES
            Route::prefix('ijazah')->name('ijazah.')->group(function() {
                Route::get('/', [App\Http\Controllers\IjazahController::class, 'index'])->name('index');
                Route::post('/store', [App\Http\Controllers\IjazahController::class, 'store'])->name('store');
                Route::post('/generate-avg', [App\Http\Controllers\IjazahController::class, 'generateRataRata'])->name('generate-avg');
                Route::get('/template', [App\Http\Controllers\IjazahController::class, 'downloadTemplate'])->name('template');
                Route::post('/import', [App\Http\Controllers\IjazahController::class, 'importGrades'])->name('import');
                Route::get('/print-dkn/{kelasId}', [App\Http\Controllers\IjazahController::class, 'printDKN'])->name('print-dkn');
            });
        });

        // Grading Rules (AJAX for Promotion Settings)
        Route::get('/settings/grading-rules/{jenjang}', [App\Http\Controllers\SettingsController::class, 'getGradingRules'])->name('settings.grading-rules.json');
        Route::post('/settings/grading-rules', [App\Http\Controllers\SettingsController::class, 'updateGradingRules'])->name('settings.grading-rules.update');

        Route::post('/settings/grading-rules/recalculate', [App\Http\Controllers\SettingsController::class, 'recalculateGrades'])->name('settings.grading-rules.recalculate');

        // Deadline & Whitelist
        Route::post('/settings/deadline/update', [App\Http\Controllers\DeadlineController::class, 'update'])->name('settings.deadline.update');
        Route::get('/settings/deadline/toggle/{id}', [App\Http\Controllers\DeadlineController::class, 'toggleLock'])->name('settings.deadline.toggle');
        Route::post('/settings/deadline/whitelist/store', [App\Http\Controllers\DeadlineController::class, 'storeWhitelist'])->name('settings.deadline.whitelist.store');
        Route::delete('/settings/deadline/whitelist/{id}', [App\Http\Controllers\DeadlineController::class, 'removeWhitelist'])->name('settings.deadline.whitelist.remove');
        
        Route::get('/catatan', [App\Http\Controllers\WaliKelasController::class, 'inputCatatan'])->name('walikelas.catatan.index');
        Route::post('/catatan', [App\Http\Controllers\WaliKelasController::class, 'storeCatatan'])->name('walikelas.catatan.store');
        
        // Kenaikan Kelas (New)
        Route::get('/kenaikan-kelas', [App\Http\Controllers\WaliKelasController::class, 'kenaikanKelas'])->name('walikelas.kenaikan.index');
        Route::post('/kenaikan-kelas', [App\Http\Controllers\WaliKelasController::class, 'storeKenaikanKelas'])->name('walikelas.kenaikan.store');

        // Admin Promotion Routes
        Route::get('/promotion', [App\Http\Controllers\PromotionController::class, 'index'])->name('promotion.index');
        Route::post('/promotion/update', [App\Http\Controllers\PromotionController::class, 'updateDecision'])->name('promotion.update');
        Route::post('/promotion/process', [App\Http\Controllers\PromotionController::class, 'processPromotion'])->name('promotion.process');
        Route::post('/promotion/process-all', [App\Http\Controllers\PromotionController::class, 'processAll'])->name('promotion.process_all');
        Route::post('/promotion/finalize', [App\Http\Controllers\PromotionController::class, 'finalize'])->name('promotion.finalize');

        Route::get('/ekskul', [App\Http\Controllers\WaliKelasController::class, 'inputEkskul'])->name('ekskul.index');
        Route::post('/ekskul', [App\Http\Controllers\WaliKelasController::class, 'storeEkskul'])->name('walikelas.ekskul.store');
        Route::get('/leger', [App\Http\Controllers\WaliKelasController::class, 'leger'])->name('walikelas.leger');
        
        // Promotion (Kenaikan Kelas) - Accessed by Wali Kelas
        Route::get('/promotion', [App\Http\Controllers\PromotionController::class, 'index'])->name('promotion.index');
        Route::post('/promotion/update', [App\Http\Controllers\PromotionController::class, 'updateDecision'])->name('promotion.update');
        Route::post('/promotion/process', [App\Http\Controllers\PromotionController::class, 'processPromotion'])->name('promotion.process');

        // Katrol Nilai (Grade Adjustment)
        Route::get('/katrol', [App\Http\Controllers\GradeAdjustmentController::class, 'index'])->name('walikelas.katrol.index');
        Route::post('/katrol', [App\Http\Controllers\GradeAdjustmentController::class, 'store'])->name('walikelas.katrol.store');

        // Monitoring Access for Wali Kelas
        Route::get('/monitoring', [App\Http\Controllers\WaliKelasController::class, 'monitoring'])->name('walikelas.monitoring');
        Route::post('/monitoring/finalize', [App\Http\Controllers\WaliKelasController::class, 'bulkFinalize'])->name('walikelas.monitoring.finalize'); // NEW
        Route::get('/leger', [App\Http\Controllers\WaliKelasController::class, 'leger'])->name('walikelas.leger');
        Route::get('/leger-rekap', [App\Http\Controllers\WaliKelasController::class, 'legerRekap'])->name('walikelas.leger.rekap');
        Route::get('/leger/rekap/export', [App\Http\Controllers\WaliKelasController::class, 'exportLegerRekap'])->name('walikelas.leger.rekap.export');
        Route::get('/leger/export', [App\Http\Controllers\WaliKelasController::class, 'exportLeger'])->name('walikelas.leger.export');
    });



    // Class Management

    




    // Master Data (ADMIN)
    Route::middleware(['role:admin'])->group(function() {
        Route::post('master/students/import', [MasterStudentController::class, 'import'])->name('master.students.import');
        Route::post('master/students/import/process', [MasterStudentController::class, 'processImport'])->name('master.students.import.process');
        Route::delete('master/students/bulk-destroy', [MasterStudentController::class, 'bulkDestroy'])->name('master.students.bulk_destroy');
        Route::get('master/students/template', [MasterStudentController::class, 'downloadTemplate'])->name('master.students.template');
        Route::resource('master/students', MasterStudentController::class, ['as' => 'master']);
        
        // Teachers
        Route::post('master/teachers/import', [MasterTeacherController::class, 'import'])->name('master.teachers.import');
        Route::get('master/teachers/template', [MasterTeacherController::class, 'downloadTemplate'])->name('master.teachers.template');
        Route::delete('master/teachers/destroy-all', [MasterTeacherController::class, 'destroyAll'])->name('master.teachers.destroy-all'); // NEW
        Route::resource('master/teachers', MasterTeacherController::class, ['as' => 'master']);
        Route::post('master/teachers/{id}/password', [MasterTeacherController::class, 'updatePassword'])->name('master.teachers.password');
        Route::post('master/teachers/{id}/generate', [MasterTeacherController::class, 'generateAccount'])->name('master.teachers.generate');
        
        Route::post('master/students/{id}/update-status', [MasterStudentController::class, 'updateStatus'])->name('master.students.updateStatus');
        Route::post('master/students/{id}/restore', [MasterStudentController::class, 'restore'])->name('master.students.restore');
        Route::post('master/students/history/{id}/update-status', [MasterStudentController::class, 'updateHistoryStatus'])->name('master.students.update-history-status');
        
        // Master Mapel
        Route::get('/master/mapel', [MasterMapelController::class, 'index'])->name('master.mapel.index');
        Route::post('/master/mapel', [MasterMapelController::class, 'store'])->name('master.mapel.store');
        Route::put('/master/mapel/{id}', [MasterMapelController::class, 'update'])->name('master.mapel.update');
        Route::delete('/master/mapel/destroy-all', [MasterMapelController::class, 'destroyAll'])->name('master.mapel.destroy-all'); // BEFORE {id}
        Route::delete('/master/mapel/{id}', [MasterMapelController::class, 'destroy'])->name('master.mapel.destroy');
        Route::get('/master/mapel/plotting', [MasterMapelController::class, 'plotting'])->name('master.mapel.plotting');
        Route::get('/master/mapel/get-plotting-data', [MasterMapelController::class, 'getPlottingData'])->name('master.mapel.get-plotting-data');
        Route::get('/master/mapel/get-plotting-data', [MasterMapelController::class, 'getPlottingData'])->name('master.mapel.get-plotting-data');
        Route::post('/master/mapel/copy-plotting', [MasterMapelController::class, 'copyPlotting'])->name('master.mapel.copy-plotting');
        Route::post('/master/mapel/save-plotting', [MasterMapelController::class, 'savePlotting'])->name('master.mapel.save-plotting');
        
        // Import Mapel
        Route::get('/master/mapel/template', [MasterMapelController::class, 'downloadTemplate'])->name('master.mapel.template');
        Route::post('/master/mapel/import', [MasterMapelController::class, 'import'])->name('master.mapel.import');
        // ... existing routes ...
        
        // GLOBAL ATTENDANCE IMPORT
        Route::get('/attendance-import', [App\Http\Controllers\AttendanceImportController::class, 'indexGlobal'])->name('admin.attendance.import.index');
        Route::get('/attendance-import/template/{jenjang}', [App\Http\Controllers\AttendanceImportController::class, 'downloadTemplateGlobal'])->name('admin.attendance.import.template');
        Route::post('/attendance-import/preview/{jenjang}', [App\Http\Controllers\AttendanceImportController::class, 'previewGlobal'])->name('admin.attendance.import.preview');
        Route::post('/attendance-import/store', [App\Http\Controllers\AttendanceImportController::class, 'storeGlobal'])->name('admin.attendance.import.store');
        Route::post('/attendance-import/store', [App\Http\Controllers\AttendanceImportController::class, 'storeGlobal'])->name('admin.attendance.import.store');
    });

    // TU Routes (Explicit)
    Route::group(['middleware' => ['role:admin,staff_tu']], function () {
        Route::get('/tu/dkn/export/{kelas}', [App\Http\Controllers\TuController::class, 'downloadDknExcel'])->name('tu.dkn.export_excel');
    });
    
    Route::middleware(['role:teacher,admin,staff_tu'])->prefix('reports')->group(function () {
        Route::get('/leger/rekap/export', [App\Http\Controllers\ReportController::class, 'exportLegerRekap'])->name('reports.leger.rekap.export');
        Route::get('/leger/export', [App\Http\Controllers\ReportController::class, 'exportLeger'])->name('reports.leger.export');
        Route::get('/leger', [App\Http\Controllers\ReportController::class, 'leger'])->name('reports.leger');
        
        // Student Analytics
        Route::get('/student/{student}/analytics', [App\Http\Controllers\ReportController::class, 'studentAnalytics'])->name('reports.student.analytics');
        
        Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('/print/{student}', [App\Http\Controllers\ReportController::class, 'printRapor'])->name('reports.print');
        Route::get('/cover/{student}', [App\Http\Controllers\ReportController::class, 'printCover'])->name('reports.print.cover');
        Route::get('/biodata/{student}', [App\Http\Controllers\ReportController::class, 'printBiodata'])->name('reports.print.biodata');
        Route::get('/print-all/{class}', [App\Http\Controllers\ReportController::class, 'printClass'])->name('reports.print.all');
    });

});
