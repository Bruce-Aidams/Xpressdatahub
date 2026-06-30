<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PendingPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('agent:id,username', 'order:id,phone_number,network_type,package_size');

        if ($agentId = $request->input('agent_id')) {
            $query->where('agent_id', $agentId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $payments = $query->orderByDesc('created_at')->paginate(25);

        return response()->json([
            'success' => true,
            'payments' => $payments,
        ]);
    }
}
