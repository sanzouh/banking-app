<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1 admin fixe
        $admin = User::factory()->create([
            'name'     => 'admin',
            'email'    => 'admin@admin.com',
            'password' => bcrypt('admin'),  // ou Hash::make('admin')
            'role'     => 'Admin',
        ]);

        // 10 clients aléatoires, chacun avec 1-3 retraits aléatoires
        Client::factory()
            ->count(10)
            ->has(
                Withdrawal::factory()
                    ->count(fake()->numberBetween(1, 3))
                    //modifie le retrait généré dans la foulée
                    ->state(function (array $attributes, Client $client) use ($admin) {
                        return [
                            'account_num' => $client->account_num, // affecte le account_num du client créé précédemment au account_num du retrait
                            'user_id'     => $admin->id_user,  // tous les retraits sont faits par l'admin
                        ];
                    })
            )
            ->create();

        // User::factory(10)->create();

/*         $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'role' => 'Admin'
        ]);
        $client = Client::factory()->create([
            'name' => 'Lio',
            'balance' => 300000
        ]);
        Withdrawal::factory()->create([
            'check_num' => 2574,
            'account_num' => $client->account_num,
            'amount' => 80000,
            'user_id'=> $user->id_user
        ]); */
    }
}
