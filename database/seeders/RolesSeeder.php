<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        Role::findOrCreate('teacher', 'web');
        Role::findOrCreate('supervisor', 'web');
    }
}
