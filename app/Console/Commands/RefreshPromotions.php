<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\Security;

class RefreshPromotions extends Command
{
    protected $signature = 'uno:refresh-promotions';

    protected $description = 'Clear and refresh promotions, settings, and security cache';

    public function handle()
    {
        Cache::forget('promotions_no_operator');
        Cache::forget('promotions_with_operator');
        Cache::forget('setting_first');
        Cache::forget('security_first');

        $this->laravel->singleton('setting', function () {
            return Cache::rememberForever('setting_first', function () {
                return Setting::first();
            });
        });

        $this->laravel->singleton('security', function () {
            return Cache::rememberForever('security_first', function () {
                return Security::first();
            });
        });

        $this->info('Promotions, setting, and security cache cleared successfully.');

        Cache::rememberForever('promotions_no_operator', function () {
            return Promotion::whereNull('is_operator')->get();
        });
        Cache::rememberForever('promotions_with_operator', function () {
            return Promotion::whereNotNull('is_operator')->get();
        });
        Cache::rememberForever('setting_first', function () {
            return Setting::first();
        });
        Cache::rememberForever('security_first', function () {
            return Security::first();
        });

        return 0;
    }
}