<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
    
        @OA\Get(
            path="/api/clients",
            summary="Liste des clients (paginée)",
            tags={"Clients"},
            security={{"bearerAuth":{}}},
      
            @OA\Response(
                response=200,
                description="Clients récupérés",
                @OA\JsonContent(
                    @OA\Property(property="status",  type="integer", example=1),
                    @OA\Property(property="message", type="string",  example="Clients récupérés"),
                    @OA\Property(property="datas", type="object",
                        @OA\Property(property="current_page", type="integer", example=1),
                        @OA\Property(property="per_page",     type="integer", example=15),
                        @OA\Property(property="total",        type="integer", example=50),
                        @OA\Property(property="data", type="array",
                            @OA\Items(ref="#/components/schemas/Client")
                        )
                    )
                )
            ),
            @OA\Response(response=401, description="Non authentifié")
        )
    
    */
    function index() {
        $clients = Client::paginate(15);
        return response()->json([
            "status" => 1,
            "message" => "Clients récupérés",
            "datas" => $clients
        ]);
    }


    /**
    
        @OA\Post(
            path="/api/clients",
            summary="Créer un client",
            tags={"Clients"},
            security={{"bearerAuth":{}}},
      
            @OA\RequestBody(
                required=true,
                @OA\JsonContent(
                    required={"account_num","name","balance"},
                    @OA\Property(property="account_num", type="integer", example=12345),
                    @OA\Property(property="name",        type="string",  example="Rakoto Andry"),
                    @OA\Property(property="balance",     type="number",  format="float", example=1500.00)
                )
            ),
      
           @OA\Response(
                response=200,
                description="Client enregistré",
                @OA\JsonContent(
                   @OA\Property(property="status",  type="integer", example=1),
                   @OA\Property(property="message", type="string",  example="Client enregistré"),
                   @OA\Property(property="data",    ref="#/components/schemas/Client")
                )
            ),
            @OA\Response(response=422, description="Validation échouée"),
            @OA\Response(response=401, description="Non authentifié")
        )

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


    /**
    
       @OA\Get(
           path="/api/clients/{account_num}",
           summary="Afficher un client",
           tags={"Clients"},
           security={{"bearerAuth":{}}},
      
           @OA\Parameter(
               name="account_num",
               in="path",
               required=true,
               @OA\Schema(type="integer", example=12345)
           ),
      
           @OA\Response(
               response=200,
               description="Client trouvé",
               @OA\JsonContent(
                   @OA\Property(property="status",  type="integer", example=1),
                   @OA\Property(property="message", type="string",  example="Client trouvé"),
                   @OA\Property(property="data",    ref="#/components/schemas/Client")
               )
           ),
           @OA\Response(response=404, description="Client non trouvé"),
           @OA\Response(response=401, description="Non authentifié")
       )
    
     */
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

    /**
    
       @OA\Put(
           path="/api/clients/{account_num}",
           summary="Mettre à jour un client",
           tags={"Clients"},
           security={{"bearerAuth":{}}},
      
           @OA\Parameter(
               name="account_num",
               in="path",
               required=true,
               @OA\Schema(type="integer", example=12345)
           ),
      
           @OA\RequestBody(
               @OA\JsonContent(
                   @OA\Property(property="name",    type="string", example="Nouveau Nom"),
                   @OA\Property(property="balance", type="number", format="float", example=9999.99)
               )
           ),

            @OA\Response(
               response=200,
               description="Client mis à jour",
               @OA\JsonContent(
                   @OA\Property(property="status",  type="integer", example=1),
                   @OA\Property(property="message", type="string",  example="Client mis à jour"),
                   @OA\Property(property="data",    ref="#/components/schemas/Client")
               )
           ),
           @OA\Response(response=404, description="Client non trouvé"),
           @OA\Response(response=422, description="Validation échouée"),
           @OA\Response(response=401, description="Non authentifié")
       ),

    */
    function update(UpdateClientRequest $request, $id) {
        
    /**         
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
    */
        
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


    /**
     
       @OA\Delete(
           path="/api/clients/{account_num}",
           summary="Supprimer un client",
           tags={"Clients"},
           security={{"bearerAuth":{}}},
      
           @OA\Parameter(
               name="account_num",
               in="path",
               required=true,
               @OA\Schema(type="integer", example=12345)
           ),
      
           @OA\Response(
               response=200,
               description="Client supprimé",
               @OA\JsonContent(
                   @OA\Property(property="status",  type="integer", example=1),
                   @OA\Property(property="message", type="string",  example="Client supprimé"),
                   @OA\Property(property="data",    type="null",    example=null)
               )
           ),
           @OA\Response(response=404, description="Client non trouvé"),
           @OA\Response(response=401, description="Non authentifié")
       ),

    */
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
