<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

/**
 * Clase controladora para gestionar las interacciones de las conversaciones.
 *
 * Se encarga de la lógica para crear, recuperar y actualizar conversaciones.
 */
class BotConversationController extends Controller
{
    /**
     * Recupera el registro de Conversation para un chat_id. Si no existe, lo crea.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $chatId El ID del chat del solicitante.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrCreateConversation(Request $request, string $chatId)
    {
        $conversation = Conversation::firstOrCreate(
            ['chat_id' => $chatId],
            ['user_name' => $request->input('user_name')] // Opcional: si n8n envía el nombre al inicio
        );

        return response()->json($conversation);
    }

    /**
     * Actualiza los datos de una conversación.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $conversationId El ID de la conversación.
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateConversation(Request $request, int $conversationId)
    {
        $request->validate([
            'current_process' => 'nullable|string|max:255',
            'process_status' => 'nullable|string|max:255',
            'process_id' => 'nullable|numeric',
        ]);

        $conversation = Conversation::findOrFail($conversationId);
        $conversation->update($request->only([
            'current_process',
            'process_status',
            'process_id'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Conversation updated.'
        ]);
    }
}
