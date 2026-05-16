<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'avatar_url' => $user->avatar_url,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'avatar_url' => 'sometimes|nullable|url|max:2048',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = User::findOrFail($request->user_id);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (array_key_exists('avatar_url', $validated)) {
            $user->avatar_url = $validated['avatar_url'];
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $user->profile_image = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }
}
