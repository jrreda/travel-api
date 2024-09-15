<?php

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;

test('public user cannot add tour', function () {
    $travel = Travel::factory()->create();
    $response = $this->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');

    $response->assertStatus(401);
});

test('non admin user cannot add tour', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->roles()->attach(Role::where('name', 'editor')->value('id'));

    $travel = Travel::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');

    $response->assertStatus(403);
});

test('saves tour successfully with valid data', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->roles()->attach(Role::where('name', 'admin')->value('id'));

    $travel = Travel::factory()->create();

    // faild to create a new travel (missing data)
    $response = $this->actingAs($user)
        ->postJson('api/v1/admin/travels/'.$travel->id.'/tours', [
            'name' => 'Test tour',
        ]);
    $response->assertStatus(403);

    // create a new travel successfully
    $response = $this->actingAs($user)
        ->postJson('api/v1/admin/travels/'.$travel->id.'/tours', [
            'name' => 'Test Tour',
            'starting_date' => now()->toDateString(),
            'ending_date' => now()->addDay()->toDateString(),
            'price' => 99.99,
        ]);
    $response->assertStatus(201);

    // access public Travel
    $response = $this->get('api/v1/travels/'.$travel->slug.'/tours');
    $response->assertJsonFragment(['name' => 'Test Tour']);
});
