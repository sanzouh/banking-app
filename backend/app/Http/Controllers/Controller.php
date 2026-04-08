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

           @OA\Schema(
               schema="Withdrawal",
               @OA\Property(property="withdraw_num", type="integer", example=11111),
               @OA\Property(property="check_num", type="integer", example=22222),
               @OA\Property(property="account_num", type="integer", example=12345),
               @OA\Property(property="amount", type="number", format="float", example=500.00),
               @OA\Property(property="user_id", type="integer", example=1),
           ),

            @OA\Schema(
                schema="WithdrawalAudit",
                @OA\Property(property="id",           type="integer", example=1),
                @OA\Property(property="action_type",  type="string",  example="INSERT"),
                @OA\Property(property="created_at",   type="string",  format="datetime", example="2026-01-01 12:00:00"),
                @OA\Property(property="withdraw_num", type="integer", example=11111),
                @OA\Property(property="account_num",  type="integer", example=12345),
                @OA\Property(property="client_name",  type="string",  example="Rakoto Andry"),
                @OA\Property(property="old_amount",   type="number",  format="float", example=1000.00, nullable=true),
                @OA\Property(property="new_amount",   type="number",  format="float", example=2000.00, nullable=true),
                @OA\Property(property="user",         type="string",  example="John Doe")
            )
        ),
    )

 */
abstract class Controller
{
    //
}
