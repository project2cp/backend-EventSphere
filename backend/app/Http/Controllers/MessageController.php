<?php

namespace App\Http\Controllers;

use App\Events\PusherBroadcast;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        // Diffuser l'événement en temps réel
        broadcast(new PusherBroadcast($message))->toOthers();

        return response()->json(['message' => 'Message envoyé avec succès', 'data' => $message]);
    }

    public function getMessages($sender_id, $receiver_id)
    {
        $messages = Message::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);
        })->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }
}
