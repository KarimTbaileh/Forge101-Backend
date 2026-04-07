<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {

        $plans = Plan::where('created_by_user_id', $request->user_id)->get();

        return response()->json($plans);
    }

    public function show($id)
    {
        return response()->json(Plan::with('exercises')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:100',
            'difficulty'        => 'required|string|max:20',
            'duration_minutes'  => 'required|integer|min:1',
        ]);

        // الآن نصل للبيانات كمصفوفة بأمان تام
        $supabaseUser = $request->supabase_user;

        \App\Models\User::updateOrCreate(
            ['id' => $request->user_id],
            [
                'name' => $supabaseUser['user_metadata']['name'] ?? 'Supabase User',
                'email' => $supabaseUser['email'] ?? 'no-email@supabase.com'
            ]
        );

        $plan = \App\Models\Plan::create([
            'name'                => $request->name,
            'difficulty'          => $request->difficulty,
            'duration_minutes'    => $request->duration_minutes,
            'created_by_user_id'  => $request->user_id,
        ]);

        return response()->json([
            'message' => 'Plan created successfully',
            'plan'    => $plan
        ], 201);
    }




    public function update(Request $request, Plan $plan)
    {
        if ($plan->created_by_user_id !== $request->user_id) {
            return response()->json(['message' => 'Unauthorized to update this plan'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'difficulty' => 'sometimes|required|string|max:20',
            'duration_minutes' => 'sometimes|required|integer',
        ]);

        $plan->update($validated);

        return response()->json(['message' => 'Plan updated successfully', 'plan' => $plan]);
    }

    public function destroy(Request $request, Plan $plan)
    {
        if ($plan->created_by_user_id !== $request->user_id) {
            return response()->json(['message' => 'Unauthorized to delete this plan'], 403);
        }

        $plan->delete();
        return response()->json(['message' => 'Plan deleted successfully']);
    }

    public function exercises($id)
    {
        $plan = Plan::with('exercises')->findOrFail($id);
        return response()->json(['plan' => $plan, 'exercises' => $plan->exercises]);
    }

    public function attachExercise(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);
        if ($plan->created_by_user_id !== $request->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'exercise_id' => 'required|exists:exercises,id',
            'sets'        => 'required|integer|min:1',
            'reps'        => 'required|integer|min:1',
            'day'         => 'required|string',
            'order_index' => 'nullable|integer'
        ]);

        $plan->exercises()->attach($validated['exercise_id'], [
            'sets'        => $validated['sets'],
            'reps'        => $validated['reps'],
            'day'         => $validated['day'],
            'order_index' => $validated['order_index'] ?? 0,
        ]);

        return response()->json(['message' => 'Exercise attached to plan successfully']);
    }

    public function updateAttachedExercise(Request $request, Plan $plan, Exercise $exercise)
    {
        if ($plan->created_by_user_id !== $request->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'sets'        => 'sometimes|required|integer|min:1',
            'reps'        => 'sometimes|required|integer|min:1',
            'day'         => 'sometimes|required|string|max:255',
            'order_index' => 'sometimes|nullable|integer|min:0',
        ]);

        $plan->exercises()->updateExistingPivot($exercise->id, $validated);

        return response()->json(['message' => 'Exercise in plan updated successfully']);
    }

    public function detachExercise(Request $request, Plan $plan, Exercise $exercise)
    {
        if ($plan->created_by_user_id !== $request->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $plan->exercises()->detach($exercise->id);
        return response()->json(['message' => 'Exercise removed from plan successfully']);
    }
}
