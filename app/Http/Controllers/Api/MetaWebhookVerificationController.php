<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->input('hub_mode');
        $challenge = $request->input('hub_challenge');
        
        return response($challenge, 200)
            ->header('Content-Type', 'text/plain');
        
        return response('Forbidden', 403);
    }
}
