<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\UserPlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ملاحظة: مسارات Register و Login لم تعد مطلوبة في Laravel
    // لأن التسجيل يتم مباشرة عبر Supabase من الموبايل.
    // سنبقيها فقط إذا كنت تريد عمل "Sync" للبيانات.

    // (Protected Routes - Supabase Auth)
    Route::middleware('auth.supabase')->group(function () {

        // --- إدارة الحساب (Profile) ---
        // استرجاع بيانات المستخدم القادمة من توكن Supabase
        Route::get('/user', function (Request $request) {
            return response()->json($request->supabase_user);
        });

        // تحديث البروفايل (الاسم، الصورة)
        Route::put('/user', [AuthController::class, 'updateProfile']);

        // --- الخطط (Plans) ---
        Route::apiResource('plans', PlanController::class);

        // إدارة التمارين داخل الخطط (Plan-Exercise Relationship)
        Route::get('plans/{plan}/exercises', [PlanController::class, 'exercises']);
        Route::post('plans/{plan}/exercises', [PlanController::class, 'attachExercise']);
        Route::put('plans/{plan}/exercises/{exercise}', [PlanController::class, 'updateAttachedExercise']);
        Route::delete('plans/{plan}/exercises/{exercise}', [PlanController::class, 'detachExercise']);

        // --- التمارين (Exercises) ---
        Route::get('exercises/search', [ExerciseController::class, 'search']);
        Route::apiResource('exercises', ExerciseController::class);

        // --- اشتراكات المستخدمين وتقدمهم (User Plans & Progress) ---
        Route::apiResource('user-plans', UserPlanController::class);

        // نظام التتبع الذكي (Smart Tracking)
        Route::post("user-plans/{userPlan}/complete-exercise/{exercise}", [UserPlanController::class, "completeExercise"]);

    });
});
Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'success',
            'message' => '✅ Laravel متصل بـ Supabase PostgreSQL بنجاح!',
            'database' => DB::getDatabaseName()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
