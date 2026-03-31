<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of all plans.
     */
    public function index()
    {
        return response()->json(Plan::with('exercises')->get());
    }

    /**
     * Display the specified plan.
     */
    public function show($id)
    {
        return response()->json(Plan::with('exercises')->findOrFail($id));
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:100',
            'difficulty'        => 'required|string|max:20',
            'duration_minutes'  => 'required|integer|min:1',
        ]);

        $plan = Plan::create([
            'name'                => $request->name,
            'difficulty'          => $request->difficulty,
            'duration_minutes'    => $request->duration_minutes,
            'created_by_user_id'  => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Plan created successfully',
            'plan'    => $plan
        ], 201);
    }

    /**
     * Get all exercises for a specific plan.
     */
    public function exercises($id)
    {
        $plan = Plan::with('exercises')->findOrFail($id);

        return response()->json([
            'plan' => $plan,
            'exercises' => $plan->exercises
        ]);
    }

    /**
     *  (Attach Exercise to Plan)
     */
    public function attachExercise(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

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
        if ($plan->created_by_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$plan->exercises()->where('exercise_id', $exercise->id)->exists()) {
            return response()->json(['message' => 'Exercise not found in this plan'], 404);
        }

        $validated = $request->validate([
            'sets'        => 'sometimes|required|integer|min:1',
            'reps'        => 'sometimes|required|integer|min:1',
            'day'         => 'sometimes|required|string|max:255',
            'order_index' => 'sometimes|nullable|integer|min:0',
        ]);

        $plan->exercises()->updateExistingPivot($exercise->id, $validated);

        return response()->json([
            'message' => 'Exercise updated in plan successfully',
            'updated_fields' => array_keys($validated)
        ]);
    }

    public function detachExercise(Plan $plan, Exercise $exercise)
    {
        if ($plan->created_by_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$plan->exercises()->where('exercise_id', $exercise->id)->exists()) {
            return response()->json(['message' => 'Exercise not found in this plan'], 404);
        }

        $plan->exercises()->detach($exercise->id);

        return response()->json([
            'message' => 'Exercise removed from plan successfully'
        ]);
    }
    public function destroy(Plan $plan)
    {
        if ($plan->created_by_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $plan->delete();

        return response()->json(['message' => 'Plan deleted successfully']);
    }

    public function update(Request $request, Plan $plan)
    {
        if ($plan->created_by_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'difficulty' => 'sometimes|required|string|max:20',
            'duration_minutes' => 'sometimes|required|integer',
        ]);

        $plan->update($validated);

        return response()->json([
            'message' => 'Plan updated successfully',
            'plan' => $plan
        ]);
    }


}
