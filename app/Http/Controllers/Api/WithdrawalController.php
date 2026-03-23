<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Http\Requests\Withdrawal\StoreWithdrawalRequest;
use App\Http\Requests\Withdrawal\UpdateWithdrawalRequest;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
