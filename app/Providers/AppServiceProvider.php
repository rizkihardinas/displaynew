<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $setting = Setting::first();
        Config::set('ffmpeg.host', $setting->ffmpeg_host);
        Config::set('ffmpeg.port', $setting->ffmpeg_port);
        Config::set('ffmpeg.username', $setting->ffmpeg_username);
        Config::set('ffmpeg.password', $setting->ffmpeg_password);
        Config::set('app.ip_server', $setting->ip);
    }
}
