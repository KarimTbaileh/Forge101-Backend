<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\UserPlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->group(function () {

    //  (Public Routes)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // (Protected Routes - Sanctum)
    Route::middleware('auth:sanctum')->group(function () {

        //  (Authentication & Profile) ---
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::put('/user', [AuthController::class, 'updateProfile']);

        //  (Plans)
        Route::apiResource('plans', PlanController::class);

        //     (Plan-Exercise Relationship)
        Route::get('plans/{plan}/exercises', [PlanController::class, 'exercises']);
        Route::post('plans/{plan}/exercises', [PlanController::class, 'attachExercise']);
        Route::put('plans/{plan}/exercises/{exercise}', [PlanController::class, 'updateAttachedExercise']);
        Route::delete('plans/{plan}/exercises/{exercise}', [PlanController::class, 'detachExercise']);

        //   (Exercises)

        Route::get('exercises/search', [ExerciseController::class, 'search']);
        Route::apiResource('exercises', ExerciseController::class);

        // (User Plans & Progress)
        Route::apiResource('user-plans', UserPlanController::class);
        // New route for smart tracking
        Route::post("user-plans/{userPlan}/complete-exercise/{exercise}", [UserPlanController::class, "completeExercise"]);

    });
});
