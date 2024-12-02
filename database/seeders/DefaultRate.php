<?php

namespace Database\Seeders;

use App\Models\Rate;
use Illuminate\Database\Seeder;

class DefaultRate extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Rate::updateOrCreate(['id' => 1],[
            'vehicle' => 'MOBIL',
            'price' => '5000;4000',
            'fine' => '50000',
            'is_default' => 1,
        ]);
    }
}
