<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
{
    return response()->json($request->user());
}
public function updateProfile(Request $request)
{
    $user = $request->user();

    $data = $request->validate([
        'name' => 'sometimes|string|max:255',
        'bio' => 'sometimes|string',
        'interests' => 'sometimes|array',
        'profile_photo' => 'sometimes|image|max:2048',
    ]);

    if ($request->hasFile('profile_photo')) {
        $path = $request->file('profile_photo')->store('profile_photos', 'public');
        $data['profile_photo'] = $path;
    }

    $user->update($data);

    return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
}
public function deleteAccount(Request $request)
{
    $user = $request->user();
    $user->delete();

    return response()->json(['message' => 'Account deleted successfully']);
}

}
