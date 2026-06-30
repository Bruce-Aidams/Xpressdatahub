<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletApiController extends Controller
{
    public function balance(Request $request)
    {
        $agent = $request->attributes->get('api_key')->agent ?? null;

        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'success' => true,
            'balance' => floatval($agent->balance ?? 0),
            'currency' => 'GH₵',
        ]);
    }
}
