<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    function index() {
        $clients = Client::paginate(15);
        return response()->json([
            "status" => 1,
            "message" => "Clients récupérés",
            "datas" => $clients
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        $client = Client::create([
            "account_num" => $request->account_num,
            "name" => $request->name,
            "balance" => $request->balance
        ]);

        return response()->json([
            "status" => 1,
            "message" => "Client enregistré",
            "data" => $client
        ]);
    }

    function show($id) {
        $client = Client::find($id);

        if(!$client){
            return response()->json([
                "status" => 0,
                "message" => "Client non trouvé",
                "data" => null
            ], 404);
        }

        return response()->json([
            "status" => 1,
            "message" => "Client trouvé",
            "data" => $client
        ]);
    }

    function update(UpdateClientRequest $request, $id) {
        
/*         $client = Client::findOrFail($id);
        $client->account_num = $request->account_num;
        $client->name = $request->name;
        $client->balance = $request->balance;
        $client->save();

        return response()->json([
            "status" => 1,
            "message" => "Client mis à jour",
            "data" => $client
        ]); */
        
        $client = Client::findOrFail($id);

        if (!$client) {
            return response()->json([
                'status'  => 0,
                'message' => 'Client non trouvé',
                'data'    => null
            ], 404);
        }

        $client->update($request->validated());

        return response()->json([
            "status" => 1,
            "message" => "Client mis à jour",
            "data" => $client
        ]);
    }

    function destroy($id) {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'status'  => 0,
                'message' => 'Client non trouvé',
                'data'    => null
            ], 404);
        }

        $client->delete();

        return response()->json([
            "status" => 1,
            "message" => "Client supprimé",
            "data" => null
        ]);
    }
}
