<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Message;
use Illuminate\Http\Request;

/**
 * Clase controladora para gestionar la lógica de mensajes.
 *
 * Se encarga de guardar y recuperar mensajes de la base de datos.
 */
class BotMessageController extends Controller
{
    /**
     * Guarda un mensaje en la tabla messages.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required',
            'phone' => 'string',
            'message' => 'required|string',
            'role' => 'required|in:user,assistant',
            'name' => 'nullable|string|max:255',
        ]);

        $message = Message::create($request->all());

        if ($request->role === 'user') {
            Applicant::where('chat_id', $request->phone)
                ->where('reminder_level', '!=', 0)
                ->update(['reminder_level' => 0]);
        }

        return response()->json([
            'status' => 'success',
            'message_id' => $message->id
        ], 201);
    }

    /**
     * Recupera el historial de mensajes para una conversation_id específica.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $conversationId El ID de la conversación.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages(Request $request, int $conversationId)
    {
        $limit = $request->query('limit', 5); // Por defecto 5 mensajes
        $messages = Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->sortBy('created_at') // Ordena de nuevo ascendente para el historial
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'message' => $message->message,
                ];
            })
            ->values(); // Para reindexar el array

        return response()->json($messages);
    }
}
