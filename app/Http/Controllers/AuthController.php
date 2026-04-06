<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Update user profile (Sync with Supabase and handle image upload)
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Sync Supabase User with Local MySQL User
        $user = User::updateOrCreate(
            ['id' => $request->user_id],
            [
                'name' => $request->name ?? ($request->supabase_user->user_metadata->name ?? 'User'),
                'email' => $request->supabase_user->email
            ]
        );

        // Handle Profile Image Upload
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $imagePath;
            $user->save();
        }

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
            $user->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
