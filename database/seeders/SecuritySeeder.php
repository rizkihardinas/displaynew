<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Security;

class SecuritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Security::updateOrCreate(['id' => 1],[
            'username' => 'userBDI',
            'password' => 'userBDIpass',
            'key' => 'PARTNERKEY',
            'location' => 3107
        ]);
    }
}
