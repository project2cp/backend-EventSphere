<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Notifications\ConfirmationInscription;
use App\Events\MessageSent;
use App\Notifications\EventNotification;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-confirmation', function () {
    $user = User::find(1); // Remplace "1" par l'ID d'un utilisateur existant dans ta base de données
    $event = 'Hackathon Algérie Poste'; // Exemple d'événement
    $user->notify(new ConfirmationInscription($event));

    return 'Notification de confirmation envoyée !';
});

Route::get('/test-event', function () {
    broadcast(new MessageSent('Ceci est un message envoyé en temps réel.'));
    return 'Événement diffusé avec succès !';
});



Route::get('/test-notification', function () {
    $user = User::first(); // Récupère le premier utilisateur

    if ($user) {
        $user->notify(new EventNotification([
            'title' => 'Test Notification',
            'message' => 'Ceci est une notification de test.',
            'event_id' => 1
        ]));
        return response()->json(['success' => 'Notification envoyée avec succès.']);
    } else {
        return response()->json(['error' => 'Aucun utilisateur trouvé.'], 404);
    }
});
