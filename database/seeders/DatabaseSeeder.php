<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SecuritySeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(SamplePusherSeeder::class);
        $this->call(FfmpegSetting::class);
        $this->call(DefaultRate::class);
        // \App\Models\User::factory(10)->create();
    }
}
