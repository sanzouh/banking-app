<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    function index() {
        $clients = Client::paginate(5);
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

        if($client){
            return response()->json([
                "status" => 0,
                "message" => "Client trouvé",
                "data" => $client
            ]);
        } else{
            return response()->json([
                "status" => 1,
                "message" => "Client non trouvé",
                "data" => null
            ]);
        }
    }

    function update(Request $request, $id) {
        $client = Client::findOrFail($id);
        $client->account_num = $request->account_num;
        $client->name = $request->name;
        $client->balance = $request->balance;
        $client->save();

        return response()->json([
            "status" => 1,
            "message" => "Client mis à jour",
            "data" => $client
        ]);
        
        /* $clientToBeUpdated = Client::findOrFail($id);
        $clientToBeUpdated->update($request->all());

        $upToDateClient = Client::find($clientToBeUpdated->account_num);
        return response()->json([
            "status" => 1,
            "message" => "Client mis à jour",
            "data" => $upToDateClient
        ]); */
    }

    function destroy($id) {
        $clientToBeDeleted = Client::find($id);
        $clientToBeDeleted->delete();
        return response()->json([
            "status" => 1,
            "message" => "Client supprimé",
            "data" => null
        ]);
    }
}
