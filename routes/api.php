<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('/me', [AuthController::class, 'users']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('isAdmin')->group(function () {
        Route::get('/users/export', [UserController::class, 'exportExcel']);
        Route::apiResource('users', UserController::class);
    });

    Route::middleware('isEmployee')->group(function () {
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
    });

    Route::middleware('isProjectManager')->group(function () {
        Route::get('/assignee-search', [UserController::class, 'search']);

        Route::post('/project', [ProjectController::class, 'store']);
        Route::patch('/project/{project}', [ProjectController::class, 'update']);
        Route::delete('/project/{project}', [ProjectController::class, 'destroy']);
        Route::get('/project/trash', [ProjectController::class, 'trash']);
        Route::patch('/project/{id}/restore', [ProjectController::class, 'restore']);
        Route::delete('/project/{id}/force-delete', [ProjectController::class, 'deletePermanent']);

        Route::post('/task', [TaskController::class, 'store']);
        Route::delete('/task/{task}', [TaskController::class, 'destroy']);
        Route::get('/task/trash', [TaskController::class, 'trash']);
        Route::patch('/task/{id}/restore', [TaskController::class, 'restore']);
        Route::delete('/task/{id}/force-delete', [TaskController::class, 'deletePermanent']);
    });

    Route::middleware('isEmployeeOrPM')->group(function () {
        Route::get('/project', [ProjectController::class, 'index']);
        Route::get('/project/{project}', [ProjectController::class, 'show']);

        Route::get('/task', [TaskController::class, 'index']);
        Route::get('/task/{task}', [TaskController::class, 'show']);
        Route::patch('/task/{task}', [TaskController::class, 'update']);
        Route::get('/task-detail/{id}', [TaskController::class, 'show']);

        Route::get('/reflection', [ReportController::class, 'index']);
        Route::post('/reflection', [ReportController::class, 'store']);

        Route::get('/mood', [MoodController::class, 'index']);
        Route::post('/mood', [MoodController::class, 'store']);

        Route::get('/weekly-task', [TaskController::class, 'weeklyCompletedTask']);
        Route::get('/weekly-mood', [MoodController::class, 'weeklyMood']);
        Route::get('/monthly-productivity', [ReportController::class, 'monthlyProductivity']);
        Route::get('/team-mood', [MoodController::class, 'teamMood']);
    });
});
