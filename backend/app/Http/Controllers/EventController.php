<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Notifications\EventNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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
         // Notification data
        $eventData = [
            'title' => 'Nouvel Événement Créé',
            'message' => 'Un nouvel événement "' . $event->name . '" a été ajouté.',
            'event_id' => $event->id,
        ];

        Log::info('Envoi de la notification pour l\'événement : ' . $event->id);

        // Envoyer la notification à tous les utilisateurs
        foreach (User::all() as $user) {
            $user->notify(new EventNotification($eventData));
        }

        Log::info('Notifications envoyées.');

        return response()->json(['success' => 'Événement créé et notifications envoyées.']);
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

         $eventData = [
            'title' => 'Événement Mis à Jour',
            'message' => 'L\'événement "' . $event->name . '" a été modifié.',
            'event_id' => $event->id,
        ];
    
        foreach (User::all() as $user) {
            $user->notify(new EventNotification($eventData));
        }
    
        return response()->json(['success' => 'Événement mis à jour et notifications envoyées.']);

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
        $event->increment('popularity');
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
         } elseif ($request->sort_by == 'ticket_price') {
             $query->orderBy('ticket_price', 'asc');
         }
     }

     // 🔹 Pagination (10 résultats par page)
     $events = $query->paginate(10);

     return response()->json($events, 200);
 }



}
