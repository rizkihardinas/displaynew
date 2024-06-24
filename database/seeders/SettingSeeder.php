<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::updateOrCreate(['id' => 1],[
            'text_promotion' => 'Teks promosi anda disini',
            'duration' => 15
        ]);
    }
}
