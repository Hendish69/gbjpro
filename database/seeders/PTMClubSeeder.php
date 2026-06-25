<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PTMClubSeeder extends Seeder
{
    public function run()
    {
        $clubs = [
            [
                'name' => 'Persatuan Tenis Meja DKI Jakarta',
                'code' => 'PTM-DKI',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'address' => 'Gelora Bung Karno',
                'phone' => '021-1234567',
                'email' => 'ptm.dki@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Persatuan Tenis Meja Jawa Barat',
                'code' => 'PTM-JABAR', 
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'address' => 'Jl. Asia Afrika No. 1',
                'phone' => '022-7654321',
                'email' => 'ptm.jabar@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Persatuan Tenis Meja Jawa Tengah',
                'code' => 'PTM-JATENG',
                'city' => 'Semarang',
                'province' => 'Jawa Tengah',
                'address' => 'Jl. Pemuda No. 1',
                'phone' => '024-1112233',
                'email' => 'ptm.jateng@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Persatuan Tenis Meja Jawa Timur',
                'code' => 'PTM-JATIM',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'address' => 'Jl. Tunjungan No. 1',
                'phone' => '031-4445566',
                'email' => 'ptm.jatim@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Club Tenis Meja Independen',
                'code' => 'INDEPENDENT',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'address' => 'Jl. Sudirman No. 1',
                'phone' => '021-9998888',
                'email' => 'independent@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ptm_clubs')->insert($clubs);
    }
}