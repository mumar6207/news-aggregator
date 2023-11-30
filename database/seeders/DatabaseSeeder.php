<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create sample articles with a unique slug
        Article::factory(10)->create();

        // Create hardcoded user
          User::create([
              'name' => 'Muhammad Umar',
              'email' => 'umar@example.com',
              'password' => bcrypt('password'), // You can use the default password or generate a hash for a secure password
              'api_key' => 'ynso6orrIQpzp5JXM0XaxT6kIsEP4B8c', // Replace with the desired API key
          ]);

        User::factory()->count(2)->create();
    }
}
