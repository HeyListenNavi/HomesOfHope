<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub.mode');
        $challenge = $request->query('hub.challenge');
        $verifyToken = $request->query('hub.verify_token');

        Log::info('Meta webhook verification attempt', [
            'mode' => $mode,
        ]);

        if ($mode === 'subscribe' &&
            $verifyToken === config('services.meta.verify_token')
        ) {
            Log::info('Meta webhook verified successfully');

            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Meta webhook verification failed');

        return response('Forbidden', 403);
    }
}
