<?php

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('travel slug returns correct tours', function () {
    $travel = Travel::factory()->create();
    $tour = Tour::factory()->create(['travel_id' => $travel->id]);

    $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $tour->id]);
});

test('tour price is shown correctly', function () {
    $travel = Travel::factory()->create();
    $tour = Tour::factory()->create([
        'travel_id' => $travel->id,
        'price' => 123.45,
    ]);

    $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['price' => '123.45']);
});

test('tours list return pagination', function () {
    $tours_per_page = 15; // default value for pagination

    $travel = Travel::factory()->create();
    $tour = Tour::factory($tours_per_page + 1)->create(['travel_id' => $travel->id]);

    $response = $this->get('/api/v1/travels/' . $travel->slug . '/tours');

    $response->assertStatus(200)
        ->assertJsonCount($tours_per_page, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.last_page', 2);
});