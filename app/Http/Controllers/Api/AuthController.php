<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {

        $rules = [
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users",
            "password" => "required|min:4" //|confirmed: pr que le password et le confirm_password soient identiques
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json([
                "status" => 0,
                "message" => "Validation error",
                "data" => $validator->errors()->all()
            ]);
        }

        $data = [];

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password) //Hash::make(): alternative
            // role = 'User' par défaut
        ]);
        
        $data["token"] = $user->createToken("register_token")->plainTextToken;
        $data["name"] = $user->name;
        $data["email"] = $user->email;

        return response()->json([
            "status" => 1,
            "message" => "utilisateur enregistré",
            "data" => $data
        ], 201);

    }

    /**
     * 
     * @return Response()
     */
    public function login(Request $request) {
    /*  1. Valider email + password

        2. Vérifier les credentials
        Auth::attempt(['email' => ..., 'password' => ...])
        → Si échec : retourner 401 Unauthorized

        3. Récupérer le user authentifié
        Auth::user()

        4. Générer le token Sanctum
        $user->createToken('auth_token')->plainTextToken

        5. Retourner token + user + role */

        $validated = $request->validate([
            "email" => "required|email|max:255",
            "password" => "required|string|max:255"
        ]);

        // si validate() échoue il retourne directement une 422 sans passer par le if
        if(Auth::attempt($validated)){
            
            $data = [];
            $user = Auth::user(); //Recup l'user authentifié
            
            // Régénérer le token à chaque login (plus sécurisé)
            // $user->tokens()->where('name', 'login_token')->delete(); 
            
            /** @var \App\Models\User $authenticatedUser */
            $data["token"] = $user->createToken("login_token")->plainTextToken;
            $data["name"] = $user->name;
            $data["email"] = $user->email;
            $data["role"] = $user->role;
    
            return response()->json([
                "status" => 1,
                "message" => "utilisateur connecté",
                "data" => $data
            ], 200);
        }

        return response()->json([
            "status" => 0,
            "message" => "Identifiants incorrects",
            "data" => null
        ], 401); // 401 = non autorisé
    }

    //logout
    
}
