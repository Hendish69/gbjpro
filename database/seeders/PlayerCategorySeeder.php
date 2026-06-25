<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlayerCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Junior',
                'color' => '#20c997',
                'description' => 'Pemain usia muda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Senior',
                'color' => '#fd7e14', 
                'description' => 'Pemain usia dewasa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Veteran',
                'color' => '#6f42c1',
                'description' => 'Pemain usia lanjut',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'National Player',
                'color' => '#dc3545',
                'description' => 'Pemain tingkat nasional',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cadet',
                'color' => '#0dcaf0',
                'description' => 'Pemain pemula',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('player_categories')->insert($categories);
    }
}