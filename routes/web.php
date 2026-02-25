<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\FinancialController;

// Главная страница
Route::get('/', function () {
    return redirect()->route('login');
});

// Аутентификация
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Защищенные маршруты
Route::middleware(['auth', 'tenant'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Проекты
    Route::resource('projects', ProjectController::class);
    
    // Управление участниками проекта
    Route::post('/projects/{project}/participants', [ProjectController::class, 'addParticipant'])->name('projects.participants.add');
    Route::delete('/projects/{project}/participants/{user}', [ProjectController::class, 'removeParticipant'])->name('projects.participants.remove');
    
    // Комментарии к проекту
    Route::post('/projects/{project}/comments', [ProjectController::class, 'addComment'])->name('projects.comments');
    
    // Файлы проекта
    Route::post('/projects/{project}/files', [ProjectController::class, 'uploadFile'])->name('projects.files.upload');
    
    // Статусы проекта
    Route::post('/projects/{project}/send-to-calculation', [ProjectController::class, 'sendToCalculation'])->name('projects.send-to-calculation');
    Route::post('/projects/{project}/send-to-approval', [ProjectController::class, 'sendToApproval'])->name('projects.send-to-approval');
    Route::post('/projects/{project}/approve', [ProjectController::class, 'approve'])->name('projects.approve');
    Route::post('/projects/{project}/reject', [ProjectController::class, 'reject'])->name('projects.reject');
    Route::post('/projects/{project}/start-implementation', [ProjectController::class, 'startImplementation'])->name('projects.start-implementation');
    
    // Задачи
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');
    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('projects.tasks.create');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::get('/projects/{project}/tasks/{task}', [TaskController::class, 'show'])->name('projects.tasks.show');
    Route::post('/tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
    Route::post('/tasks/{task}/report', [TaskController::class, 'report'])->name('tasks.report');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    
    // Материалы
    Route::get('/projects/{project}/materials', [MaterialController::class, 'index'])->name('projects.materials.index');
    Route::get('/projects/{project}/materials/create', [MaterialController::class, 'create'])->name('projects.materials.create');
    Route::post('/projects/{project}/materials', [MaterialController::class, 'store'])->name('projects.materials.store');
    Route::post('/materials/{delivery}/confirm', [MaterialController::class, 'confirm'])->name('materials.confirm');
    
    // Финансы
    Route::get('/projects/{project}/financial', [FinancialController::class, 'index'])->name('projects.financial.index');
    Route::post('/projects/{project}/financial', [FinancialController::class, 'update'])->name('projects.financial.update');
    
    Route::delete('/projects/{project}/files/{file}', [ProjectController::class, 'deleteFile'])->name('projects.files.delete');
    
    // Отправка расчетов
    Route::post('/projects/{project}/submit-pto', [ProjectController::class, 'submitPTO'])->name('projects.submit-pto');
    Route::post('/projects/{project}/submit-supply', [ProjectController::class, 'submitSupply'])->name('projects.submit-supply');

    // Утверждение отделов
    Route::post('/projects/{project}/approve-pto', [ProjectController::class, 'approvePTO'])->name('projects.approve-pto');
    Route::post('/projects/{project}/approve-supply', [ProjectController::class, 'approveSupply'])->name('projects.approve-supply');

    // Отправка на доработку
    Route::post('/projects/{project}/reject-pto', [ProjectController::class, 'rejectPTO'])->name('projects.reject-pto');
    Route::post('/projects/{project}/reject-supply', [ProjectController::class, 'rejectSupply'])->name('projects.reject-supply');
    
    // Комментарии к задачам
    Route::post('/tasks/{task}/comments', [App\Http\Controllers\TaskCommentController::class, 'store'])->name('tasks.comments');
});