<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageRole;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
     * @return JsonResponse
     */
    public function storeMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required',
            'phone' => 'string',
            'message' => 'required|string',
            'role' => ['required', Rule::enum(MessageRole::class)],
            'name' => 'nullable|string|max:255',
        ]);

        $message = Message::create($request->all());

        return response()->json([
            'status' => 'success',
            'message_id' => $message->id,
        ], 201);
    }

    /**
     * Recupera el historial de mensajes para una conversation_id específica.
     *
     * @param  int  $conversationId  El ID de la conversación.
     * @return JsonResponse
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
