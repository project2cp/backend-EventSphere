<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
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
             'category' => 'required|string|max:255' // Ajout de la catégorie
         ]);

         $event = Event::create([
             'organizer_id' => Auth::id(),
             'title' => $request->title,
             'description' => $request->description,
             'date' => $request->date,
             'location' => $request->location,
             'ticket_limit' => $request->ticket_limit,
             'is_paid' => $request->is_paid,
             'ticket_price' => $request->is_paid ? $request->ticket_price : null,
             'category' => $request->category
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
         ]);

         $event->update($request->all());

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
    public function show($id) {
        $event = Event::findOrFail($id);
        return response()->json($event);
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
         } elseif ($request->sort_by == 'price') {
             $query->orderBy('price', 'asc');
         }
     }

     // 🔹 Pagination (10 résultats par page)
     $events = $query->paginate(10);

     return response()->json($events, 200);
 }

 // 📌 2. Recommandation d'événements basée sur les centres d'intérêt
 public function recommendedEvents()
 {
     $user = Auth::user();
    // Vérifier si l'utilisateur est authentifié
    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
    }

     // 🔹 Récupérer les événements auxquels l'utilisateur a participé
     $attendedEventIds = $user->tickets()->pluck('event_id')->toArray();

     if (empty($attendedEventIds)) {
        return response()->json(['message' => 'Aucun événement recommandé.'], 200);
    }
     // 🔹 Récupérer les catégories des événements fréquentés
     $favoriteCategories = Event::whereIn('id', $attendedEventIds)
         ->pluck('category')
         ->unique()
         ->toArray();

 // Vérifier si des catégories ont été trouvées
 if (empty($favoriteCategories)) {
    return response()->json(['message' => 'Aucune catégorie préférée trouvée.'], 200);
}

     // 🔹 Sélectionner des événements similaires en fonction des catégories
     $recommendedEvents = Event::whereNotIn('id', $attendedEventIds)
         ->whereIn('category', $favoriteCategories)
         ->orderBy('date', 'asc')
         ->take(5)
         ->get();
         dd($recommendedEvents);
     return response()->json($recommendedEvents, 200);
 }

}
