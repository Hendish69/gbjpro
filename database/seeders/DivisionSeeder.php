<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run()
    {
        $divisions = [
            [
                'name' => 'Elite',
                'level' => 'national',
                'min_points' => 2500,
                'max_points' => null,
                'color' => '#dc3545',
                'description' => 'Pemain tingkat nasional/internasional',
                'order' => 1,
            ],
            [
                'name' => 'A',
                'level' => 'regional',
                'min_points' => 2000,
                'max_points' => 2499,
                'color' => '#fd7e14',
                'description' => 'Pemain tingkat regional advanced',
                'order' => 2,
            ],
            [
                'name' => 'B',
                'level' => 'regional',
                'min_points' => 1500,
                'max_points' => 1999,
                'color' => '#ffc107',
                'description' => 'Pemain tingkat regional intermediate',
                'order' => 3,
            ],
            [
                'name' => 'C',
                'level' => 'club',
                'min_points' => 1000,
                'max_points' => 1499,
                'color' => '#20c997',
                'description' => 'Pemain klub level advanced',
                'order' => 4,
            ],
            [
                'name' => 'D',
                'level' => 'club',
                'min_points' => 500,
                'max_points' => 999,
                'color' => '#0dcaf0',
                'description' => 'Pemain klub level intermediate',
                'order' => 5,
            ],
            [
                'name' => 'Pemula',
                'level' => 'beginner',
                'min_points' => 0,
                'max_points' => 499,
                'color' => '#6c757d',
                'description' => 'Pemain pemula',
                'order' => 6,
            ],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}