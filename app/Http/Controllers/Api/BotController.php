<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Applicant;
use App\Models\Group;
use App\Services\GroupAssignmentService;
use App\Models\Stage;
use App\Models\Question;
use App\Models\ApplicantQuestionResponse;
use Illuminate\Support\Facades\DB;

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

    // Endpoints para el Flujo de Solicitantes
    public function startEvaluation(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|string|unique:applicants,chat_id',
        ]);

        // Crea un nuevo Applicant y lo asocia a la primera etapa y pregunta
        $firstStage = Stage::orderBy('order')->first();
        $firstQuestion = $firstStage ? $firstStage->questions()->orderBy('order')->first() : null;

        if (!$firstStage || !$firstQuestion) {
            return response()->json(['error' => 'No hay etapas o preguntas configuradas.'], 404);
        }

        $applicant = Applicant::create([
            'chat_id' => $validated['chat_id'],
            'current_stage_id' => $firstStage->id,
            'current_question_id' => $firstQuestion->id,
            'process_status' => 'in_progress',
        ]);

        // Crea un registro de respuesta vacío para todas las preguntas para el historial
        $allQuestions = Question::orderBy('stage_id')->orderBy('order')->get();
        foreach ($allQuestions as $question) {
            ApplicantQuestionResponse::create([
                'applicant_id' => $applicant->id,
                'question_id' => $question->id,
                'question_text_snapshot' => $question->question_text,
                'user_response' => null,
                'is_correct' => null,
            ]);
        }

        return response()->json([
            'applicant_id' => $applicant->id,
            'question' => $firstQuestion,
            'next_question_text' => $firstQuestion->question_text,
            'validation_rules' => $firstQuestion->validation_rules,
        ]);
    }

    public function submitAnswer(Request $request, string $chat_id)
    {
        $validated = $request->validate([
            'question_key' => 'required|string',
            'user_response' => 'required|string',
        ]);

        $applicant = Applicant::where('chat_id', $chat_id)->where('process_status', 'in_progress')->firstOrFail();
        $question = Question::where('key', $validated['question_key'])->firstOrFail();

        // Lógica de validación
        $isCorrect = $this->validateResponse($question->validation_rules, $validated['user_response']);

        // Guarda la respuesta en el historial y en evaluation_data
        $response = ApplicantQuestionResponse::where('applicant_id', $applicant->id)
                                            ->where('question_id', $question->id)
                                            ->first();
        if ($response) {
            $response->update([
                'user_response' => $validated['user_response'],
                'is_correct' => $isCorrect,
            ]);
        }

        $evaluationData = $applicant->evaluation_data ?? [];
        $evaluationData[$validated['question_key']] = $validated['user_response'];
        $applicant->update(['evaluation_data' => $evaluationData]);

        return response()->json(['status' => 'success', 'is_correct' => $isCorrect]);
    }

    public function updateManually(Request $request, int $applicant_id)
    {
        // Este endpoint es para uso exclusivo de Filament
        $applicant = Applicant::findOrFail($applicant_id);

        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'user_response' => 'nullable|string',
            'current_stage_id' => 'nullable|exists:stages,id',
            'current_question_id' => 'nullable|exists:questions,id',
            'process_status' => 'nullable|string|in:in_progress,completed,rejected,approved',
        ]);
        
        // Actualiza la respuesta en el historial
        $response = $applicant->responses()->where('question_id', $validated['question_id'])->firstOrFail();
        $response->update(['user_response' => $validated['user_response']]);

        // Actualiza el evaluation_data
        $questionKey = Question::find($validated['question_id'])->key;
        $evaluationData = $applicant->evaluation_data ?? [];
        $evaluationData[$questionKey] = $validated['user_response'];
        $applicant->evaluation_data = $evaluationData;

        // Actualiza el estado si se pasa en el request
        if (isset($validated['current_stage_id'])) {
            $applicant->current_stage_id = $validated['current_stage_id'];
        }
        if (isset($validated['current_question_id'])) {
            $applicant->current_question_id = $validated['current_question_id'];
        }
        if (isset($validated['process_status'])) {
            $applicant->process_status = $validated['process_status'];
        }

        $applicant->save();

        return response()->json(['status' => 'success', 'applicant' => $applicant]);
    }
    
    // --- Métodos de Ayuda (Lógica de Negocio) ---

    private function validateResponse(array $rules, string $response)
    {
        // TODO: Implementar la lógica de validación aquí
        // Por ejemplo, usando una librería como 'json-schema' o reglas personalizadas.
        // Por ahora, solo devolver true
        return true;
    }

    private function evaluateStageApproval(Applicant $applicant, Stage $stage): bool
    {
        // TODO: Implementar la lógica para evaluar los criterios de aprobación de la etapa
        // Por ejemplo:
        // $criteria = $stage->approval_criteria;
        // if ($criteria && $applicant->evaluation_data[$criteria['key']] !== $criteria['value']) {
        //     return false;
        // }
        return true;
    }

    private function finalizeApplicant(Applicant $applicant)
    {
        // TODO: Implementar la lógica para la aprobación final
        // Esto incluirá la lógica de negocio completa de los criterios del programa
        $isApproved = true; // Lógica de negocio
        
        if ($isApproved) {
            $group = $this->groupAssignmentService->assignApplicantToGroup($applicant);
            $applicant->update([
                'is_approved' => true,
                'process_status' => 'completed',
                'group_id' => $group->id,
            ]);
        } else {
            $applicant->update([
                'is_approved' => false,
                'process_status' => 'rejected',
                'rejection_reason' => 'No cumple con todos los requisitos finales.',
            ]);
        }
    }

    public function getNextQuestion(string $chat_id)
    {
        $applicant = Applicant::where('chat_id', $chat_id)
                                ->where('process_status', 'in_progress')
                                ->first();

        if (!$applicant) {
            return response()->json(['error' => 'No se encontró un solicitante en proceso o el proceso ha terminado.'], 404);
        }

        $currentStage = $applicant->currentStage;
        $currentQuestion = $applicant->currentQuestion;
        $nextQuestion = $currentStage->questions()->where('order', '>', $currentQuestion->order)->first();

        if (!$nextQuestion) {
            // La IA debe decidir la aprobación de la etapa
            return response()->json([
                'status' => 'waiting_for_ai_approval',
                'stage_id' => $currentStage->id,
                'message' => 'Llegaste al final de la etapa. Evaluando tu solicitud...',
            ]);
        } else {
            // ... (resto de la lógica para avanzar a la siguiente pregunta de la misma etapa)
            $applicant->update(['current_question_id' => $nextQuestion->id]);
            
            return response()->json([
                'status' => 'next_question',
                'question' => $nextQuestion,
                'next_question_text' => $nextQuestion->question_text,
                'validation_rules' => $nextQuestion->validation_rules,
            ]);
        }
    }

    // Nuevo método para recibir la decisión de la IA desde n8n
    public function handleStageApproval(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|string|exists:applicants,chat_id',
            'stage_id' => 'required|integer|exists:stages,id',
            'is_approved' => 'required|boolean',
            'rejection_reason' => 'nullable|string',
        ]);

        $applicant = Applicant::where('chat_id', $validated['chat_id'])
                                ->where('process_status', 'in_progress')
                                ->firstOrFail();

        // Verificamos que la decisión sea para la etapa actual del solicitante
        if ($applicant->current_stage_id != $validated['stage_id']) {
            return response()->json(['error' => 'La decisión de la IA no corresponde a la etapa actual del solicitante.'], 400);
        }

        if ($validated['is_approved']) {
            // Si la IA aprobó, avanzamos a la siguiente etapa o finalizamos el proceso
            $nextStage = Stage::where('order', '>', $applicant->currentStage->order)->orderBy('order')->first();

            if ($nextStage) {
                // Avanzamos a la siguiente etapa
                $firstQuestionOfNextStage = $nextStage->questions()->orderBy('order')->first();
                $applicant->update([
                    'current_stage_id' => $nextStage->id,
                    'current_question_id' => $firstQuestionOfNextStage->id,
                ]);
                return response()->json([
                    'status' => 'stage_approved',
                    'message' => 'Has pasado a la siguiente etapa.',
                    'next_question' => $firstQuestionOfNextStage,
                ]);
            } else {
                // Fin del proceso, aprobación final
                $this->finalizeApplicant($applicant);
                return response()->json([
                    'status' => 'process_completed',
                    'applicant_id' => $applicant->id,
                    'is_approved' => $applicant->is_approved,
                    'message' => $applicant->is_approved ? '¡Felicidades, has sido aprobado!' : $applicant->rejection_reason,
                ]);
            }
        } else {
            // Si la IA rechazó, actualizamos el estado del solicitante
            $applicant->update([
                'process_status' => 'rejected',
                'is_approved' => false,
                'rejection_reason' => $validated['rejection_reason'],
            ]);
            return response()->json([
                'status' => 'stage_rejected',
                'message' => $validated['rejection_reason'],
                'applicant_id' => $applicant->id,
            ]);
        }
    }

    public function getStageDataForAi(string $chat_id)
    {
        $applicant = Applicant::where('chat_id', $chat_id)
                            ->where('process_status', 'in_progress')
                            ->firstOrFail();

        $currentStage = $applicant->currentStage;
        
        // Obtenemos todas las preguntas y respuestas de la etapa actual
        $stageData = [
            'stage_id' => $currentStage->id,
            'stage_name' => $currentStage->name,
            'approval_criteria' => $currentStage->approval_criteria,
            'questions' => []
        ];

        $questionsInStage = $currentStage->questions()->get();
        
        foreach ($questionsInStage as $question) {
            $userResponse = $applicant->responses()->where('question_id', $question->id)->first();
            
            $stageData['questions'][] = [
                'question_key' => $question->key,
                'question_text' => $question->question_text,
                'user_response' => $userResponse ? $userResponse->user_response : 'Sin respuesta',
                'approval_criteria' => $question->approval_criteria
            ];
        }

        return response()->json($stageData);
    }
}
