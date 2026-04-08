<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalAudit;
use Illuminate\Http\Request;

class WithdrawalAuditController extends Controller
{
    /**
     
       @OA\Get(
           path="/api/withdrawals-audit",
           summary="Liste des audits de retraits (paginée)",
           tags={"Audit"},
           security={{"bearerAuth":{}}},
      
           @OA\Response(
               response=200,
               description="Audits récupérés",
               @OA\JsonContent(
                   @OA\Property(property="status", type="integer", example=1),
                   @OA\Property(property="message", type="string", example="Liste des audits"),
                   @OA\Property(
                       property="data",
                       type="object",
      
                       @OA\Property(property="current_page", type="integer", example=1),
                       @OA\Property(
                           property="data",
                           type="array",
                           @OA\Items(ref="#/components/schemas/WithdrawalAudit")
                       ),
                       @OA\Property(property="per_page", type="integer", example=15),
                       @OA\Property(property="total", type="integer", example=3),
                       @OA\Property(property="last_page", type="integer", example=1),
      
                       @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
                       @OA\Property(property="last_page_url", type="string", nullable=true, example=null),
      
                   )
               )
           ),

      
           @OA\Response(response=401, description="Non authentifié"),
           @OA\Response(response=403, description="Accès refusé")
       )
     
    */
    public function index()
    {
        return response()->json([
            'status' => 1,
            'message' => 'Liste des audits',
            'data' => WithdrawalAudit::paginate(15)
        ]);
    }


    /**
        @OA\Get(
            path="/api/withdrawals-audit/stats",
            summary="Statistiques des opérations",
            tags={"Audit"},
            security={{"bearerAuth":{}}},

            @OA\Response(
                response=200,
                description="Statistiques des opérations",
                @OA\JsonContent(
                    @OA\Property(property="status",  type="integer", example=1),
                    @OA\Property(property="message", type="string",  example="Statistiques des opérations"),
                    @OA\Property(property="data", type="object",
                        @OA\Property(property="inserts", type="integer", example=10),
                        @OA\Property(property="updates", type="integer", example=5),
                        @OA\Property(property="deletes", type="integer", example=2)
                    )
                )
            ),
            @OA\Response(response=401, description="Non authentifié"),
            @OA\Response(response=403, description="Accès refusé")
        )
    */
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
