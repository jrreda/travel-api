<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Travel;

uses(RefreshDatabase::class);

test('returns paginated data correctly', function () {
    Travel::factory(16)->create(['is_public' => true]);

    $response = $this->get('/api/v1/travels');

    $response->assertStatus(200)
        ->assertJsonCount(15, 'data')
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