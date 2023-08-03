<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(5)->create();
        User::factory()->create([
            'name' => 'root',
            'email' => 'root@example.com',
            'password' => '$2y$10$JoA15fyJ0FRjjgOwVWwmLu/z2ANv1Pcs6VC6Wc0tcLAFtepgcdeh2'
        ]);
    }
}
