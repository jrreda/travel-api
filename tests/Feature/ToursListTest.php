<?php

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('travel slug returns correct tours', function () {
    $travel = Travel::factory()->create();
    $tour = Tour::factory()->create(['travel_id' => $travel->id]);

    $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

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

    $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['price' => '123.45']);
});

test('tours list return pagination', function () {
    $tours_per_page = 15; // default value for pagination

    $travel = Travel::factory()->create();
    $tour = Tour::factory($tours_per_page + 1)->create(['travel_id' => $travel->id]);

    $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

    $response->assertStatus(200)
        ->assertJsonCount($tours_per_page, 'data')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.last_page', 2);
});

test('sorts by starting date correctly', function () {
    $travel = Travel::factory()->create();
    $tour1 = Tour::factory()->create(['travel_id' => $travel->id, 'starting_date' => now()->subDays(1)]);
    $tour2 = Tour::factory()->create(['travel_id' => $travel->id, 'starting_date' => now()]);

    $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $tour1->id)
        ->assertJsonPath('data.1.id', $tour2->id);
});

test('filter tours by price range correctly', function () {
    $travel = Travel::factory()->create();
    $expensiveTour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 200]);
    $cheapLaterTour = Tour::factory()->create([
        'travel_id' => $travel->id,
        'price' => 100,
        'starting_date' => now()->addDays(2),
        'ending_date' => now()->addDays(3),
    ]);
    $cheapEarlierTour = Tour::factory()->create([
        'travel_id' => $travel->id,
        'price' => 100,
        'starting_date' => now(),
        'ending_date' => now()->addDays(1),
    ]);

    $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours?sortBy=price&sortOrder=asc');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $cheapEarlierTour->id)
        ->assertJsonPath('data.1.id', $cheapLaterTour->id)
        ->assertJsonPath('data.2.id', $expensiveTour->id);
});

test('filter tours by price correctly', function () {
    $travel = Travel::factory()->create();
    $expensiveTour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 200]);
    $cheapTour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 100]);

    $endpoint = '/api/v1/travels/'.$travel->slug.'/tours';

    // PriceFrom filter
    $response = $this->get($endpoint.'?priceFrom=100');
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $cheapTour->id])
        ->assertJsonFragment(['id' => $expensiveTour->id]);

    $response = $this->get($endpoint.'?priceFrom=150');
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $cheapTour->id])
        ->assertJsonFragment(['id' => $expensiveTour->id]);

    $response = $this->get($endpoint.'?priceFrom=250');
    $response->assertStatus(200)
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $cheapTour->id])
        ->assertJsonMissing(['id' => $expensiveTour->id]);

    // PriceTo filter
    $response = $this->get($endpoint.'?priceTo=200');
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $cheapTour->id])
        ->assertJsonFragment(['id' => $expensiveTour->id]);

    $response = $this->get($endpoint.'?priceTo=150');
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $cheapTour->id])
        ->assertJsonMissing(['id' => $expensiveTour->id]);

    $response = $this->get($endpoint.'?priceTo=50');
    $response->assertStatus(200)
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $cheapTour->id])
        ->assertJsonMissing(['id' => $expensiveTour->id]);
});

test('filter by starting date correctly', function () {
    $travel = Travel::factory()->create();
    $lateTour = Tour::factory()->create([
        'travel_id' => $travel->id,
        'starting_date' => now()->addDays(2),
        'ending_date' => now()->addDays(3),
    ]);
    $earlierTour = Tour::factory()->create([
        'travel_id' => $travel->id,
        'starting_date' => now(),
        'ending_date' => now()->addDays(1),
    ]);

    $endpoint = '/api/v1/travels/'.$travel->slug.'/tours';

    // dateFrom Filters
    $response = $this->get($endpoint.'?dateFrom='.now());
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $earlierTour->id])
        ->assertJsonFragment(['id' => $lateTour->id]);

    $response = $this->get($endpoint.'?dateFrom='.now()->addDay());
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $earlierTour->id])
        ->assertJsonFragment(['id' => $lateTour->id]);

    $response = $this->get($endpoint.'?dateFrom='.now()->addDay(5));
    $response->assertStatus(200)
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $earlierTour->id])
        ->assertJsonMissing(['id' => $lateTour->id]);

    // dateTo Filters
    $response = $this->get($endpoint.'?dateTo='.now()->addDay(5));
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $earlierTour->id])
        ->assertJsonFragment(['id' => $lateTour->id]);

    $response = $this->get($endpoint.'?dateTo='.now()->addDay());
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $earlierTour->id])
        ->assertJsonMissing(['id' => $lateTour->id]);

    $response = $this->get($endpoint.'?dateTo='.now()->subDay());
    $response->assertStatus(200)
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['id' => $earlierTour->id])
        ->assertJsonMissing(['id' => $lateTour->id]);

    // dateFrom & dateTo Filters
    $response = $this->get($endpoint.'?dateFrom='.now()->addDay().'&dateTo='.now()->addDay(5));
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $earlierTour->id])
        ->assertJsonFragment(['id' => $lateTour->id]);
});

test('filters returns validation errors', function () {
    $travel = Travel::factory()->create();

    $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours?dateFrom=abcde');
    $response->assertStatus(422);

    $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours?priceFrom=abcde');
    $response->assertStatus(422);
});
