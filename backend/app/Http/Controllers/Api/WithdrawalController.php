<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Http\Requests\Withdrawal\StoreWithdrawalRequest;
use App\Http\Requests\Withdrawal\UpdateWithdrawalRequest;

class WithdrawalController extends Controller
{

    /**
       @OA\Get(
           path="/api/withdrawals",
           summary="Liste des retraits",
           tags={"Retraits"},
           security={{"bearerAuth":{}}},
      
           @OA\Response(
               response=200,
               description="Retraits récupérés",
               @OA\JsonContent(
                   @OA\Property(property="status",  type="integer", example=1),
                   @OA\Property(property="message", type="string",  example="Retraits récupérés"),
                   @OA\Property(property="data", type="array",
                       @OA\Items(ref="#/components/schemas/Withdrawal")
                   )
               )
           ),
           @OA\Response(response=401, description="Non authentifié")
       )
     */
    public function index()
    {
        return response()->json([
            'status' => 1,
            'message' => 'Liste des retraits',
            'data' => Withdrawal::with('client')->get()
        ]);
    }

    /**
        @OA\Post(
            path="/api/withdrawals",
            summary="Créer un retrait",
            tags={"Retraits"},
            security={{"bearerAuth":{}}},

            @OA\RequestBody(
                required=true,
                @OA\JsonContent(
                    required={"withdraw_num","check_num","account_num","amount"},
                    @OA\Property(property="withdraw_num", type="integer", example=11111),
                    @OA\Property(property="check_num",    type="integer", example=22222),
                    @OA\Property(property="account_num",  type="integer", example=12345),
                    @OA\Property(property="amount",       type="number",  format="float", example=500.00)
                )
            ),

            @OA\Response(
                response=201,
                description="Retrait créé",
                @OA\JsonContent(
                    @OA\Property(property="status",  type="integer", example=1),
                    @OA\Property(property="message", type="string",  example="Retrait créé avec succès"),
                    @OA\Property(property="data",    ref="#/components/schemas/Withdrawal")
                )
            ),
            @OA\Response(response=422, description="Validation échouée"),
            @OA\Response(response=401, description="Non authentifié")
        )
    */
    public function store(StoreWithdrawalRequest $request)
    {
        $withdrawal = Withdrawal::create(array_merge(
            $request->validated(), 
            ['user_id' => $request->user()->id_user]) // ← l'user authentifié
        );

        return response()->json([
            'status' => 1,
            'message' => 'Retrait créé avec succès',
            'data' => $withdrawal
        ], 201);
    }

    /**
        @OA\Get(
            path="/api/withdrawals/{withdraw_num}",
            summary="Afficher un retrait",
            tags={"Retraits"},
            security={{"bearerAuth":{}}},

            @OA\Parameter(
                name="withdraw_num",
                in="path",
                required=true,
                @OA\Schema(type="integer", example=11111)
            ),

            @OA\Response(
                response=200,
                description="Retrait trouvé",
                @OA\JsonContent(
                    @OA\Property(property="status",  type="integer", example=1),
                    @OA\Property(property="message", type="string",  example="Retrait trouvé"),
                    @OA\Property(property="data",    ref="#/components/schemas/Withdrawal")
                )
            ),
            @OA\Response(response=404, description="Retrait non trouvé"),
            @OA\Response(response=401, description="Non authentifié")
        )
    */
    public function show(string $id)
    {
        $withdrawal = Withdrawal::find($id);

        if(!$withdrawal){
            return response()->json([
                "status" => 0,
                "message" => "Retrait non trouvé",
                "data" => null
            ], 404);
        }

        return response()->json([
            "status" => 1,
            "message" => "Retrait trouvé",
            "data" => $withdrawal
        ]);
    }

    /**
        @OA\Put(
            path="/api/withdrawals/{withdraw_num}",
            summary="Mettre à jour un retrait",
            tags={"Retraits"},
            security={{"bearerAuth":{}}},

            @OA\Parameter(
                name="withdraw_num",
                in="path",
                required=true,
                @OA\Schema(type="integer", example=11111)
            ),

            @OA\RequestBody(
                @OA\JsonContent(
                    @OA\Property(property="check_num",   type="integer", example=33333),
                    @OA\Property(property="account_num", type="integer", example=12345),
                    @OA\Property(property="amount",      type="number",  format="float", example=999.99)
                )
            ),

            @OA\Response(
                response=200,
                description="Retrait mis à jour",
                @OA\JsonContent(
                    @OA\Property(property="status",  type="integer", example=1),
                    @OA\Property(property="message", type="string",  example="Retrait mis à jour avec succès"),
                    @OA\Property(property="data",    ref="#/components/schemas/Withdrawal")
                )
            ),
            @OA\Response(response=404, description="Retrait non trouvé"),
            @OA\Response(response=422, description="Validation échouée"),
            @OA\Response(response=401, description="Non authentifié")
        )
    */
    public function update(UpdateWithdrawalRequest $request, string $id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        
        if(!$withdrawal) {
            return response()->json([
                'status' => 0,
                'message' => 'Retrait non trouvé',
                'data' => null
            ], 404);
        }

        $withdrawal->update($request->validated());

        return response()->json([
            'status' => 1,
            'message' => 'Retrait mis à jour avec succès',
            'data' => $withdrawal
        ]);
    }

    /**
        @OA\Delete(
            path="/api/withdrawals/{withdraw_num}",
            summary="Supprimer un retrait",
            tags={"Retraits"},
            security={{"bearerAuth":{}}},

            @OA\Parameter(
                name="withdraw_num",
                in="path",
                required=true,
                @OA\Schema(type="integer", example=11111)
            ),

            @OA\Response(
                response=200,
                description="Retrait supprimé",
                @OA\JsonContent(
                    @OA\Property(property="status",  type="integer", example=1),
                    @OA\Property(property="message", type="string",  example="Retrait supprimé"),
                    @OA\Property(property="data",    type="null",    example=null)
                )
            ),
            @OA\Response(response=404, description="Retrait introuvable"),
            @OA\Response(response=401, description="Non authentifié")
        )
    */
    public function destroy(string $id)
    {
        $withdrawal = Withdrawal::find($id);

        if (!$withdrawal) {
            return response()->json([
                'status' => 0, 
                'message' => 'Retrait introuvable'
            ], 404);
        }

        $withdrawal->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Retrait supprimé',
            "data" => null
        ]);
    }
}
