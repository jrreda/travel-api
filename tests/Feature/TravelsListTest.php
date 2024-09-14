<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Travel;

uses(RefreshDatabase::class);

test('returns paginated data correctly', function () {
    $travels_per_page = 15; // default value for pagination

    Travel::factory($travels_per_page + 1)->create(['is_public' => true]);

    $response = $this->get('/api/v1/travels');

    $response->assertStatus(200)
        ->assertJsonCount($travels_per_page, 'data')
        ->assertJsonPath('meta.last_page', 2);
});

test('shows only public records', function () {
    $public_travel = Travel::factory()->create(['is_public' => true]);
    Travel::factory()->create(['is_public' => false]);

    $response = $this->get('/api/v1/travels');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $public_travel->id);
});