<?php

namespace App\Http\Controllers;

use App\Models\UserExerciseProgress;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPlanController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = UserPlan::where('user_id', $request->user_id)
            ->with('plan')
            ->get();


        $subscriptions->each(function ($sub) {
            $currentExerciseIds = DB::table('plan_exercises')
                ->where('plan_id', $sub->plan_id)
                ->pluck('exercise_id');

            $total = $currentExerciseIds->count();
            $completed = UserExerciseProgress::where('user_plan_id', $sub->id)
                ->whereIn('exercise_id', $currentExerciseIds)
                ->where('is_completed', true)
                ->count();

            $percent = $total > 0 ? round(($completed / $total) * 100) : 0;

            $sub->progress_percent = $percent;
            $sub->completed = ($percent >= 100);
            $sub->save();
        });

        return response()->json($subscriptions);
    }


    public function store(Request $request)
    {
        $request->validate([
            'plan_id'    => 'required|exists:plans,id',
            'start_date' => 'required|date',
        ]);

        $exists = UserPlan::where('user_id', $request->user_id)
            ->where('plan_id', $request->plan_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You are already subscribed to this plan'], 422);
        }

        $userPlan = UserPlan::create([
            'user_id'          => $request->user_id,
            'plan_id'          => $request->plan_id,
            'start_date'       => $request->start_date,
            'completed'        => false,
            'progress_percent' => 0,
        ]);

        return response()->json(['message' => 'Subscribed to plan successfully', 'user_plan' => $userPlan], 201);
    }

    public function show(Request $request, $id)
    {
        $userPlan = UserPlan::where('user_id', $request->user_id)
            ->with(['plan', 'plan.exercises'])
            ->findOrFail($id);

        return response()->json($userPlan);
    }

    /**
     * Smart Tracking: Complete an exercise and auto-calculate progress
     */
    public function completeExercise(Request $request, $userPlanId, $exerciseId)
    {
        $userPlan = UserPlan::where('id', $userPlanId)
            ->where('user_id', $request->user_id)
            ->firstOrFail();

        UserExerciseProgress::updateOrCreate(
            ['user_plan_id' => $userPlanId, 'exercise_id' => $exerciseId],
            ['is_completed' => true, 'completed_at' => now()]
        );

        $currentExerciseIds = DB::table('plan_exercises')
            ->where('plan_id', $userPlan->plan_id)
            ->pluck('exercise_id');

        $totalExercises = $currentExerciseIds->count();

        $completedExercises = UserExerciseProgress::where('user_plan_id', $userPlanId)
            ->whereIn('exercise_id', $currentExerciseIds)
            ->where('is_completed', true)
            ->count();

        $progressPercent = $totalExercises > 0
            ? round(($completedExercises / $totalExercises) * 100)
            : 0;

        $userPlan->update([
            'progress_percent' => $progressPercent,
            'completed' => $progressPercent >= 100
        ]);

        return response()->json([
            'message' => 'Progress updated successfully',
            'progress_percent' => $progressPercent,
            'is_completed' => $userPlan->completed
        ]);
    }


    public function destroy(Request $request, $id)
    {
        $userPlan = UserPlan::where('user_id', $request->user_id)->findOrFail($id);
        $userPlan->delete();

        return response()->json(['message' => 'Unsubscribed from plan successfully']);
    }
}
