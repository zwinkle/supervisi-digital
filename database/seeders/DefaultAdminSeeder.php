<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('DEFAULT_ADMIN_EMAIL', 'admin@example.com');
        $password = env('DEFAULT_ADMIN_PASSWORD', 'Admin123!');
        $name = env('DEFAULT_ADMIN_NAME', 'Administrator');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        if (!$user->is_admin) {
            $user->is_admin = true;
            $user->save();
        }

        $this->command?->info("Default admin ready. Email: {$email}");
    }
}
