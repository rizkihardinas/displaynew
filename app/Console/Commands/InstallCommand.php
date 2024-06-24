<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uno:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Database & Seed Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = database_path('database.sqlite');
        $exist = file_exists($path);
        if(!$exist){
            touch($path);
            // if (file_put_contents($path,null)) {
            //     return "File created successfully at: " . $path;
            // } else {
            //     return "Failed to create file.";
            // }
        }
        Artisan::call('migrate --seed');
    }
}
