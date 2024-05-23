<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GoogleController;

class PollGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:poll-google';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Google every 5 minutes!';

    // Dependency Injections
    public function __construct(GoogleController $googleController)
    {
        parent::__construct();
        $this->googleController = $googleController;
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('Polling the server...');
        $result = $this->googleController->listSearch();
        return 0;
    }
}
