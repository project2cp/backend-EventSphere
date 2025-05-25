<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
class EventController extends Controller
{
     // 🟢 1. Création d'un événement
     public function store(Request $request)
     {
         $request->validate([
             'title' => 'required|string|max:255',
             'description' => 'nullable|string',
             'date' => 'required|date',
             'location' => 'required|string|max:255',
             'ticket_limit' => 'nullable|integer',
             'is_paid' => 'required|boolean',
             'ticket_price' => 'nullable|numeric|min:0',
             'category' => 'required|string|max:255', // Ajout de la catégorie
             'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048' // validation de l'imagev
         ]);

         
// Gestion de l'image
         $imagePath = null;
         if ($request->hasFile('image') && $request->file('image')->isValid()) {
             $imagePath = $request->file('image')->store('events', 'public');
             if (!$imagePath) {
                 return response()->json(['error' => 'Failed to store the image'], 500);
             }
         }
         $event = Event::create([
             'organizer_id' => Auth::id(),
             'title' => $request->title,
             'description' => $request->description,
             'date' => $request->date,
             'location' => $request->location,
             'ticket_limit' => $request->ticket_limit,
             'is_paid' => $request->is_paid,
             'ticket_price' => $request->is_paid ? $request->ticket_price : null,
             'category' => $request->category,
             'image' => $imagePath
         ]);

         return response()->json($event, 201);
     }

     // 🟢 2. Mise à jour d'un événement
     public function update(Request $request, $id)
     {$event = Event::findOrFail($id);
         if (Auth::id() !== $event->organizer_id) {
             return response()->json(['error' => 'Unauthorized'], 403);
         }

         $request->validate([
             'title' => 'sometimes|string|max:255',
             'description' => 'nullable|string',
             'date' => 'sometimes|date',
             'location' => 'sometimes|string|max:255',
             'ticket_limit' => 'nullable|integer',
             'is_paid' => 'sometimes|boolean',
             'ticket_price' => 'nullable|numeric|min:0',
             'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
         ]);
// Gestion de l'image
         $data = $request->except('image');

         // Gestion de l'image
         if ($request->hasFile('image') && $request->file('image')->isValid()) {
             // Supprimer l'ancienne image si elle existe
             if ($event->image && file_exists(storage_path('app/public/' . $event->image))) {
                 unlink(storage_path('app/public/' . $event->image));
             }
     
             // Déplacer manuellement l'image
             $file = $request->file('image');
             $fileName = time() . '_' . $file->getClientOriginalName();
             $file->move(storage_path('app/public/events'), $fileName);
             $data['image'] = 'events/' . $fileName;
         } else {
             $data['image'] = $event->image;
         }
         $event->update($request->all());

         // Ajouter l'URL de l'image
         $event->image_url = $event->image ? asset('storage/' . $event->image) : null;
        // Débogage : Vérifier ce qui est sauvegardé
        $event = $event->fresh();
        \Illuminate\Support\Facades\Log::info('Image après mise à jour : ' . $event->image);
         return response()->json($event, 200);
     }

     // 🟢 3. Suppression d'un événement
     public function destroy($id)
     {$event = Event::findOrFail($id);
         if (Auth::id() !== $event->organizer_id) {
             return response()->json(['error' => 'Unauthorized'], 403);
         }

         $event->delete();
         return response()->json(['message' => 'Événement supprimé'], 200);
     }
       //  Afficher un événement
    public function show($id)
{
    $event = Event::with('organizer')->findOrFail($id);
    $event->increment('popularity');

    $event->image_url = $event->image ? asset('storage/' . $event->image) : null;

    return response()->json([
        'id' => $event->id,
        'title' => $event->title,
        'description' => $event->description,
        'date' => $event->date,
        'location' => $event->location,
        'ticket_limit' => $event->ticket_limit,
        'ticket_price' => $event->ticket_price,
        'is_paid' => $event->is_paid,
        'category' => $event->category,
        'popularity' => $event->popularity,
        'image_url' => $event->image_url,
        'organization_name' => optional($event->organizer)->organization_name,
        'tickets_count' => $event->tickets()->count(),
    ]);
}

 // 📌 1. Recherche, tri et pagination
 public function index(Request $request)
 {
     $query = Event::query();

     // 🔹 Filtrage par catégorie
     if ($request->has('category')) {
         $query->where('category', $request->category);
     }

     // 🔹 Filtrage par lieu
     if ($request->has('location')) {
         $query->where('location', 'LIKE', "%{$request->location}%");
     }

     // 🔹 Filtrage par date
     if ($request->has('date')) {
         $query->whereDate('date', $request->date);
     }

     // 🔹 Recherche par mots-clés
     if ($request->has('keyword')) {
         $query->where('title', 'LIKE', "%{$request->keyword}%")
               ->orWhere('description', 'LIKE', "%{$request->keyword}%");
     }

     // 🔹 Trier par popularité, date ou prix
     if ($request->has('sort_by')) {
         if ($request->sort_by == 'popularity') {
             $query->orderBy('popularity', 'desc');
         } elseif ($request->sort_by == 'date') {
             $query->orderBy('date', 'asc');
         } elseif ($request->sort_by == 'ticket_price') {
             $query->orderBy('ticket_price', 'asc');
         }
     }

     // 🔹 Pagination (10 résultats par page)
     $events = $query->paginate(10);

     return response()->json($events, 200);
 }



}
