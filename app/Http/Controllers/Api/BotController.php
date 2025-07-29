<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PartialApplicant;
use App\Models\Applicant;
use App\Models\Group;
use App\Services\GroupAssignmentService; // Vamos a crear este servicio

class BotController extends Controller
{
    protected $groupAssignmentService;

    public function __construct(GroupAssignmentService $groupAssignmentService)
    {
        $this->groupAssignmentService = $groupAssignmentService;
    }

    /**
     * Guarda un mensaje en la tabla messages.
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

        return response()->json([
            'status' => 'success',
            'message_id' => $message->id
        ], 201);
    }

    /**
     * Recupera el historial de mensajes para una conversation_id específica.
     */
    public function getMessages(Request $request, $conversationId)
    {
        $limit = $request->query('limit', 5); // Por defecto 5 mensajes
        $messages = Message::where('conversation_id', $conversationId)
                            ->orderBy('created_at', 'desc')
                            ->limit($limit)
                            ->get()
                            ->sortBy('created_at') // Ordena de nuevo ascendente para el historial
                            ->map(function($message) {
                                return [
                                    'role' => $message->role,
                                    'message' => $message->message,
                                ];
                            })
                            ->values(); // Para reindexar el array

        return response()->json($messages);
    }

    /**
     * Recupera el registro Conversation para un chat_id. Si no existe, lo crea.
     */
    public function getOrCreateConversation(Request $request, $chat_id)
    {
        $conversation = Conversation::firstOrCreate(
            ['chat_id' => $chat_id],
            ['user_name' => $request->input('user_name')] // Opcional: si n8n envía el nombre al inicio
        );

        return response()->json($conversation);
    }

    /**
     * Actualiza el current_process, process_status y process_id de una conversación.
     */
    public function updateConversation(Request $request, $conversationId)
    {
        $request->validate([
            'current_process' => 'nullable|string|max:255',
            'process_status' => 'nullable|string|max:255',
            'process_id' => 'nullable|numeric|exists_or_null:partial_applicants,id', // Valida que el ID exista si no es nulo
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

    /**
     * Crea un nuevo registro PartialApplicant y lo asocia a una conversation_id.
     */
    public function createPartialApplicant(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|unique:partial_applicants,conversation_id|exists:conversations,id',
            'current_evaluation_status' => 'required|string|max:255',
            'evaluation_data' => 'nullable|json',
        ]);

        $partialApplicant = PartialApplicant::create([
            'conversation_id' => $request->conversation_id,
            'current_evaluation_status' => $request->current_evaluation_status,
            'evaluation_data' => json_decode($request->evaluation_data, true) ?? [],
        ]);

        return response()->json([
            'status' => 'success',
            'partial_applicant_id' => $partialApplicant->id
        ], 201);
    }

    /**
     * Recupera el current_evaluation_status y evaluation_data para el PartialApplicant.
     */
    public function getPartialApplicant(Request $request, $conversationId)
    {
        $partialApplicant = PartialApplicant::where('conversation_id', $conversationId)
                                            ->firstOrFail();

        return response()->json($partialApplicant);
    }

    /**
     * Actualiza el current_evaluation_status y evaluation_data de un registro PartialApplicant.
     */
    public function updatePartialApplicant(Request $request, $partialApplicantId)
    {
        $request->validate([
            'current_evaluation_status' => 'required|string|max:255',
            'evaluation_data' => 'required|json',
            'is_completed' => 'boolean',
        ]);

        $partialApplicant = PartialApplicant::findOrFail($partialApplicantId);
        $partialApplicant->update([
            'current_evaluation_status' => $request->current_evaluation_status,
            'evaluation_data' => json_decode($request->evaluation_data, true),
            'is_completed' => $request->input('is_completed', $partialApplicant->is_completed),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Partial applicant updated.'
        ]);
    }

    /**
     * Lógica de Negocio Crítica: Evalúa y guarda al solicitante final, asigna grupo.
     */
    public function evaluateAndSaveApplicant(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'partial_applicant_id' => 'required|exists:partial_applicants,id',
            'final_evaluation_data' => 'required|json', // Asegúrate de que n8n envíe un JSON válido
        ]);

        $finalEvaluationData = json_decode($request->final_evaluation_data, true);
        $conversation = Conversation::findOrFail($request->conversation_id);
        $partialApplicant = PartialApplicant::findOrFail($request->partial_applicant_id);

        // 1. Lógica de Evaluación de Elegibilidad
        $isApproved = false;
        $rejectionReason = null;

        // Ejemplo de reglas de negocio:
        // Debe tener CURP, poseer terreno, y el terreno debe medir al menos 6 metros
        if (isset($finalEvaluationData['curp']) &&
            isset($finalEvaluationData['owns_land']) && $finalEvaluationData['owns_land'] === true &&
            isset($finalEvaluationData['land_size_meters']) && $finalEvaluationData['land_size_meters'] >= 6 &&
            isset($finalEvaluationData['is_land_payments_current']) && $finalEvaluationData['is_land_payments_current'] === true)
        {
            $isApproved = true;
        } else {
            // Aquí podrías tener lógica más compleja para determinar el motivo
            if (!isset($finalEvaluationData['curp'])) {
                $rejectionReason = "CURP no proporcionado.";
            } elseif (!isset($finalEvaluationData['owns_land']) || $finalEvaluationData['owns_land'] === false) {
                $rejectionReason = "No eres dueño del terreno.";
            } elseif (!isset($finalEvaluationData['land_size_meters']) || $finalEvaluationData['land_size_meters'] < 6) {
                $rejectionReason = "El tamaño del terreno es insuficiente (mínimo 6 metros).";
            } elseif (!isset($finalEvaluationData['is_land_payments_current']) || $finalEvaluationData['is_land_payments_current'] === false) {
                $rejectionReason = "No estás al corriente con los pagos del terreno.";
            } else {
                $rejectionReason = "No cumples con todos los requisitos.";
            }
        }

        // Asegura que la CURP esté presente en los datos finales para el Applicant
        $curp = $finalEvaluationData['curp'] ?? 'N/A_' . uniqid();


        // 2. Crear/Actualizar Applicant
        $applicant = Applicant::create([
            'curp' => $curp, // Asegura que la CURP esté en el modelo Applicant
            'is_approved' => $isApproved,
            'rejection_reason' => $rejectionReason,
            'final_evaluation_data' => $finalEvaluationData,
        ]);

        $responseMessage = '';
        $groupData = null;

        // 3. Asignación de Grupo (CONDICIONAL)
        if ($isApproved) {
            $group = $this->groupAssignmentService->assignApplicantToGroup($applicant);
            $applicant->save(); // Guarda el applicant con el group_id asignado

            $responseMessage = "¡Felicidades! Has sido pre-aprobado para el programa de Casas de Esperanza y asignado al " . $group->name . ". Pronto nos comunicaremos contigo para los siguientes pasos.";
            $groupData = [
                'group_id' => $group->id,
                'group_name' => $group->name,
            ];
        } else {
            $responseMessage = "Gracias por tu interés en Casas de Esperanza. Lamentablemente, no cumples con todos los requisitos del programa debido a: " . $rejectionReason;
        }

        // 4. Actualizar Estado de Procesos
        $partialApplicant->update(['is_completed' => true]);
        $conversation->update([
            'current_process' => null,
            'process_id' => null,
            'process_status' => 'completed'
        ]);

        // 5. Generar Respuesta Final para n8n
        return response()->json(array_merge([
            'status' => 'success',
            'message' => $responseMessage,
            'applicant_id' => $applicant->id,
            'is_approved' => $isApproved,
        ], $groupData ?? []));
    }
}
