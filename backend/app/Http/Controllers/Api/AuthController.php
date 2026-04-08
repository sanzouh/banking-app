<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
    
       @OA\Post(
           path="/api/register",
           summary="Créer un compte utilisateur",
           tags={"Auth"},
      
           @OA\RequestBody(
               required=true,
               @OA\JsonContent(
                   required={"name","email","password"},
                   @OA\Property(property="name",     type="string",  example="John Doe"),
                   @OA\Property(property="email",    type="string",  example="john@test.com"),
                   @OA\Property(property="password", type="string",  example="password123")
               )
           ),
      
           @OA\Response(
               response=201,
               description="Utilisateur enregistré",
               @OA\JsonContent(
                   @OA\Property(property="status",  type="integer", example=1),
                   @OA\Property(property="message", type="string",  example="utilisateur enregistré"),
                   @OA\Property(property="data", ref="#/components/schemas/RegisterResponse"
                   )
               )
           ),
           @OA\Response(response=422, description="Validation échouée")
       )

    */
    public function register(RegisterRequest $request) {
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password) //Hash::make(): alternative
            // role = 'User' par défaut
        ]);
        
        // un seul token à la création
        $user->tokens()->delete();
        
        return response()->json([
            "status" => 1,
            "message" => "utilisateur enregistré",
            "data" => [
                'token' => $user->createToken('register_token')->plainTextToken,
                'name'  => $user->name,
                'email' => $user->email
            ]
        ], 201);

    }

    /**
    
        @OA\Post(
            path="/api/login",
            summary="Se connecter",
            tags={"Auth"},
        
            @OA\RequestBody(
                required=true,
                @OA\JsonContent(
                    required={"email","password"},
                    @OA\Property(property="email",    type="string",  example="john@test.com"),
                    @OA\Property(property="password", type="string",  example="password123")
                )
            ),

            @OA\Response(
                response=200,
                description="Connecté",
                @OA\JsonContent(
                    @OA\Property(property="status",  type="integer", example=1),
                    @OA\Property(property="message", type="string",  example="utilisateur connecté"),
                    @OA\Property(property="data", ref="#/components/schemas/LoginResponse"
                    )
                )
            ),
            @OA\Response(response=401, description="Identifiants incorrects"),
            @OA\Response(response=422, description="Validation échouée")
        )

    */
    public function login(LoginRequest $request) {
    /**
        1. Valider email + password

        2. Vérifier les credentials
        Auth::attempt(['email' => ..., 'password' => ...])
        → Si échec : retourner 401 Unauthorized

        3. Récupérer le user authentifié
        Auth::user()

        4. Générer le token Sanctum
        $user->createToken('auth_token')->plainTextToken

        5. Retourner token + user + role 
    */

        if (!Auth::attempt($request->validated())) {
            return response()->json([
                'status'  => 0,
                'message' => 'Identifiants incorrects',
                'data'    => null
            ], 401); // 401 = non autorisé
        }
        
        /** @var \App\Models\User $user */
        $user = Auth::user(); //Recup l'user authentifié
        
        // Révoquer tous les anciens tokens avant d'en créer un nouveau
        $user->tokens()->delete();
        

        return response()->json([
            "status" => 1,
            "message" => "utilisateur connecté",
            "data" => [
                'token' => $user->createToken('login_token')->plainTextToken,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role
            ]
        ], 200);

    }

    /**
    
        @OA\Post(
            path="/api/logout",
            summary="Se déconnecter",
            tags={"Auth"},
            security={{"bearerAuth":{}}},

            @OA\Response(
                response=200,
                description="Déconnecté",
                @OA\JsonContent(
                    @OA\Property(property="status",  type="integer", example=1),
                    @OA\Property(property="message", type="string",  example="Déconnecté avec succès"),
                    @OA\Property(property="data",    type="null",    example=null)
                )
            ),
        ),
        @OA\Response(response=401, description="Non authentifié")

    */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Déconnecté avec succès',
            "data" => null
        ]);
    }
}
