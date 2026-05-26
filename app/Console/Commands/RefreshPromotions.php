<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RefreshPromotions extends Command
{
    protected $signature = 'uno:refresh-promotions';

    protected $description = 'Clear and refresh promotions cache';

    public function handle()
    {
        Cache::forget('promotions_no_operator');
        Cache::forget('promotions_with_operator');

        $this->info('Promotions cache cleared successfully.');

        return 0;
    }
}
