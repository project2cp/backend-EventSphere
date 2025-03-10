<?php

namespace App\Http\Controllers;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class OrganizerController extends Controller
{
    public function requestOrganizer(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        if ($user->is_organizer) {
            return response()->json(['message' => 'Already an organizer'], 400);
        }

        $logoPath = $request->hasFile('logo') ? $request->file('logo')->store('logos', 'public') : null;

        $organizer = Organizer::create([
            'user_id' => $user->id,
            'category' => $request->category,
            'description' => $request->description,
            'logo' => $logoPath,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Organizer request submitted', 'organizer' => $organizer], 201);
    }

    public function approveOrganizer($id)
    {
        $organizer = Organizer::findOrFail($id);
        $organizer->update(['status' => 'approved']);
        $organizer->user->update(['is_organizer' => true]);

        return response()->json(['message' => 'Organizer approved', 'organizer' => $organizer]);
    }

    public function rejectOrganizer($id)
    {
        $organizer = Organizer::findOrFail($id);
        $organizer->update(['status' => 'rejected']);

        return response()->json(['message' => 'Organizer rejected']);
    }

    public function getProfile()
    {
        $organizer = Auth::user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Not an organizer'], 404);
        }

        return response()->json($organizer);
    }

    public function updateProfile(Request $request)
    {
        $organizer = Auth::user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Not an organizer'], 404);
        }

        $request->validate([
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'category' => 'required|string',
        ]);

        if ($request->hasFile('logo')) {
            if ($organizer->logo) {
                Storage::disk('public')->delete($organizer->logo);
            }
            $organizer->logo = $request->file('logo')->store('logos', 'public');
        }

        $organizer->update($request->only('description', 'category', 'logo'));

        return response()->json(['message' => 'Profile updated', 'organizer' => $organizer]);
    }
    public function destroy($id)
{
    $organizer = Organizer::find($id);

    if (!$organizer) {
        return response()->json([
            'message' => 'Organisateur non trouvé'
        ], 404);
    }

    $organizer->delete();

    return response()->json([
        'message' => 'Organisateur supprimé avec succès'
    ], 200);
}

}
