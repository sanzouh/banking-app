<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalAudit;
use Illuminate\Http\Request;

class WithdrawalAuditController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 1,
            'message' => 'Liste des audits',
            'data' => WithdrawalAudit::paginate(15)
        ]);
    }

    public function stats()
    {
        return response()->json([
            'status' => 1,
            'message' => 'Statistiques des opérations',
            'data' => [
                'inserts' => WithdrawalAudit::where('action_type', 'INSERT')->count(),
                'updates' => WithdrawalAudit::where('action_type', 'UPDATE')->count(),
                'deletes' => WithdrawalAudit::where('action_type', 'DELETE')->count(),
            ]
        ]);
    }
}
