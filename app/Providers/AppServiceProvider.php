<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        Config::set('uno.timeout_in', $setting->timeout_in);
        Config::set('uno.timeout_out', $setting->timeout_out);

        if (Schema::hasTable('preferences') && DB::table('preferences')->count() > 0) {
            $preferences = \App\Models\Preference::first();
            Config::set('uno.style.background', $preferences->background_color);
            Config::set('uno.style.primary', $preferences->primary_color);
            Config::set('uno.style.text_primary', $preferences->text_primary_color);
            Config::set('uno.style.secondary', $preferences->secondary_color);
            Config::set('uno.style.text_secondary', $preferences->text_secondary_color);
            Config::set('uno.style.header', $preferences->header_color);
            Config::set('uno.style.footer', $preferences->footer_color);
        }
    }
}
