<?php

namespace App\Jobs;

use App\Models\FamilyMember;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mayaram\LaravelOcr\Facades\LaravelOcr;

class ProcessMemberOcrJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public FamilyMember $member,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $documents = $this->member->documents()->get();

        foreach ($documents as $document) {
            $rawText = $this->extractTextFromDocument($document);

            if (empty($rawText)) {
                continue;
            }

            $extractedData = $this->extractStructuredData($rawText);

            if ($extractedData) {
                $this->updateMemberFromOcr($extractedData);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("OCR processing failed for member {$this->member->id}: ".$exception->getMessage());
    }

    protected function extractTextFromDocument($document): string
    {
        $tempPath = 'temp/ocr/'.$document->original_name;

        Storage::disk('local')->makeDirectory('temp/ocr');
        Storage::disk('local')->put($tempPath, Storage::disk('r2')->get($document->file_path));

        try {
            $result = LaravelOcr::driver('google_vision')->extract(
                Storage::disk('local')->path($tempPath)
            );

            return $result['text'] ?? '';
        } finally {
            Storage::disk('local')->delete($tempPath);
        }
    }

    protected function extractStructuredData(string $rawText): ?array
    {
        try {
            $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
                ->withSystemInstruction(
                    Content::parse('Eres un experto en extracción de datos de documentos de identificación mexicanos (INE, CURP, actas de nacimiento, comprobantes de domicilio, etc.). Tu único objetivo es extraer datos precisos del texto OCR proporcionado. No inventes información que no esté presente en el texto.
                        Reglas de formato:
                        - Nombre, apellidos: primera letra en mayúscula, resto en minúscula (Title Case).
                        - País: nombre completo en Title Case, nunca uses siglas ni abreviaturas (ej. "MX" → "México", "USA" → "Estados Unidos").
                        - Estado: nombre común del estado en Title Case, sin sufijos oficiales ni abreviaturas. Ejemplos: "VERACRUZ-LLAVE" → "Veracruz", "MICHOACÁN DE OCAMPO" → "Michoacán", "BC" → "Baja California", "JAL" → "Jalisco". Si no se puede determinar, déjalo como null.
                        - CURP: exactamente 18 caracteres en mayúsculas.
                        - Fecha: formato YYYY-MM-DD.'
                    )
                )
                ->withGenerationConfig(
                    new GenerationConfig(
                        responseMimeType: ResponseMimeType::APPLICATION_JSON,
                        responseSchema: new Schema(
                            type: DataType::OBJECT,
                            properties: [
                                'nombre' => new Schema(type: DataType::STRING),
                                'apellido_paterno' => new Schema(type: DataType::STRING),
                                'apellido_materno' => new Schema(type: DataType::STRING),
                                'curp' => new Schema(type: DataType::STRING),
                                'fecha_nacimiento' => new Schema(type: DataType::STRING),
                                'sexo' => new Schema(type: DataType::STRING),
                                'lugar_nacimiento' => new Schema(type: DataType::STRING),
                                'pais_origen' => new Schema(type: DataType::STRING),
                                'estado_origen' => new Schema(type: DataType::STRING),
                            ],
                            required: ['nombre', 'apellido_paterno', 'apellido_materno'],
                        )
                    )
                )
                ->generateContent($rawText);

            $json = $response->json();

            if (is_object($json)) {
                $json = (array) $json;
            }

            if (! is_array($json)) {
                return null;
            }

            return $json;
        } catch (\Exception $e) {
            Log::error('Gemini extraction failed for member '.$this->member->id.': '.$e->getMessage());

            return null;
        }
    }

    protected function updateMemberFromOcr(array $data): void
    {
        $updates = [];

        if (! empty($data['nombre']) && $data['nombre'] !== 'null' && empty($this->member->name)) {
            $updates['name'] = $data['nombre'];
        }
        if (! empty($data['apellido_paterno']) && $data['apellido_paterno'] !== 'null' && empty($this->member->paternal_surname)) {
            $updates['paternal_surname'] = $data['apellido_paterno'];
        }
        if (! empty($data['apellido_materno']) && $data['apellido_materno'] !== 'null' && empty($this->member->maternal_surname)) {
            $updates['maternal_surname'] = $data['apellido_materno'];
        }
        if (! empty($data['curp']) && $data['curp'] !== 'null' && empty($this->member->curp)) {
            $updates['curp'] = strtoupper($data['curp']);
        }
        if (! empty($data['fecha_nacimiento']) && $data['fecha_nacimiento'] !== 'null' && empty($this->member->birth_date)) {
            $updates['birth_date'] = $data['fecha_nacimiento'];
        }
        if (! empty($data['pais_origen']) && $data['pais_origen'] !== 'null' && empty($this->member->origin_country)) {
            $updates['origin_country'] = $data['pais_origen'];
        }
        if (! empty($data['estado_origen']) && $data['estado_origen'] !== 'null' && empty($this->member->origin_state)) {
            $updates['origin_state'] = $data['estado_origen'];
        }

        if (! empty($updates)) {
            $this->member->update($updates);
        }
    }
}
