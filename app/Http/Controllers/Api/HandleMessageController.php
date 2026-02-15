<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendToN8nJob;
use Illuminate\Support\Facades\Cache;

class HandleMessageController extends Controller
{
    public function handle(Request $request)
    {
        $body = $request->all();

        if (!isset($body['entry'][0]['changes'][0]['value'])) {
            return response()->json(['ignored' => true]);
        }

        $value = $body['entry'][0]['changes'][0]['value'];

        // Ignorar statuses
        if (isset($value['statuses'])) {
            return response()->json(['ignored' => true]);
        }

        if (!isset($value['messages'][0])) {
            return response()->json(['ignored' => true]);
        }

        $message = $value['messages'][0];
        $type = $message['type'] ?? null;

        if ($type === 'reaction') {
            return response()->json(['ignored' => true]);
        }

        $allowedTypes = ['text', 'audio', 'image', 'video', 'document'];

        if (!in_array($type, $allowedTypes)) {
            return response()->json(['ignored' => true]);
        }

        $messageId = $message['id'] ?? null;

        if (!$messageId) {
            return response()->json(['ignored' => true]);
        }

        if (Cache::has("whatsapp_message_$messageId")) {
            return response()->json(['duplicate' => true]);
        }

        Cache::put("whatsapp_message_$messageId", true, now()->addHours(24));

        // 🔥 Enviamos EXACTAMENTE lo que llegó
        SendToN8nJob::dispatch($body);

        return response()->json(['queued' => true]);
    }


}
