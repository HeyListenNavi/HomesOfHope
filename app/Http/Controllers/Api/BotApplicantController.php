<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Stage;
use App\Models\Question;
use App\Models\ApplicantQuestionResponse;
use App\Services\GroupAssignmentService;
use finfo;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


/**
 * Clase controladora para gestionar el flujo de evaluación de los solicitantes a través del bot.
 *
 * Maneja el inicio del proceso, la recepción de respuestas y el avance entre etapas.
 */
class BotApplicantController extends Controller
{
    protected $groupAssignmentService;

    public function __construct(GroupAssignmentService $groupAssignmentService)
    {
        $this->groupAssignmentService = $groupAssignmentService;
    }

    /**
     * Inicia el proceso de evaluación para un nuevo solicitante.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Procesa la respuesta de un usuario a una pregunta.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $chatId El ID del chat del solicitante.
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAnswer(Request $request, string $chatId)
    {
        $validated = $request->validate([
            'question_key' => 'required|string',
            'user_response' => 'required|string',
        ]);

        $applicant = Applicant::where('chat_id', $chatId)
                              ->where('process_status', 'in_progress')
                              ->firstOrFail();
        $question = Question::where('key', $validated['question_key'])->firstOrFail();

        // Guarda la respuesta en el historial
        $response = ApplicantQuestionResponse::where('applicant_id', $applicant->id)
                                             ->where('question_id', $question->id)
                                             ->first();
        if ($response) {
            $response->update([
                'user_response' => $validated['user_response'],
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Obtiene la siguiente pregunta en el flujo de evaluación.
     *
     * @param string $chatId El ID del chat del solicitante.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextQuestion(string $chatId)
    {
        $applicant = Applicant::where('chat_id', $chatId)
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
            // Avanza a la siguiente pregunta de la misma etapa
            $applicant->update(['current_question_id' => $nextQuestion->id]);
            
            return response()->json([
                'status' => 'next_question',
                'question' => $nextQuestion,
                'next_question_text' => $nextQuestion->question_text,
                'validation_rules' => $nextQuestion->validation_rules,
            ]);
        }
    }

    /**
     * Nuevo método para recibir la decisión de la IA desde n8n
     * y avanzar a la siguiente etapa o finalizar el proceso.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                $this->finalizeApplicant($applicant, true);
                return response()->json([
                    'status' => 'process_completed',
                    'applicant_id' => $applicant->id,
                    'message' => '¡Felicidades, has sido aprobado!',
                ]);
            }
        } else {
            // Si la IA rechazó, actualizamos el estado del solicitante
            $applicant->update([
                'process_status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
            ]);
            return response()->json([
                'status' => 'stage_rejected',
                'message' => $validated['rejection_reason'],
                'applicant_id' => $applicant->id,
            ]);
        }
    }

    /**
     * Obtiene los datos de la etapa para ser enviados a la IA para su evaluación.
     *
     * @param string $chatId El ID del chat del solicitante.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStageDataForAi(string $chatId)
    {
        $applicant = Applicant::where('chat_id', $chatId)
                              ->where('process_status', 'in_progress')
                              ->firstOrFail();

        $currentStage = $applicant->currentStage;
        
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
    
    /**
     * Finaliza el proceso de un solicitante, ya sea aprobándolo o rechazándolo.
     *
     * @param \App\Models\Applicant $applicant
     * @param bool $isApproved
     * @return void
     */
    private function finalizeApplicant(Applicant $applicant, bool $isApproved)
    {
        if ($isApproved) {
            $group = $this->groupAssignmentService->assignApplicantToGroup($applicant);
            $applicant->update([
                'process_status' => 'approved',
                'group_id' => $group->id,
            ]);
        } else {
            $applicant->update([
                'process_status' => 'rejected',
                'rejection_reason' => 'No cumple con los requisitos finales.',
            ]);
        }
    }

    public function aplicantCurrentStatus( $chatId ){
        $applicant = Applicant::where("chat_id", $chatId)->first();

        if (!$applicant) {
            return response()->json(['error' => 'Solicitante no encontrado.'], 404);
        }

        return response()->json([
            "current_stage" => $applicant->current_stage_id,
            "current_question" => $applicant->current_question_id
        ]);
    }

    public function currentStageQuestions( $stageId ){
        $stage = Stage::find($stageId);

        if (!$stage) {
            return response()->json(['error' => 'Etapa no encontrada.'], 404);
        }

        $questions = $stage->questions;

        return response()->json($questions);
    }

    public function sendInitialData( Request $request ){
        $validated = $request->validate([
            "chat_id" => "required|string",
            "applicant_name" => "required|string",
            "curp" => "required|string",
            "gender" => "required|string",
        ]);

        $applicant = Applicant::where("chat_id", $validated["chat_id"])->first();

        if (!$applicant) {
            return response()->json(['error' => 'Solicitante no encontrado.'], 404);
        }

        $applicant->update([
            "curp" => $validated["curp"],
            "applicant_name" => $validated["applicant_name"], // Corregido
            "gender" => $validated["gender"],
        ]);
        
        return response()->json(['message' => 'Datos iniciales actualizados correctamente.'], 200);
    }

    public function updateAnswer( Request $request ){
        $request->validate([
            'chat_id' => 'required|integer',
            'question_id' => 'required|integer',
            'new_response' => 'required|string',
        ]);

        $applicant = Applicant::where('chat_id', $request->input('chat_id'))->first();

        if (!$applicant) {
            return response()->json(['error' => 'No se encontró al solicitante con ese chat_id.'], 404);
        }

        $response = ApplicantQuestionResponse::where('applicant_id', $applicant->id)
                                             ->where('question_id', $request->input('question_id'))
                                             ->first();

        if (!$response) {
            return response()->json(['error' => 'No se encontró una respuesta para esa pregunta.'], 404);
        }

        $response->user_response = $request->input('new_response');
        $response->save();

        return response()->json(['message' => 'Respuesta actualizada exitosamente.'], 200);
    }
}
 