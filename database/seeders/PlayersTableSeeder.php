<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayersTableSeeder extends Seeder
{
    public function run()
    {
        Player::create([
            'name' => 'Zhang Jike',
            'email' => 'zhang@example.com',
            'rating' => 2850,
            'bio' => 'Chinese table tennis player, two-time World Champion and Olympic gold medalist.'
        ]);

        Player::create([
            'name' => 'Ma Long',
            'email' => 'ma@example.com',
            'rating' => 2900,
            'bio' => 'Chinese table tennis player, considered one of the greatest players of all time.'
        ]);

        // Add more sample players...
    }
}