<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MasterStudentController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\SettingsController; // Use the Correct Controller
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\WaliKelasController;
use App\Http\Controllers\ExportLegerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MonitoringController; 
use App\Http\Controllers\GradeImportController;

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

// Auth Routes (Manually defined instead of Auth::routes())
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ... [EXISTING ROUTES] ...
    
    // --- Settings Routes ---
    // Ensure this route exists specifically for the form action
    Route::post('/settings/general/update', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
    
    // ...
});
