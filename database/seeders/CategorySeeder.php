<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory()
            ->count(4)
            ->sequence(
                ['name' => '上衣'],
                ['name' => '外套'],
                ['name' => '背心'],
                ['name' => '襯衫'],
            )
            ->create();
    }
}
