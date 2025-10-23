<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        // Seed a few example schools if none exist
        if (School::count() === 0) {
            School::create(['name' => 'SD Negeri Japoh 1', 'address' => 'Jl. Tangen-Jenar Km. 01 Japoh, Japoh, Kec. Jenar, Kab. Sragen, Jawa Tengah']);
            School::create(['name' => 'SD Negeri Japoh 2', 'address' => 'Jl. Tangen-Jenar Km. 02 Japoh, Japoh, Kec. Jenar, Kab. Sragen, Jawa Tengah']);
        }
    }
}
