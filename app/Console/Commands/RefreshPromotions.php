<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Promotion;

class RefreshPromotions extends Command
{
    protected $signature = 'uno:refresh-promotions';

    protected $description = 'Clear and refresh promotions cache';

    public function handle()
    {
        Cache::forget('promotions_no_operator');
        Cache::forget('promotions_with_operator');

        $this->info('Promotions cache cleared successfully.');
        Cache::rememberForever('promotions_no_operator', function () {
            return Promotion::whereNull('is_operator')->get();
        });
        Cache::rememberForever('promotions_with_operator', function () {
            return Promotion::whereNotNull('is_operator')->get();
        });
        return 0;
    }
}
