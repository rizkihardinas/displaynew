<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class FfmpegSetting extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::updateOrCreate(['id' => 1],[
            'ffmpeg_host' => '192.168.9.90',
            'ffmpeg_port' => '80',
            'ffmpeg_username' => 'sysuno',
            'ffmpeg_password' => 'bdsyst3m',
            'ip' => '192.168.9.167'
        ]);
    }
}
