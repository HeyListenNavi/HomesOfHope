<?php

namespace App\Jobs;

use App\Models\Testimony;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessTestimonyTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Testimony $testimony,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (blank($this->testimony->audio_path)) {
            return;
        }

        $disk = Storage::disk('r2');
        if (! $disk->exists($this->testimony->audio_path)) {
            Log::warning("Audio file not found on disk r2 for testimony {$this->testimony->id}: {$this->testimony->audio_path}");

            return;
        }

        $audioBytes = $disk->get($this->testimony->audio_path);
        if (empty($audioBytes)) {
            Log::warning("Audio file is empty for testimony {$this->testimony->id}: {$this->testimony->audio_path}");

            return;
        }

        $mimeType = $this->resolveMimeType($this->testimony->audio_path);
        $extracted = $this->transcribeAndSummarize($audioBytes, $mimeType);

        if ($extracted) {
            $updates = [];

            if (! empty($extracted['transcription'])) {
                $updates['transcription'] = $extracted['transcription'];
            }

            if (! empty($extracted['summary'])) {
                $updates['summary'] = $extracted['summary'];
            }

            if (! empty($updates)) {
                $this->testimony->update($updates);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Transcription processing failed for testimony {$this->testimony->id}: ".$exception->getMessage());
    }

    protected function transcribeAndSummarize(string $audioBytes, MimeType $mimeType): ?array
    {
        try {
            $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
                ->withSystemInstruction(
                    Content::parse(
                        'You are an expert audio transcriptionist and analyst for personal testimonies. '.
                        'Task 1: Transcribe the audio verbatim in the exact language spoken by the speaker (e.g. if spoken in English, transcribe in English; if spoken in Spanish, transcribe in Spanish). Do not translate the transcription. '.
                        'Task 2: Provide a concise, clear 2-3 sentence summary of the testimony, also written in the same language spoken in the audio. '
                    )
                )
                ->withGenerationConfig(
                    new GenerationConfig(
                        responseMimeType: ResponseMimeType::APPLICATION_JSON,
                        responseSchema: new Schema(
                            type: DataType::OBJECT,
                            properties: [
                                'transcription' => new Schema(type: DataType::STRING),
                                'summary' => new Schema(type: DataType::STRING),
                            ],
                            required: ['transcription', 'summary'],
                        )
                    )
                )
                ->generateContent([
                    'Transcribe and summarize this audio testimony:',
                    new Blob(
                        mimeType: $mimeType,
                        data: base64_encode($audioBytes),
                    ),
                ]);

            $json = $response->json();

            if (is_object($json)) {
                $json = (array) $json;
            }

            if (! is_array($json)) {
                return null;
            }

            return $json;
        } catch (\Throwable $e) {
            Log::error("Gemini transcription failed for testimony {$this->testimony->id}: ".$e->getMessage());

            return null;
        }
    }

    protected function resolveMimeType(string $path): MimeType
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'wav' => MimeType::AUDIO_WAV,
            'ogg', 'oga' => MimeType::AUDIO_OGG,
            'aac', 'm4a' => MimeType::AUDIO_AAC,
            'flac' => MimeType::AUDIO_FLAC,
            'aif', 'aiff' => MimeType::AUDIO_AIFF,
            'webm' => MimeType::VIDEO_WEBM,
            'mp4' => MimeType::VIDEO_MP4,
            default => MimeType::AUDIO_MP3,
        };
    }
}
