<?php

namespace Database\Seeders;

use App\Models\Preference;
use App\Models\Rate;
use Illuminate\Database\Seeder;

class DefaultPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Preference::firstOrCreate([
            'id' => 1
        ]);
    }
}
