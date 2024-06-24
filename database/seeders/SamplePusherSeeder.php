<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SamplePusherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::updateOrCreate(['id' => 1],[
            'pusher_key' => 'db230015e67b085df02f',
            'pusher_secret' => '8e717a8e3c765961a7d0',
            'pusher_app_id' => '1814534'
        ]);
    }
}
