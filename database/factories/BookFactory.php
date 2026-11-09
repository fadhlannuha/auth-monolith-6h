<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Author;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Publisher;
use Modules\Catalog\ValueObjects\Isbn;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        $isbn = (string) Isbn::generate();

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'isbn' => $isbn,
            'author_id' => Author::factory(),
            'publisher_id' => Publisher::factory(),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 45000, 250000),
            'stock' => fake()->numberBetween(1, 30),
            'low_stock_threshold' => 3,
            'published_year' => fake()->numberBetween(2000, (int) date('Y')),
            'is_active' => true,
        ];
    }

    public function emptyStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
