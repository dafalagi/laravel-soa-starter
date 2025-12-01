<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;

class AuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Master Admin',
            'email' => 'masteradmin@example.com',
            'email_verified_at' => now(),
        ]);

        // if (app()->environment(['local', 'testing'])) {
        //     User::factory(10)->create();
        // }
    }
}