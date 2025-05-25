<?php

namespace App\Http\Controllers;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\EventAdmin;
class OrganizerController extends Controller
{
    // 1️⃣ Demande pour devenir organisateur
    public function requestOrganizer(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'document' => 'nullable|mimes:pdf,jpg,png|max:5120', // Accepte PDF et images (max 5MB) 
            'organization_type' => 'required|string|in:Club,Enterprise,Non-Profit,Government,Educational',
            'organization_name' => 'required|string|max:255'
        ]);

        $user = Auth::user();

        if ($user->is_organizer) {
            return response()->json(['message' => 'Déjà organisateur'], 400);
        }

        $logoPath = $request->hasFile('logo') ? $request->file('logo')->store('logos', 'public') : null;
        $documentPath = $request->hasFile('document') ? $request->file('document')->store('documents', 'public') : null;
        $verificationToken = Str::random(40);

        $organizer = Organizer::create([
            'user_id' => $user->id,
            'category' => $request->category,
            'description' => $request->description,
            'logo' => $logoPath,
            'status' => 'pending',
            'email_verification_token' => $verificationToken,
            'document' => $documentPath,  // Ajout du document
            'organization_type' => $request->organization_type,
             'organization_name' => $request->organization_name,
        ]);

        // Envoyer un email de vérification (Simulation)
        Mail::raw('Cliquez sur ce lien pour vérifier votre email: '.url("/api/organizers/verify/".$verificationToken), function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Vérification de votre demande d’organisateur');
        });

        return response()->json(['message' => 'Demande soumise. Vérifiez votre email.', 'organizer' => $organizer], 201);
    }

    // 2️⃣ Vérification automatique par email
    public function verifyEmail($token)
    {
        $organizer = Organizer::where('email_verification_token', $token)->first();

        if (!$organizer) {
            return response()->json(['message' => 'Lien invalide ou expiré'], 400);
        }

        $organizer->update([
            'status' => 'approved',
            'email_verification_token' => null
        ]);

        $organizer->user->update(['is_organizer' => true]);

        return response()->json(['message' => 'Vérification réussie, vous êtes maintenant organisateur']);
    }

    // 3️⃣ Récupérer le profil de l’organisateur
    public function getProfile()
    {
        $organizer = Auth::user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Non organisateur'], 404);
        }

        return response()->json($organizer);
    }

    // 4️⃣ Modifier le profil de l’organisateur
    public function updateProfile(Request $request)
    {
        $organizer = Auth::user()->organizer;

        if (!$organizer) {
            return response()->json(['message' => 'Non organisateur'], 404);
        }

        $request->validate([
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'category' => 'required|string',
            'document' => 'nullable|mimes:pdf,jpg,png|max:5120', // Vérification du fichier
            'organization_type' => 'required|string|in:Club,Enterprise,Non-Profit,Government,Educational',
            'organization_name' => 'required|string|max:255'
        ]);

        if ($request->hasFile('logo')) {
            if ($organizer->logo) {
                Storage::disk('public')->delete($organizer->logo);
            }
            $organizer->logo = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('document')) {
            if ($organizer->document) {
                Storage::disk('public')->delete($organizer->document);
            }
            $organizer->document = $request->file('document')->store('documents', 'public');
        }

        $organizer->update($request->only('description', 'category', 'logo'));

        return response()->json(['message' => 'Profil mis à jour', 'organizer' => $organizer]);
    }


public function addAdmin(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'permissions' => 'required|array',
    ]);

    $organizer = Auth::user()->organizer;
    if (!$organizer) {
        return response()->json(['message' => 'Vous n’êtes pas organisateur'], 403);
    }

    $user = User::where('email', $request->email)->first();

    // Vérifier si l’utilisateur est déjà admin
    if ($organizer->admins()->where('user_id', $user->id)->exists()) {
        return response()->json(['message' => 'Cet utilisateur est déjà admin'], 400);
    }

    EventAdmin::create([
        'organizer_id' => $organizer->id,
        'user_id' => $user->id,
        'permissions' => $request->permissions,
    ]);

    // Envoyer un email de notification simple
    Mail::raw("Bonjour {$user->name},\n\nVous avez été ajouté comme administrateur de l'organisateur {$organizer->user->name}. Connectez-vous pour gérer les événements.\n\nCordialement,\nL'équipe de l'application", function ($message) use ($user) {
        $message->to($user->email)
                ->subject("Ajout en tant qu'administrateur");
    });

    return response()->json(['message' => 'Admin ajouté avec succès et email envoyé']);
}
public function listAdmins()
{
    $organizer = Auth::user()->organizer;
    if (!$organizer) {
        return response()->json(['message' => 'Vous n’êtes pas organisateur'], 403);
    }

    $admins = $organizer->admins()->with('user:id,name,email')->get();

    return response()->json(['admins' => $admins]);
}
public function removeAdmin(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $organizer = Auth::user()->organizer;
    if (!$organizer) {
        return response()->json(['message' => 'Vous n’êtes pas organisateur'], 403);
    }

    $user = User::where('email', $request->email)->first();

    $admin = $organizer->admins()->where('user_id', $user->id)->first();

    if (!$admin) {
        return response()->json(['message' => 'Cet utilisateur n’est pas un admin'], 404);
    }

    $admin->delete();

    return response()->json(['message' => 'Admin supprimé avec succès']);
}
public function deleteOrganizer()
{
    $user = Auth::user();
    $organizer = $user->organizer;

    if (!$organizer) {
        return response()->json(['message' => 'Non organisateur'], 404);
    }

    // Supprimer le logo et les fichiers liés
    if ($organizer->logo) {
        Storage::disk('public')->delete($organizer->logo);
    }

    if ($organizer->document) {
        Storage::disk('public')->delete($organizer->document);
    }

    // Supprimer l'organisateur
    $organizer->delete();

    // Mettre à jour l'utilisateur
    User::where('id', $user->id)->update(['is_organizer' => false]);
    return response()->json(['message' => 'Organisateur supprimé avec succès']);
}

}
