<?php

namespace App\Http\Controllers;

use App\Models\UserExerciseProgress;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPlanController extends Controller
{
    /**
     * Display a listing of the authenticated user's plans.
     */
    public function index()
    {
        $userPlans = UserPlan::where('user_id', auth()->id())
            ->with(['plan', 'plan.exercises'])
            ->latest()
            ->get();

        return response()->json($userPlans);
    }

    /**
     * Assign a plan to the authenticated user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_id'    => 'required|exists:plans,id',
            'start_date' => 'required|date',
        ]);

        $exists = UserPlan::where('user_id', auth()->id())
            ->where('plan_id', $request->plan_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You are already subscribed to this plan'], 422);
        }

        $userPlan = UserPlan::create([
            'user_id'          => auth()->id(),
            'plan_id'          => $request->plan_id,
            'start_date'       => $request->start_date,
            'completed'        => false,
            'progress_percent' => 0,
        ]);

        $userPlan->load('plan');

        return response()->json([
            'message' => 'Successfully subscribed to the plan',
            'user_plan' => $userPlan
        ], 201);
    }

    /**
     * Display the specified user plan.
     */
    public function show($id)
    {
        $userPlan = UserPlan::where('user_id', auth()->id())
            ->with(['plan', 'plan.exercises'])
            ->findOrFail($id);

        return response()->json($userPlan);
    }

    /**
     * Update the progress of a user plan.
     */
    public function update(Request $request, $id)
    {
        $userPlan = UserPlan::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'progress_percent' => 'required|integer|between:0,100',
            'completed'        => 'boolean',
        ]);

        $userPlan->update([
            'progress_percent' => $request->progress_percent,
            'completed'        => $request->completed ?? ($request->progress_percent == 100),
        ]);

        return response()->json([
            'message' => 'Progress updated successfully',
            'user_plan' => $userPlan
        ]);
    }

    /**
     * Unsubscribe from a plan.
     */
    public function destroy($id)
    {
        $userPlan = UserPlan::where('user_id', auth()->id())->findOrFail($id);
        $userPlan->delete();

        return response()->json([
            'message' => 'Successfully unsubscribed from the plan'
        ]);
    }


    public function completeExercise(Request $request, $userPlanId, $exerciseId)
    {
        $userPlan = UserPlan::where('id', $userPlanId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        UserExerciseProgress::updateOrCreate(
            ['user_plan_id' => $userPlanId, 'exercise_id' => $exerciseId],
            ['is_completed' => true, 'completed_at' => now()]
        );


        $totalExercises = DB::table('plan_exercises')
            ->where('plan_id', $userPlan->plan_id)
            ->count();

        $completedExercises = UserExerciseProgress::where('user_plan_id', $userPlanId)
            ->where('is_completed', true)
            ->count();

        $progressPercent = $totalExercises > 0
            ? round(($completedExercises / $totalExercises) * 100)
            : 0;

        $userPlan->update([
            'progress_percent' => $progressPercent,
            'is_completed' => $progressPercent >= 100
        ]);

        return response()->json([
            'message' => 'Progress has been successfully updated.',
            'progress_percent' => $progressPercent,
            'is_completed' => $userPlan->is_completed
        ]);
    }



}
