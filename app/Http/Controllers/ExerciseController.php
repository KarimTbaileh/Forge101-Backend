<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    /**
     * Display a listing of all exercises.
     */
    public function index()
    {
        $exercises = Exercise::latest()->paginate(15);
        return response()->json($exercises);
    }

    /**
     * Store a newly created exercise.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'target_muscle'  => 'required|string|max:100',
            'description'    => 'required|string',
            'image_url'      => 'nullable|url|max:255',
            'video_url'      => 'nullable|url|max:255',
            'difficulty'     => 'required|in:beginner,intermediate,advanced',
            'category'       => 'required|string|max:255',
        ]);

        $exercise = Exercise::create($request->all());

        return response()->json([
            'message' => 'Exercise created successfully',
            'exercise' => $exercise
        ], 201);
    }

    /**
     * Display the specified exercise.
     */
    public function show($id)
    {
        $exercise = Exercise::findOrFail($id);
        return response()->json($exercise);
    }

    /**
     * Update the specified exercise.
     */
    public function update(Request $request, $id)
    {
        $exercise = Exercise::findOrFail($id);

        $request->validate([
            'name'           => 'sometimes|string|max:100',
            'target_muscle'  => 'sometimes|string|max:100',
            'description'    => 'sometimes|string',
            'image_url'      => 'nullable|url|max:255',
            'video_url'      => 'nullable|url|max:255',
            'difficulty'     => 'sometimes|in:beginner,intermediate,advanced',
            'category'       => 'sometimes|string|max:255',
        ]);

        $exercise->update($request->all());

        return response()->json([
            'message' => 'Exercise updated successfully',
            'exercise' => $exercise
        ]);
    }

    /**
     * Remove the specified exercise.
     */
    public function destroy($id)
    {
        $exercise = Exercise::findOrFail($id);
        $exercise->delete();

        return response()->json([
            'message' => 'Exercise deleted successfully'
        ]);
    }

    public function search(Request $request)
    {
        $query = Exercise::query();

        if ($request->has('q')) {
            $query->where('name', 'like', "%{$request->q}%")
                ->orWhere('description', 'like', "%{$request->q}%");
        }

        foreach (['target_muscle', 'difficulty', 'category'] as $filter) {
            if ($request->has($filter)) {
                $query->where($filter, $request->$filter);
            }
        }

        return response()->json($query->paginate($request->input('per_page', 10)));
    }

}
