<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
          'title' => $this->faker->sentence,
          'content' => $this->faker->paragraph,
          'category' => $this->faker->word,
          'source' => $this->faker->word,
          'author' => $this->faker->name,
          'slug' => $this->faker->slug,
          'description' =>  $this->faker->paragraph,
          'url' =>  $this->faker->url,
          'published_at' => $this->faker->dateTime,
        ];
    }
}
