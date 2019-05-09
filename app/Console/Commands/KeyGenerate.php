<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KeyGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application key';

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
     * @return mixed
     */
    public function handle()
    {
//        echo 'Application key:', PHP_EOL;
//        echo str_random(32), PHP_EOL;
//        echo 'Please copy to .env APP_KEY', PHP_EOL;

        $path = base_path();

        $env_path = $path . DIRECTORY_SEPARATOR . '.env';

        if (!is_file($env_path)) {
            copy($path . DIRECTORY_SEPARATOR . '.env.example', $env_path);
        }

        echo 'Application key [', str_random(32), '] generate successfully.', PHP_EOL;
        echo 'Please copy to .env APP_KEY', PHP_EOL;

        return;
    }





}
