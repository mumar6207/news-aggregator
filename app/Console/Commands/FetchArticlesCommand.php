<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
     protected $signature = 'fetch:articles';

    /**
     * The console command description.
     *
     * @var string
     */
     protected $description = 'Fetch articles from the API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      // Make a request to the API endpoint
      $response = Http::get('http://127.0.0.1:8000/api/fetch/articles');

      // Log the response (optional)
      \Log::info('API Response: ' . $response->body());

      // Display a message (optional)
      $this->info('Articles fetched successfully!');
    }
}
