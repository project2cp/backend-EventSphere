<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::delete('/profile', [ProfileController::class, 'deleteAccount']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/organizers/request', [OrganizerController::class, 'requestOrganizer']);
    Route::get('/organizers/profile', [OrganizerController::class, 'getProfile']);
    Route::put('/organizers/profile', [OrganizerController::class, 'updateProfile']);

    Route::middleware('admin')->group(function () {
        Route::put('/organizers/{id}/approve', [OrganizerController::class, 'approveOrganizer']);
        Route::put('/organizers/{id}/reject', [OrganizerController::class, 'rejectOrganizer']);
    });
});

Route::delete('/organizers/{id}', [OrganizerController::class, 'destroy']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::get('/events/{id}', [EventController::class, 'show']); // Voir un événement
// Gestion des billets
Route::post('/events/{event}/buy-ticket', [TicketController::class, 'buyTicket']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/events', [EventController::class, 'index']); // 🔍 Recherche et filtres
   
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events/{eventId}/register', [TicketController::class, 'registerForEvent']);
    Route::post('/tickets/{ticketId}/refund', [TicketController::class, 'refundTicket']);
    Route::post('/tickets/{ticketId}/cancel', [TicketController::class, 'cancelRegistration']);
});


//chat participants-organisateurs
Route::post('/send-message', [MessageController::class, 'sendMessage']);
Route::get('/messages/{sender_id}/{receiver_id}', [MessageController::class, 'getMessages']);

//notifications push
//Route::middleware('auth')->get('/notifications', [NotificationController::class, 'index']);
//Route::get('/notifications', [NotificationController::class, 'index']);



//Dashboard
Route::middleware('auth:sanctum')->get('/dashboard/summary', [DashboardController::class, 'summary']);