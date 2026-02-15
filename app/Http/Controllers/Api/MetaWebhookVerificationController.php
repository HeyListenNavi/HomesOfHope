<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookVerificationController extends Controller
{
    public function verify(Request $request)
    {
        return response($request->query('hub.challenge'), 200);
    }

}
