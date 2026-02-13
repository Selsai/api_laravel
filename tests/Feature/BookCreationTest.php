<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_is_created_with_valid_data(): void
    {
        $user = User::factory()->create();

        $validData = [
            'title' => 'Le Seigneur des Anneaux',
            'author' => 'J.R.R. Tolkien',
            'summary' => 'Une épopée fantastique dans la Terre du Milieu où un hobbit doit détruire un anneau maléfique.',
            'isbn' => '9782266154345',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $validData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'Le Seigneur des Anneaux',
            'author' => 'J.R.R. Tolkien',
            'isbn' => '9782266154345',
        ]);
    }


    public function test_book_is_not_created_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $invalidData = [
            'title' => 'AB',
            'author' => 'J.R.R. Tolkien',
            'summary' => 'Une épopée fantastique dans la Terre du Milieu.',
            'isbn' => '9782266154345',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $invalidData);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('books', [
            'title' => 'AB',
        ]);
    }


    public function test_book_is_not_created_if_user_is_not_authenticated(): void
    {
        $validData = [
            'title' => 'Le Seigneur des Anneaux',
            'author' => 'J.R.R. Tolkien',
            'summary' => 'Une épopée fantastique dans la Terre du Milieu où un hobbit doit détruire un anneau maléfique.',
            'isbn' => '9782266154345',
        ];

        $response = $this->postJson('/api/v1/books', $validData);

        $response->assertStatus(401);

        $this->assertDatabaseMissing('books', [
            'title' => 'Le Seigneur des Anneaux',
        ]);
    }
}