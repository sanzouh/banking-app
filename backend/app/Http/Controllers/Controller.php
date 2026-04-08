<?php

namespace App\Http\Controllers;

/**
    @OA\OpenApi(
        @OA\Info(
            title="Banking App APIs",
            version="1.0.0",
        ),

        @OA\Server(
            url="http://localhost:8000",
            description="Serveur de développement local"
        ),

        @OA\Components(
            @OA\SecurityScheme(
                securityScheme="bearerAuth",
                in="header",
                name="bearerAuth",
                type="http",
                scheme="bearer",
                bearerFormat="Sanctum",
            ),

            @OA\Schema(
                schema="RegisterResponse",
                @OA\Property(property="token", type="string", example="1|abc123..."),
                @OA\Property(property="name", type="string", example="John Doe"),
                @OA\Property(property="email", type="string", example="john@test.com")
            ),

            @OA\Schema(
                schema="LoginResponse",
                @OA\Property(property="token", type="string", example="1|abc123..."),
                @OA\Property(property="name", type="string", example="John Doe"),
                @OA\Property(property="email", type="string", example="john@test.com"),
                @OA\Property(property="role", type="string", example="User")
            ),

            @OA\Schema(
                schema="Client",
                @OA\Property(property="account_num", type="integer", example=12345),
                @OA\Property(property="name",        type="string",  example="Rakoto Andry"),
                @OA\Property(property="balance",     type="number",  format="float", example=1500.00)
            ),
        ),
    )

 */
abstract class Controller
{
    //
}
